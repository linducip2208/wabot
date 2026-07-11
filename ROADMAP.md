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

### 🔴 Belum Ada

| Fitur | Estimasi | Prioritas |
|-------|----------|-----------|
| Website Widget (floating chat) | 3-4 hari | High |
| Appointment/Booking | 4-5 hari | Medium |
| Facebook Messenger | 2-3 hari | High |
| TikTok DM | 2 hari | Low |
| LINE Bot | 2 hari | Low |
| X/Twitter DM | 1-2 hari | Low |
| WooCommerce/Shopify | 5-6 hari | Medium |
| Google Sheets Sync | 2 hari | Medium |
| Stripe/Razorpay | 3-4 hari | Medium |
| Social Publishing (StackPosts) | 15-20 hari | Low |

---

## Fase Selanjutnya

### Fase 1 — Website Widget (3-4 hari)
Floating chat button multi-channel (WA/Telegram/IG/Messenger), embed JS, popup lead capture.

### Fase 2 — Facebook Messenger (2-3 hari)
Service + webhook mirror Instagram pattern, auto-reply, comment→DM private reply.

### Fase 3 — Instagram Comment Automation (1-2 hari)
Isi stub `handleComment()`, auto-reply comment + private reply.

### Fase 4 — E-Commerce Integration (5-6 hari)
WooCommerce adapter, Shopify adapter, abandoned cart recovery, order notification.

### Fase 5 — Payment Gateway Tambahan (3-4 hari)
Stripe, Razorpay, PhonePe, SSLCommerz — pola adapter generik.

---

## Checklist Standar per Modul

1. Migration → Model
2. Service (business logic)
3. Controller + route (ter-gate subscription)
4. View responsive (mobile + desktop)
5. Seed plan limit
6. Screenshot untuk /docs
7. i18n keys (en.json + id.json)
