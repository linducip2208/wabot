require('dotenv').config();
const express = require('express');
const instanceManager = require('./instance-manager');

const app = express();
app.use(express.json());

const PORT = process.env.PORT || 3100;
const API_KEY = process.env.API_KEY || 'wabotaku';

function auth(req, res, next) {
    const key = req.headers['x-api-key'] || req.query.api_key;
    if (key !== API_KEY) {
        return res.status(401).json({ error: 'Unauthorized' });
    }
    next();
}

app.get('/', (req, res) => {
    res.json({ status: 'ok', service: 'WABot Baileys Service', version: '1.0.0' });
});

app.post('/sessions/create', auth, async (req, res) => {
    const { session_id, webhook_url } = req.body;
    if (!session_id) {
        return res.status(400).json({ error: 'session_id is required' });
    }
    const result = await instanceManager.create(session_id, webhook_url);
    res.json(result);
});

app.get('/sessions/:id/status', auth, (req, res) => {
    const status = instanceManager.getStatus(req.params.id);
    res.json(status);
});

app.get('/sessions/:id/qr', auth, (req, res) => {
    const instance = instanceManager.getInstance(req.params.id);
    if (!instance || !instance.qr) {
        return res.status(404).json({ error: 'QR not available' });
    }
    res.json({ qr: instance.qr });
});

app.delete('/sessions/:id', auth, async (req, res) => {
    await instanceManager.delete(req.params.id);
    res.json({ ok: true, message: 'Session deleted' });
});

app.post('/sessions/restore-all', auth, async (req, res) => {
    await instanceManager.restoreAll();
    res.json({ ok: true, message: 'All sessions restored' });
});

app.post('/sessions/:id/send', auth, async (req, res) => {
    const { phone, message } = req.body;
    if (!phone || !message) {
        return res.status(400).json({ error: 'phone and message are required' });
    }
    const result = await instanceManager.send(req.params.id, phone, message);
    res.json(result);
});

app.post('/sessions/:id/send-bulk', auth, async (req, res) => {
    const { recipients, message } = req.body;
    if (!recipients || !message) {
        return res.status(400).json({ error: 'recipients and message are required' });
    }
    const result = await instanceManager.sendBulk(req.params.id, recipients, message);
    res.json(result);
});

app.listen(PORT, async () => {
    console.log(`WABot Baileys Service running on port ${PORT}`);
    await instanceManager.restoreAll();
    console.log('Auto-restore complete');
});
