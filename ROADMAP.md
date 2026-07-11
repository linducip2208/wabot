# Roadmap WABot — Omnichat SaaS

## Status Saat Ini (Juli 2026)

### ✅ Sudah Selesai

| Fitur | Status | Detail |
|-------|--------|--------|
| WhatsApp Baileys | ✅ | Multi-session, QR scan, send/receive text & media, webhook forward |
| WhatsApp Cloud API (Meta) | ✅ | Template, interactive buttons, media send/receive, auto-reply, AI |
| Instagram DM | ✅ | Send/receive text + image/video, auto-reply pipeline, AI, sentiment, SLA |
| Telegram Bot | ✅ | Text/photo/video/document receive, send video/audio/location/inline keyboard, auto-reply pipeline |
| Voice Call (TTS) | ✅ | ElevenLabs TTS → audio kirim via Meta API + FFmpeg OGG convert |
| Unified Inbox | ✅ | Multi-channel read + send, channel badge, contact per platform |
| Auto-Reply | ✅ | Keyword, welcome, fallback, AI mode, spintax — all 5 channels |
| Flow Engine | ✅ | Visual builder, cross-channel routing, AI node |
| AI Agents | ✅ | Multi-agent, per-channel settings, knowledge base, trigger keywords |
| Campaign/Broadcast | ✅ | Cross-channel (WA Baileys, Meta, Telegram), schedule, anti-ban delay |
| Recurring Schedule | ✅ | Daily/weekly/monthly, cross-channel |
| A/B Testing | ✅ | Dua varian pesan, auto-winner, reply rate tracking |
| Drip Campaign | ✅ | Multi-step, delay, cross-channel |
| Click Tracking | ✅ | URL shortener + click stats |
| Contact Management | ✅ | CSV import, tags, groups, cross-channel dedup |
| Sentiment Analysis | ✅ | Per-channel filter, dashboard chart, trend |
| Team Inbox | ✅ | Round-robin assign, reassign, close, SLA tracking |
| Webhook | ✅ | Outgoing per event, test send |
| REST API | ✅ | WhatsApp, Meta, Instagram, Telegram endpoints |
| Real-time Chat | ✅ | Socket.io — live message push tanpa polling |
| SaaS Layer | ✅ | Plans, subscriptions, vouchers, payments, payouts |
| Admin Panel | ✅ | Users, plans, vouchers, transactions, payouts, CMS, blog |
| i18n | ✅ | English + Indonesian, 1,326 translation keys, JSON-based |
| CMS Pages | ✅ | Visual builder, blocks, drag-drop |
| Blog | ✅ | Admin CRUD, public pages, SEO |
| Programmatic SEO | ✅ | Sitemap, robots.txt, docs page |

### 🟡 Sebagian / Perlu Perbaikan

| Fitur | Status | Yang Kurang |
|-------|--------|-------------|
| Instagram | 🟡 | Comment automation masih stub, belum private reply |
| Facebook Messenger | 🟡 | Belum ada — perlu FacebookService (mirror Instagram pattern) |
| E-Commerce | 🟡 | Internal commerce ada; WooCommerce/Shopify belum |
| Payment Gateway | 🟡 | Midtrans/Xendit/Tripay/PayPal ada; Stripe/Razorpay belum |
| Integrasi No-Code | 🟡 | Webhook+REST API ada; Google Sheets/Zapier/Make native belum |

### 🔴 Belum Ada — Media Sosial & Messaging

| # | Platform | Estimasi | Prioritas | Catatan |
|---|----------|----------|-----------|---------|
| 1 | Facebook Messenger | 2-3 hari | High | Mirror Instagram pattern, comment→DM |
| 2 | Google Business Messages | 2-3 hari | High | GBM API, lokasi/bisnis, rich card |
| 3 | Discord Bot | 1-2 hari | Medium | Gateway intents, slash commands |
| 4 | Viber Bot | 1-2 hari | Medium | Viber REST API, rich media |
| 5 | WeChat Official Account | 2 hari | Medium | MP API, template message, menu |
| 6 | SMS (Twilio/Vonage) | 2 hari | Medium | Outbound + inbound webhook |
| 7 | Email (SendGrid/Mailgun) | 1-2 hari | Medium | Template + inbound parse |
| 8 | TikTok DM | 2 hari | Low | Business Messaging API |
| 9 | LINE Bot | 2 hari | Low | Messaging API, rich menu, flex |
| 10 | X/Twitter DM | 1-2 hari | Low | OAuth 2.0, DM API v2 |
| 11 | Snapchat Business | 2 hari | Low | Snap Kit, Creative Kit |
| 12 | Pinterest Messages | 1 hari | Low | Business API |
| 13 | YouTube Comments | 1-2 hari | Low | Data API v3, comment thread |
| 14 | Reddit API | 1-2 hari | Low | PM + comment reply |
| 15 | Slack Bot | 1 hari | Low | Bolt SDK, slash commands |
| 16 | Microsoft Teams Bot | 1-2 hari | Low | Bot Framework |
| 17 | KakaoTalk Channel | 2 hari | Low | Korea market |
| 18 | Zalo OA | 2 hari | Low | Vietnam market |

### 🔴 Belum Ada — Fitur Tambahan

| # | Fitur | Estimasi | Prioritas |
|---|-------|----------|-----------|
| 19 | Website Widget | 3-4 hari | High |
| 20 | Appointment/Booking | 4-5 hari | Medium |
| 21 | Instagram Comment Automation | 1-2 hari | High |
| 22 | Social Media Monitoring | 3-4 hari | Medium |
| 23 | Review Management (Google/FB) | 2-3 hari | Medium |
| 24 | WooCommerce/Shopify | 5-6 hari | Medium |
| 25 | Google Sheets Sync | 2 hari | Medium |
| 26 | Stripe/Razorpay/PhonePe | 3-4 hari | Medium |
| 27 | CRM — Lead Scoring | 2-3 hari | Low |
| 28 | Push Notification (Web Push) | 1-2 hari | Low |
| 29 | Social Publishing (StackPosts) | 15-20 hari | Low |
| 30 | AI Content Studio | 5-6 hari | Low |
| 31 | Affiliate/Referral System | 3-4 hari | Low |

---

## Fase Selanjutnya

### Fase 1 — High Priority (7-9 hari)
- **Website Widget** — floating chat button multi-channel, embed JS, lead capture
- **Facebook Messenger** — service + webhook, auto-reply, comment→DM
- **Instagram Comment Automation** — isi stub, auto-reply comment + private reply

### Fase 2 — New Channels High (8-10 hari)
- **Google Business Messages** — GBM API, rich card, lokasi
- **Discord Bot** — gateway intents, slash commands
- **SMS (Twilio/Vonage)** — outbound + inbound webhook
- **Email (SendGrid/Mailgun)** — template + inbound parse

### Fase 3 — New Channels Medium (10-12 hari)
- **Viber Bot** — REST API, rich media
- **WeChat Official Account** — MP API, template
- **LINE Bot** — Messaging API, flex message
- **TikTok DM** — Business Messaging
- **X/Twitter DM** — OAuth 2.0, DM API v2

### Fase 4 — Fitur Tambahan (8-12 hari)
- **Appointment/Booking** — calendar, slot, reminder
- **Social Media Monitoring** — keyword tracking, sentiment alert
- **Review Management** — Google My Business + Facebook Reviews
- **Google Sheets Sync** — two-way contact sync

### Fase 5 — E-Commerce + Payment (8-10 hari)
- **WooCommerce/Shopify** — order notif, abandoned cart, katalog sync
- **Stripe/Razorpay/PhonePe** — pola adapter generic

### Fase 6 — Low Priority Channels (8-12 hari)
- Snapchat, Pinterest, YouTube Comments, Reddit, Slack, Teams, KakaoTalk, Zalo

### Fase 7 — Advanced (15-20 hari)
- **Social Publishing (StackPosts)** — multi-platform post scheduler, AI content studio
- **CRM Lead Scoring** — pipeline automation
- **Affiliate/Referral** — program referral

---

## Ringkasan Timeline

| Fase | Modul | Estimasi |
|------|-------|----------|
| 1 | Widget + FB + IG comment | 7-9 hari |
| 2 | GBM + Discord + SMS + Email | 8-10 hari |
| 3 | Viber + WeChat + LINE + TikTok + X | 10-12 hari |
| 4 | Appointment + Monitoring + Review + Sheets | 8-12 hari |
| 5 | E-Commerce + Payment gateway | 8-10 hari |
| 6 | Low priority channels (8 platform) | 8-12 hari |
| 7 | Advanced (publishing, CRM, affiliate) | 15-20 hari |

**Total: 64-85 hari kerja** untuk full omnichat suite.

---

## Checklist Standar per Modul

1. Migration → Model
2. Service (business logic)
3. Controller + route (ter-gate subscription)
4. View responsive (mobile + desktop)
5. Seed plan limit
6. Screenshot untuk /docs
7. i18n keys (en.json + id.json)
