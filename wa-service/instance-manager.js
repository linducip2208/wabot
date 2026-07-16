const {
    makeWASocket,
    useMultiFileAuthState,
    DisconnectReason,
    makeCacheableSignalKeyStore,
    fetchLatestBaileysVersion,
} = require('baileys');
const pino = require('pino');
const fs = require('fs');
const path = require('path');

class InstanceManager {
    constructor() {
        this.instances = new Map();
        this.sessionsDir = path.join(__dirname, 'sessions');
        if (!fs.existsSync(this.sessionsDir)) {
            fs.mkdirSync(this.sessionsDir, { recursive: true });
        }
    }

    getSessionDir(sessionId) {
        const dir = path.join(this.sessionsDir, sessionId);
        if (!fs.existsSync(dir)) {
            fs.mkdirSync(dir, { recursive: true });
        }
        return dir;
    }

    getInstance(sessionId) {
        return this.instances.get(sessionId) || null;
    }

    getStatus(sessionId) {
        const inst = this.instances.get(sessionId);
        if (!inst) return { status: 'not_found' };
        return inst.status;
    }

    async create(sessionId, webhookUrl) {
        if (this.instances.has(sessionId)) {
            const existing = this.instances.get(sessionId);
            if (existing.status.status === 'connected') {
                return { ok: true, status: 'connected', qr: null, message: 'Already connected' };
            }
            return { ok: true, status: existing.status.status, qr: existing.qr || null, message: 'Session already exists' };
        }

        const sessionDir = this.getSessionDir(sessionId);

        if (webhookUrl) {
            const configFile = path.join(sessionDir, 'config.json');
            fs.writeFileSync(configFile, JSON.stringify({ webhook_url: webhookUrl }, null, 2));
        }

        const logger = pino({ level: 'silent' });

        const { state, saveCreds } = await useMultiFileAuthState(sessionDir);
        const { version } = await fetchLatestBaileysVersion();

        const instanceData = {
            status: { status: 'connecting' },
            qr: null,
            sock: null,
            webhookUrl: webhookUrl || null,
        };
        this.instances.set(sessionId, instanceData);

        const sock = makeWASocket({
            version,
            logger,
            printQRInTerminal: false,
            auth: {
                creds: state.creds,
                keys: makeCacheableSignalKeyStore(state.keys, logger),
            },
            browser: ['Chrome', '', ''],
            markOnlineOnConnect: true,
            syncFullHistory: false,
            connectTimeoutMs: 60000,
            defaultQueryTimeoutMs: 60000,
            keepAliveIntervalMs: 30000,
            generateHighQualityLinkPreview: true,
        });

        instanceData.sock = sock;

        const lidMap = new Map();
        instanceData.lidMap = lidMap;

        sock.ev.on('contacts.upsert', (contacts) => {
            console.log(`[${sessionId}] contacts.upsert: ${contacts.length} contacts`);
            if (contacts.length > 0) console.log(`[${sessionId}] sample:`, JSON.stringify(contacts[0]));
            for (const c of contacts) {
                if (c.lid && c.id && c.lid !== c.id) {
                    lidMap.set(c.lid, c.id);
                    console.log(`[${sessionId}] LID mapped: ${c.lid} → ${c.id}`);
                }
            }
        });

        sock.ev.on('contacts.update', (contacts) => {
            console.log(`[${sessionId}] contacts.update: ${contacts.length} contacts`);
            if (contacts.length > 0) console.log(`[${sessionId}] sample:`, JSON.stringify(contacts[0]));
            for (const c of contacts) {
                if (c.lid && c.id && c.lid !== c.id) {
                    lidMap.set(c.lid, c.id);
                }
            }
        });

        sock.ev.on('creds.update', saveCreds);

        sock.ev.on('connection.update', async (update) => {
            const { connection, lastDisconnect, qr } = update;

            if (qr) {
                instanceData.status = { status: 'qr_ready' };
                instanceData.qr = qr;
                console.log(`[${sessionId}] QR ready`);
            }

            if (connection === 'open') {
                instanceData.status = { status: 'connected', phone: sock.user?.id || null };
                instanceData.qr = null;
                console.log(`[${sessionId}] Connected`);
                notifyStatus(sessionId, instanceData, 'connected');
            }

            if (connection === 'close') {
                const code = lastDisconnect?.error?.output?.statusCode;
                const shouldReconnect = code !== DisconnectReason.loggedOut;

                if (shouldReconnect) {
                    instanceData.status = { status: 'reconnecting' };
                    console.log(`[${sessionId}] Reconnecting...`);
                    notifyStatus(sessionId, instanceData, 'reconnecting');
                    this.reconnect(sessionId, webhookUrl);
                } else {
                    instanceData.status = { status: 'disconnected', reason: 'logged_out' };
                    console.log(`[${sessionId}] Logged out`);
                    notifyStatus(sessionId, instanceData, 'logged_out');
                }
            }
        });

        sock.ev.on('messages.upsert', async (msg) => {
            const message = msg.messages[0];
            if (!message?.key?.remoteJid || message.key.fromMe) return;

            const isGroup = message.key.remoteJid.endsWith('@g.us');
            const sender = isGroup
                ? message.key.participant || message.key.remoteJid
                : message.key.remoteJid;

            const text = message.message?.conversation
                || message.message?.extendedTextMessage?.text
                || message.message?.imageMessage?.caption
                || '';

            const pushName = message.pushName || null;

            console.log(`[${sessionId}] INCOMING: msg=${JSON.stringify(message.message).substring(0,200)}`);

            let realPhone = sender;
            let displayPhone = null;
            if (sender.endsWith('@lid') && lidMap.has(sender)) {
                realPhone = lidMap.get(sender);
                displayPhone = realPhone.replace(/@s.whatsapp.net/g, '');
            } else if (sender.endsWith('@lid')) {
                displayPhone = sender.replace(/@s.whatsapp.net|@lid/gi, '');
                try {
                    const pn = await sock.signalRepository.lidMapping.getPNForLID(sender);
                    if (pn) {
                        realPhone = pn;
                        displayPhone = realPhone.replace(/@s.whatsapp.net/g, '');
                        console.log(`[${sessionId}] LID resolved: ${sender} → ${pn}`);
                        lidMap.set(sender, pn);
                    }
                } catch (e) {
                    console.log(`[${sessionId}] LID resolve failed: ${e.message}`);
                }
            } else {
                displayPhone = sender.replace(/@s.whatsapp.net|@lid/gi, '');
            }

            // text check removed - send all messages
            if (!webhookUrl) return;

            try {
                const res = await fetch(webhookUrl, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        session_id: sessionId,
                        phone: realPhone,
                        display_phone: displayPhone,
                        message: text,
                        push_name: pushName,
                        is_group: isGroup,
                        group_id: isGroup ? message.key.remoteJid : null,
                        message_id: message.key.id,
                        timestamp: Math.floor(Date.now() / 1000),
                    }),
                });

                if (res.ok) {
                    const data = await res.json().catch(() => null);
                    if (data?.reply) {
                        let replyJid = realPhone;
                        if (!replyJid || replyJid.endsWith('@lid')) {
                            replyJid = message.key.remoteJid;
                        }
                        await sock.sendMessage(replyJid, { text: data.reply });
                        console.log(`[${sessionId}] Reply sent via socket to ${replyJid}`);
                    }
                }
            } catch (e) {
                console.error(`[${sessionId}] Webhook error:`, e.message);
            }
        });

        return { ok: true, status: 'connecting', qr: null, message: 'Session created, waiting for QR...' };
    }

    async reconnect(sessionId, webhookUrl) {
        const instanceData = this.instances.get(sessionId);
        if (!instanceData) return;

        setTimeout(async () => {
            try {
                if (instanceData.sock) {
                    instanceData.sock.ev.removeAllListeners();
                    instanceData.sock.ws?.close();
                }
            } catch (e) { /* ignore */ }

            this.instances.delete(sessionId);
            await this.create(sessionId, webhookUrl);
        }, 3000);
    }

    async restoreAll() {
        if (!fs.existsSync(this.sessionsDir)) return;

        const entries = fs.readdirSync(this.sessionsDir, { withFileTypes: true });
        for (const entry of entries) {
            if (!entry.isDirectory()) continue;

            const sessionId = entry.name;
            const configFile = path.join(this.sessionsDir, sessionId, 'config.json');

            let webhookUrl = null;
            if (fs.existsSync(configFile)) {
                try {
                    const config = JSON.parse(fs.readFileSync(configFile, 'utf-8'));
                    webhookUrl = config.webhook_url || null;
                } catch (e) { /* ignore corrupt config */ }
            }

            if (webhookUrl) {
                console.log(`[${sessionId}] Auto-restoring session...`);
                await this.create(sessionId, webhookUrl).catch(e => {
                    console.error(`[${sessionId}] Restore failed:`, e.message);
                });
                await new Promise(r => setTimeout(r, 2000));
            }
        }
    }

    async delete(sessionId) {
        const instanceData = this.instances.get(sessionId);
        if (instanceData?.sock) {
            try {
                instanceData.sock.ev.removeAllListeners();
                instanceData.sock.ws?.close();
            } catch (e) { /* ignore */ }
        }
        this.instances.delete(sessionId);

        const sessionDir = this.getSessionDir(sessionId);
        try {
            fs.rmSync(sessionDir, { recursive: true, force: true });
        } catch (e) { /* ignore */ }
    }

    buildContent(message, mediaUrl) {
        if (!mediaUrl) return { text: message };
        const ext = (mediaUrl.split('?')[0].split('.').pop() || '').toLowerCase();
        if (['jpg', 'jpeg', 'png', 'gif', 'webp'].includes(ext)) {
            return { image: { url: mediaUrl }, caption: message || undefined };
        }
        if (['mp4', 'mov', '3gp', 'mkv'].includes(ext)) {
            return { video: { url: mediaUrl }, caption: message || undefined };
        }
        if (['mp3', 'ogg', 'opus', 'm4a', 'wav'].includes(ext)) {
            return { audio: { url: mediaUrl }, mimetype: 'audio/mpeg' };
        }
        return {
            document: { url: mediaUrl },
            fileName: mediaUrl.split('/').pop().split('?')[0] || 'file',
            caption: message || undefined,
        };
    }

    async send(sessionId, to, message, mediaUrl = null) {
        const instanceData = this.instances.get(sessionId);
        if (!instanceData?.sock || instanceData.status.status !== 'connected') {
            return { ok: false, error: 'Session not connected' };
        }

        try {
            let jid = to.includes('@') ? to : `${to}@s.whatsapp.net`;
            if (jid.endsWith('@lid')) {
                if (instanceData.lidMap?.has(jid)) {
                    jid = instanceData.lidMap.get(jid);
                } else {
                    try {
                        const pn = await instanceData.sock.signalRepository.lidMapping.getPNForLID(jid);
                        if (pn) {
                            console.log(`[${sessionId}] LID resolved via getPNForLID: ${jid} → ${pn}`);
                            jid = pn;
                        }
                    } catch (e) {
                        console.log(`[${sessionId}] getPNForLID failed: ${e.message}`);
                    }
                }
            }
            const result = await instanceData.sock.sendMessage(jid, this.buildContent(message, mediaUrl));
            return { ok: true, messageId: result?.key?.id };
        } catch (e) {
            console.error(`[${sessionId}] send error:`, e.message);
            return { ok: false, error: e.message };
        }
    }

    async sendBulk(sessionId, recipients, message) {
        const results = { sent: 0, failed: 0, errors: [] };
        for (const to of recipients) {
            const result = await this.send(sessionId, to, message);
            if (result.ok) {
                results.sent++;
            } else {
                results.failed++;
                results.errors.push({ phone: to, error: result.error });
            }
            await new Promise(r => setTimeout(r, 1000 + Math.random() * 2000));
        }
        return results;
    }
}

async function notifyStatus(sessionId, instanceData, status) {
    const webhookUrl = instanceData.webhookUrl;
    if (!webhookUrl) return;

    const statusUrl = webhookUrl.replace(/\/webhook\/whatsapp$/, '/webhook/whatsapp-status');
    try {
        await fetch(statusUrl, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                session_id: sessionId,
                status: status,
                reason: instanceData.status.reason || null,
                phone: instanceData.status.phone || null,
            }),
        });
    } catch (e) {
        console.error(`[${sessionId}] Status notify error:`, e.message);
    }
}

module.exports = new InstanceManager();

async function waitForLidMap(lidMap, lid, timeout) {
    const start = Date.now();
    while (Date.now() - start < timeout) {
        if (lidMap.has(lid)) return lidMap.get(lid);
        await new Promise(r => setTimeout(r, 300));
    }
    return null;
}
