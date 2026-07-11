import { makeWASocket, useMultiFileAuthState, DisconnectReason, fetchLatestBaileysVersion, delay } from 'baileys';
import { toDataURL } from 'qrcode';
import express from 'express';
import pino from 'pino';
import fs from 'fs';
import path from 'path';

const PORT = process.env.PORT || 3333;
const API_KEY = process.env.API_KEY || 'wabot-secret-key-change-me';
const AUTH_DIR = process.env.AUTH_DIR || './baileys-auth';

if (!fs.existsSync(AUTH_DIR)) fs.mkdirSync(AUTH_DIR, { recursive: true });

const app = express();
app.use(express.json());

// Auth middleware
function auth(req, res, next) {
    const key = req.headers['x-api-key'];
    if (key !== API_KEY) return res.status(401).json({ error: 'Unauthorized' });
    next();
}

// Session store
const sessions = {};

async function createSocket(sessionId) {
    if (sessions[sessionId]?.sock) return sessions[sessionId];

    const authPath = path.join(AUTH_DIR, sessionId);
    if (!fs.existsSync(authPath)) fs.mkdirSync(authPath, { recursive: true });

    const { state, saveCreds } = await useMultiFileAuthState(authPath);
    const { version, isLatest } = await fetchLatestBaileysVersion();

    const sock = makeWASocket({
        version,
        auth: state,
        printQRInTerminal: false,
        logger: pino({ level: 'silent' }),
        browser: ['WABot', 'Chrome', '1.0.0'],
    });

    const info = { sock, status: 'connecting', qr: null, phone: null, saveCreds };

    sock.ev.on('creds.update', saveCreds);

    sock.ev.on('connection.update', (update) => {
        const { connection, lastDisconnect, qr } = update;
        if (qr) {
            info.qr = qr;
            info.status = 'qr_ready';
        }
        if (connection === 'open') {
            info.status = 'connected';
            info.qr = null;
            info.phone = sock.user?.id?.split(':')[0] || null;
        }
        if (connection === 'close') {
            const reason = lastDisconnect?.error?.output?.statusCode;
            const shouldReconnect = reason !== DisconnectReason.loggedOut;
            info.status = reason === DisconnectReason.loggedOut ? 'logged_out' : 'disconnected';
            info.qr = null;
            if (shouldReconnect) {
                delete sessions[sessionId];
            }
        }
    });

    sessions[sessionId] = info;
    return info;
}

// GET / — health check
app.get('/', auth, (req, res) => {
    res.json({ ok: true, uptime: process.uptime(), sessions: Object.keys(sessions).length });
});

// POST /sessions/create
app.post('/sessions/create', auth, async (req, res) => {
    const { session_id, webhook_url } = req.body;
    if (!session_id) return res.status(400).json({ ok: false, error: 'session_id required' });

    try {
        const info = await createSocket(session_id);
        info.webhook_url = webhook_url || null;
        res.json({ ok: true, status: info.status, qr: info.qr });
    } catch (e) {
        res.status(500).json({ ok: false, status: 'error', qr: null, message: e.message });
    }
});

// GET /sessions/:id/status
app.get('/sessions/:id/status', auth, async (req, res) => {
    const info = sessions[req.params.id];
    if (!info) {
        try {
            const info = await createSocket(req.params.id);
            return res.json({ status: info.status, phone: info.phone, qr: info.qr });
        } catch (e) {
            return res.json({ status: 'error' });
        }
    }
    res.json({ status: info.status, phone: info.phone, qr: info.qr });
});

// GET /sessions/:id/qr
app.get('/sessions/:id/qr', auth, async (req, res) => {
    const info = sessions[req.params.id];
    if (!info) {
        try {
            const info = await createSocket(req.params.id);
            await delay(1000);
            if (info.qr) {
                const qrImg = await toDataURL(info.qr);
                return res.json({ qr: info.qr, qr_image: qrImg });
            }
            return res.json({ qr: null });
        } catch (e) {
            return res.json({ qr: null });
        }
    }
    if (info.qr) {
        const qrImg = await toDataURL(info.qr);
        return res.json({ qr: info.qr, qr_image: qrImg });
    }
    res.json({ qr: null });
});

// DELETE /sessions/:id
app.delete('/sessions/:id', auth, async (req, res) => {
    const info = sessions[req.params.id];
    if (info?.sock) {
        try {
            info.sock.end();
            info.sock.logout();
        } catch (e) {}
    }
    delete sessions[req.params.id];
    // cleanup auth dir
    const authPath = path.join(AUTH_DIR, req.params.id);
    if (fs.existsSync(authPath)) {
        fs.rmSync(authPath, { recursive: true, force: true });
    }
    res.json({ ok: true });
});

// POST /sessions/restore-all
app.post('/sessions/restore-all', auth, async (req, res) => {
    const restored = [];
    const dirs = fs.readdirSync(AUTH_DIR, { withFileTypes: true })
        .filter(d => d.isDirectory())
        .map(d => d.name);
    
    for (const dir of dirs) {
        if (sessions[dir]) continue;
        try {
            await createSocket(dir);
            restored.push(dir);
        } catch (e) {}
    }
    res.json({ ok: true, restored, count: restored.length });
});

// POST /sessions/:id/send
app.post('/sessions/:id/send', auth, async (req, res) => {
    const { phone, message } = req.body;
    if (!phone || !message) return res.status(400).json({ ok: false, error: 'phone and message required' });

    const info = sessions[req.params.id];
    if (!info || info.status !== 'connected') {
        return res.json({ ok: false, error: 'Session not connected' });
    }

    try {
        const jid = phone.includes('@s.whatsapp.net') ? phone : `${phone.replace(/[^0-9]/g, '')}@s.whatsapp.net`;
        await info.sock.sendMessage(jid, { text: message });
        res.json({ ok: true, phone, status: 'sent' });
    } catch (e) {
        res.json({ ok: false, error: e.message });
    }
});

// POST /sessions/:id/send-bulk
app.post('/sessions/:id/send-bulk', auth, async (req, res) => {
    const { recipients, message } = req.body;
    if (!recipients?.length || !message) return res.status(400).json({ ok: false, error: 'recipients and message required' });

    const info = sessions[req.params.id];
    if (!info || info.status !== 'connected') {
        return res.json({ sent: 0, failed: recipients.length, errors: ['Session not connected'] });
    }

    let sent = 0, failed = 0, errors = [];
    for (const phone of recipients) {
        try {
            const jid = `${String(phone).replace(/[^0-9]/g, '')}@s.whatsapp.net`;
            await info.sock.sendMessage(jid, { text: message });
            sent++;
        } catch (e) {
            failed++;
            errors.push({ phone, error: e.message });
        }
        await delay(1500); // anti-ban delay
    }
    res.json({ sent, failed, errors });
});

// Start server
app.listen(PORT, () => {
    console.log(`Baileys REST API running on port ${PORT}`);
    // Auto-restore existing auth sessions
    if (fs.existsSync(AUTH_DIR)) {
        const dirs = fs.readdirSync(AUTH_DIR, { withFileTypes: true })
            .filter(d => d.isDirectory())
            .map(d => d.name);
        dirs.forEach(dir => {
            createSocket(dir).catch(() => {});
        });
    }
});
