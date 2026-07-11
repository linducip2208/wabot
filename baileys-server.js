import { makeWASocket, useMultiFileAuthState, DisconnectReason, fetchLatestBaileysVersion, delay, downloadMediaMessage } from 'baileys';
import axios from 'axios';
import { toDataURL } from 'qrcode';
import express from 'express';
import { createServer } from 'http';
import { Server as SocketIOServer } from 'socket.io';
import cors from 'cors';
import pino from 'pino';
import fs from 'fs';
import path from 'path';

const PORT = process.env.PORT || 3333;
const API_KEY = process.env.API_KEY || 'wabot-secret-key-change-me';
const AUTH_DIR = process.env.AUTH_DIR || './baileys-auth';

if (!fs.existsSync(AUTH_DIR)) fs.mkdirSync(AUTH_DIR, { recursive: true });

const app = express();
app.use(express.json());
app.use(cors());

const httpServer = createServer(app);

// Socket.io with auth
const io = new SocketIOServer(httpServer, {
    cors: { origin: '*', methods: ['GET', 'POST'] },
    transports: ['websocket', 'polling'],
});

io.use((socket, next) => {
    const key = socket.handshake.auth?.apiKey || socket.handshake.query?.apiKey;
    if (key === API_KEY) return next();
    next(new Error('Unauthorized'));
});

io.on('connection', (socket) => {
    console.log(`Socket.io client connected: ${socket.id}`);
    socket.on('disconnect', () => {
        console.log(`Socket.io client disconnected: ${socket.id}`);
    });
});

// Auth middleware
function auth(req, res, next) {
    const key = req.headers['x-api-key'];
    if (key !== API_KEY) return res.status(401).json({ error: 'Unauthorized' });
    next();
}

// Session store
const sessions = {};

async function notifyWebhook(url, payload) {
    if (!url) return;
    try {
        await axios.post(url, payload, { timeout: 10000 });
    } catch (e) {
        console.error(`Webhook ${payload.event} failed for session ${payload.session_id}: ${e.message}`);
    }
}

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
            notifyWebhook(info.webhook_url, {
                event: 'session.connected',
                session_id: sessionId,
                phone: info.phone,
                timestamp: Math.floor(Date.now() / 1000),
            });
            io.emit('session.connected', { session_id: sessionId, phone: info.phone });
        }
        if (connection === 'close') {
            const reason = lastDisconnect?.error?.output?.statusCode;
            const loggedOut = reason === DisconnectReason.loggedOut;
            info.status = loggedOut ? 'logged_out' : 'disconnected';
            info.qr = null;
            notifyWebhook(info.webhook_url, {
                event: loggedOut ? 'session.logged_out' : 'session.disconnected',
                session_id: sessionId,
                reason: loggedOut ? 'logged_out' : (lastDisconnect?.error?.message || 'unknown'),
                timestamp: Math.floor(Date.now() / 1000),
            });
            io.emit('session.disconnected', { session_id: sessionId, reason: loggedOut ? 'logged_out' : 'disconnected' });
            if (!loggedOut) {
                delete sessions[sessionId];
            }
        }
    });

    sock.ev.on('messages.upsert', async (m) => {
        if (m.type !== 'notify') return;

        for (const msg of m.messages) {
            if (msg.key.fromMe) continue;

            const messageContent = msg.message;
            if (!messageContent) continue;

            const sender = msg.key.remoteJid;
            let messageType = 'unknown';
            let messageData = {};
            const timestamp = msg.messageTimestamp || Math.floor(Date.now() / 1000);

            try {
                if (messageContent.conversation) {
                    messageType = 'text';
                    messageData = { text: messageContent.conversation };
                } else if (messageContent.extendedTextMessage) {
                    messageType = 'text';
                    messageData = { text: messageContent.extendedTextMessage.text || '' };
                } else if (messageContent.imageMessage) {
                    messageType = 'image';
                    const img = messageContent.imageMessage;
                    messageData = {
                        caption: img.caption || '',
                        mimetype: img.mimetype,
                    };
                    try {
                        const buffer = await downloadMediaMessage(msg, 'buffer', {});
                        messageData.imageUrl = `data:${img.mimetype};base64,${buffer.toString('base64')}`;
                    } catch (_) {
                        messageData.imageUrl = null;
                    }
                } else if (messageContent.videoMessage) {
                    messageType = 'video';
                    const vid = messageContent.videoMessage;
                    messageData = {
                        caption: vid.caption || '',
                        mimetype: vid.mimetype,
                        seconds: vid.seconds || 0,
                    };
                    try {
                        const buffer = await downloadMediaMessage(msg, 'buffer', {});
                        messageData.mediaUrl = `data:${vid.mimetype};base64,${buffer.toString('base64')}`;
                    } catch (_) {
                        messageData.mediaUrl = null;
                    }
                } else if (messageContent.audioMessage) {
                    messageType = 'audio';
                    const aud = messageContent.audioMessage;
                    messageData = {
                        mimetype: aud.mimetype,
                        seconds: aud.seconds || 0,
                        ptt: aud.ptt || false,
                    };
                    try {
                        const buffer = await downloadMediaMessage(msg, 'buffer', {});
                        messageData.mediaUrl = `data:${aud.mimetype};base64,${buffer.toString('base64')}`;
                    } catch (_) {
                        messageData.mediaUrl = null;
                    }
                } else if (messageContent.documentMessage) {
                    messageType = 'document';
                    const doc = messageContent.documentMessage;
                    messageData = {
                        fileName: doc.fileName || 'document',
                        mimetype: doc.mimetype,
                        caption: doc.caption || '',
                        fileLength: doc.fileLength || 0,
                    };
                    try {
                        const buffer = await downloadMediaMessage(msg, 'buffer', {});
                        messageData.mediaUrl = `data:${doc.mimetype};base64,${buffer.toString('base64')}`;
                    } catch (_) {
                        messageData.mediaUrl = null;
                    }
                } else if (messageContent.stickerMessage) {
                    messageType = 'sticker';
                    const stk = messageContent.stickerMessage;
                    messageData = {
                        mimetype: stk.mimetype,
                        isAnimated: stk.isAnimated || false,
                    };
                } else if (messageContent.contactMessage) {
                    messageType = 'contact';
                    messageData = {
                        displayName: messageContent.contactMessage.displayName || '',
                        vcard: messageContent.contactMessage.vcard || '',
                    };
                } else if (messageContent.locationMessage) {
                    messageType = 'location';
                    const loc = messageContent.locationMessage;
                    messageData = {
                        degreesLatitude: loc.degreesLatitude,
                        degreesLongitude: loc.degreesLongitude,
                        name: loc.name || '',
                        address: loc.address || '',
                    };
                } else if (messageContent.reactionMessage) {
                    messageType = 'reaction';
                    messageData = {
                        text: messageContent.reactionMessage.text || '',
                        sender: messageContent.reactionMessage.key?.remoteJid || '',
                    };
                } else {
                    messageType = 'unknown';
                    messageData = { raw: Object.keys(messageContent) };
                }
            } catch (e) {
                messageType = 'error';
                messageData = { error: e.message };
            }

            if (info.webhook_url) {
                notifyWebhook(info.webhook_url, {
                    event: 'message.received',
                    session_id: sessionId,
                    phone: sender,
                    message: messageData,
                    message_type: messageType,
                    timestamp,
                });
            }

            // Emit via Socket.io for real-time chat
            io.emit('message.received', {
                session_id: sessionId,
                phone: sender,
                message: messageType === 'text' ? (messageData.text || messageData.caption || '') : `[${messageType}]`,
                message_type: messageType,
                channel: 'whatsapp',
                timestamp,
            });
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
httpServer.listen(PORT, () => {
    console.log(`Baileys REST API + WebSocket running on port ${PORT}`);
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
