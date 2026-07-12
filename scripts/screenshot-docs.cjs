const { chromium } = require('playwright');
const fs = require('fs');

const BASE = 'http://127.0.0.1:8765';
const EMAIL = 'admin@wabot.test';
const PASSWORD = 'password';
const OUT = 'public/marketing/screens';

if (!fs.existsSync(OUT)) fs.mkdirSync(OUT, { recursive: true });

(async () => {
    const browser = await chromium.launch({ headless: true });
    const context = await browser.newContext({ viewport: { width: 1440, height: 900 } });
    const page = await context.newPage();

    // Login
    await page.goto(BASE + '/login');
    await page.fill('input[type="email"]', EMAIL);
    await page.fill('input[type="password"]', PASSWORD);
    await page.click('button[type="submit"]');
    await page.waitForURL('**/dashboard**', { timeout: 10000 });
    console.log('Logged in');

    const pages = [
        { name: '01-dashboard', url: '/dashboard?stats=1' },
        { name: '02-chat-inbox', url: '/chat' },
        { name: '03-contacts', url: '/contacts' },
        { name: '04-campaigns', url: '/campaigns' },
        { name: '05-autoreply', url: '/autoreplies' },
        { name: '06-ai-agents', url: '/ai-agents' },
        { name: '07-flows', url: '/flows' },
        { name: '08-knowledge', url: '/knowledge' },
        { name: '09-sessions', url: '/sessions' },
        { name: '10-webhooks', url: '/webhooks' },
        { name: '11-templates', url: '/templates' },
        { name: '12-publishing', url: '/publishing' },
    ];

    for (const p of pages) {
        await page.goto(BASE + p.url, { waitUntil: 'networkidle', timeout: 15000 });
        await page.waitForTimeout(500);
        await page.screenshot({ path: `${OUT}/${p.name}.png`, fullPage: false });
        console.log('Screenshot: ' + p.name);
    }

    await browser.close();
    console.log('Done — ' + pages.length + ' screenshots saved to ' + OUT);
})();
