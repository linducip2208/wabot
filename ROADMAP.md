# Roadmap Implementasi — Modul yang Belum Ada

Menutup gap fitur terhadap daftar fitur target. Disusun mengikuti arsitektur existing:
**channel service** (`app/Services/*Service.php`) + **webhook controller** inbound +
**route ter-gate** `check.subscription` + integrasi ke `FlowEngineService`.
Urutan berdasarkan nilai bisnis vs effort.

## Status Fitur Saat Ini

| # | Kategori | Status | Bukti di kode |
|---|----------|--------|---------------|
| 1 | WhatsApp API | Ada | `BaileysService` (multi-nomor), `MetaApiService` (Cloud API), `WaMessageTemplate`, `WaMediaTemplate`, `WaInteractiveButton`, `WaCatalog`/`WaCatalogItem` |
| 2 | Chatbot Builder | Ada | `WaFlow`/`WaFlowNode` + `FlowEngineService`, `WaAutoreply`, `WaForm`, `WaIntentConfig`, `SpintaxService` |
| 3 | AI Chatbot | Ada | `AiService`, `WaAiAgent`, `WaAiKey`, `WaKnowledge`, `IntentService`, `SentimentService`, `ElevenLabsService` |
| 4 | Shared Inbox | Ada | `TeamInboxService`, `WaTeamMember`, `WaConversationAssignment`, `SlaService`/`WaSlaConfig`, `WaConversationRating` |
| 5 | Broadcast | Ada | `WaCampaign`, `WaCampaignAB`, `WaClickEvent` + `ClickTrackerService` |
| 6 | Contact Management | Ada | `WaContact`, `WaContactTag`, `ContactGroup` |
| 7 | Automation | Ada | `WaAutoreply`, `WaDripCampaign`/`WaDripStep`, `WaRecurring` |
| 8 | Website Widget | **Belum** | perlu widget JS + generator embed |
| 9 | Facebook & Instagram | **Sebagian** | `InstagramService` ada; Messenger FB + comment automation belum |
| 10 | Telegram | Ada | `TelegramService`, `WaTelegramAccount` |
| 11 | E-Commerce | **Sebagian** | commerce internal ada; WooCommerce/Shopify/abandoned cart belum |
| 12 | Appointment | **Belum** | perlu modul booking + calendar |
| 13 | Integrasi | **Sebagian** | Webhook + REST API ada; Google Sheets/Zapier/Make/n8n/Mailchimp/HubSpot native belum |
| 14 | Payment | **Sebagian** | Midtrans/Xendit/Tripay/PayPal ada; Stripe/Razorpay/PhonePe/SSLCommerz belum |
| 15 | SaaS / White Label | Ada | `Plan`, `Subscription`, `PaymentGateway`, `WaVoucher`, `Payout`, multi-tenant + `CmsPage` |

---

## Fase 1 — Website Widget (modul #8) · ~3-4 hari

Paling cepat menghasilkan lead, tidak butuh approval pihak ketiga.

- **DB**: `wa_widgets` (user_id, name, channels JSON, theme, position, greeting, offline_msg, is_active, embed_key)
- **Backend**: `WidgetController` (CRUD + generator embed), route publik `GET /widget/{embed_key}.js` (serve JS, no-auth), `POST /widget/{embed_key}/lead`
- **Frontend**: 1 file widget vanilla JS (floating button + popup, multi-channel: WA/Telegram/IG/Messenger), builder UI di panel dengan live preview
- **Gate**: `check.subscription:widgets` + limit per plan
- **Deliverable**: floating button, multi-channel, popup, kode embed copy-paste

## Fase 2 — Appointment / Booking (modul #12) · ~4-5 hari

- **DB**: `wa_services` (durasi, harga), `wa_availabilities` (jam kerja/slot), `wa_appointments` (contact_id, service_id, start_at, status)
- **Backend**: `AppointmentController` + `AppointmentService` (cek slot bentrok), reminder via `WaRecurring`/scheduler existing
- **Flow integration**: node baru di `FlowEngineService` -> "Booking" (pilih layanan -> tanggal -> slot -> konfirmasi)
- **Notif**: konfirmasi + reminder H-1 pakai channel service existing, reschedule via keyword
- **Deliverable**: booking, calendar view, reminder, confirmation, reschedule

## Fase 3 — Media Sosial Lengkap (Omnichannel) · ~10-12 hari

Target: SEMUA kanal media sosial tersedia. Manfaatkan pola `InstagramService`/`TelegramService`
yang sudah ada dan integrasikan ke `FlowEngineService` (cross-channel).

Status awal: WhatsApp, Telegram, Instagram DM sudah ada. Sisanya ditambahkan di fase ini.

### 3a. Instagram — Lengkapi (~2 hari)
- Isi `handleComment()` di `InstagramController.php:426` (masih stub kosong)
- Auto-reply comment + private reply (comment->DM)
- **Deliverable**: comment automation, private reply

### 3b. Facebook Messenger (~3 hari)
- **DB**: `wa_facebook_accounts` (page_id, page_token, app_secret)
- **Service**: `FacebookService` (mirror `InstagramService`) — kirim/terima DM
- **Webhook**: extend Meta webhook controller -> event `messages` + `feed` (comment)
- **Fitur**: Messenger bot, auto-reply comment, private reply, Click-to-WhatsApp Ads (`referral` payload -> route ke flow WA)
- **Deliverable**: FB Messenger bot, comment automation, private reply, CTWA

### 3c. TikTok (~2 hari)
- **DB**: `wa_tiktok_accounts` (open_id, access_token, app credentials)
- **Service**: `TikTokService` — TikTok Business Messaging API / DM automation
- **Webhook**: event pesan masuk -> auto-reply + flow
- **Deliverable**: TikTok DM automation

### 3d. LINE (~2 hari)
- **DB**: `wa_line_accounts` (channel_id, channel_secret, channel_access_token)
- **Service**: `LineService` — Messaging API (reply/push message, rich menu)
- **Webhook**: `POST /webhook/line/{account}` -> auto-reply + flow + broadcast
- **Deliverable**: LINE bot, automation, broadcast

### 3e. X / Twitter DM (~1-2 hari)
- **DB**: `wa_twitter_accounts` (OAuth 2.0 credentials)
- **Service**: `TwitterService` — DM API
- **Deliverable**: X/Twitter DM automation

### 3f. WeChat (opsional, target-2) (~2 hari)
- **DB**: `wa_wechat_accounts` (app_id, app_secret, token)
- **Service**: `WeChatService` — Official Account API
- **Deliverable**: WeChat auto-reply + broadcast

Catatan: semua kanal baru mengikuti pola seragam — model account, service kirim/terima,
webhook inbound, integrasi `FlowEngineService` + auto-reply + team inbox, dan tercatat
di `WaMessage` dengan kolom `channel`. Widget multi-channel (Fase 1) otomatis menambahkan
kanal baru ini sebagai pilihan.

## Fase 4 — E-Commerce Integration (modul #11) · ~5-6 hari

- **DB**: `wa_store_integrations` (platform enum: woocommerce/shopify, base_url, api_key/secret terenkripsi), `wa_ecommerce_events`
- **Service**: `EcommerceService` + adapter pola generik (`WooCommerceAdapter`, `ShopifyAdapter` — REST-format, sesuai prinsip no-hardcoded)
- **Webhook masuk**: `POST /webhook/store/{integration}` -> order created/paid/shipped -> trigger template WA
- **Abandoned cart**: scheduler cek cart pending -> drip message existing
- **Deliverable**: order/payment/shipping notif, abandoned cart, COD verification, sinkron katalog

## Fase 5 — Integrasi No-Code Native (modul #13) · ~4-5 hari

Webhook + REST API sudah ada; ini tinggal konektor spesifik.

- **Google Sheets**: `GoogleSheetsService` (OAuth/service account) — append kontak/lead, baca sebagai sumber broadcast
- **Zapier/Make/n8n**: sudah bisa lewat webhook+API generik -> cukup dokumentasi trigger/action + polish payload + Zapier app manifest (opsional)
- **Mailchimp/HubSpot**: adapter sync kontak dua arah (pola generik provider, key terenkripsi)
- **Deliverable**: konektor Sheets, sync CRM, halaman "Integrations" + dokumentasi webhook

## Fase 6 — Payment Gateway Tambahan (modul #14) · ~3-4 hari

Sudah ada Midtrans/Xendit/Tripay/PayPal; tinggal tambah adapter.

- **Pola generik** (sesuai preferensi no-hardcode): kelompok berdasarkan format — redirect-based (Stripe Checkout, PayPal), hosted-page (Razorpay, PhonePe, SSLCommerz), bukan per-vendor class kaku
- **DB**: extend `payment_gateways` — sudah dynamic, tinggal seed + form kredensial user-input
- **Deliverable**: Stripe, Razorpay, PhonePe, SSLCommerz + kerangka tambah gateway lain via UI

## Fase 7 — Social Publishing & AI Content Studio (StackPosts) · ~15-20 hari

Adopsi kapabilitas **StackPosts** (Laravel + Livewire, referensi source di
`D:\folder selamat lindu\download\stackposts-1000\...\Install`). Posisi: wabot = **inbound/percakapan**,
StackPosts = **outbound/posting konten**. Digabung = suite media sosial lengkap.

Pendekatan: port fitur sebagai modul native di wabot (reuse koneksi channel & SaaS layer existing),
BUKAN menjalankan dua aplikasi terpisah. Channel di sini dipakai untuk **publish konten** (berbeda
dengan Fase 3 yang untuk DM/percakapan) — model account bisa di-share.

### 7a. Social Channels untuk Publishing (~4-5 hari)
- Facebook Pages, Instagram (feed/reel/story), LinkedIn (profile + page), TikTok, X/Twitter, YouTube (opsional)
- **DB**: `social_accounts` (platform, oauth token terenkripsi, profile meta), reuse OAuth pola `InstagramService`
- **Deliverable**: connect/manage akun multi-platform untuk posting

### 7b. Publishing Engine (~4-5 hari)
- Composer post (multi-foto/video/carousel), preview per platform
- Schedule + calendar view, bulk upload (CSV), queue slot, **RSS auto-post**
- Watermark, URL shortener (sudah ada `UrlShortener` — reuse), caption library, hashtag groups
- **Deliverable**: schedule post, bulk post, RSS scheduler, kalender konten

### 7c. AI Content Studio (~5-6 hari) — pakai `AiService` existing (BYOK, no-hardcode)
- AI content generator + content planner
- AI best-time-to-post (analisa engagement)
- AI image + AI video + AI repurpose (1 konten -> banyak format)
- AI review/moderasi + semantic search aset
- **Deliverable**: generator caption/gambar/video, planner, best-time, repurpose

### 7d. SaaS & Team (~2-3 hari) — extend layer existing
- Reuse `Plan`/`Subscription`/`PaymentGateway`/`WaVoucher`/`Payout` yang sudah ada
- Team workspace + approval posting, credits per aksi AI, affiliate (opsional)
- **Deliverable**: paket langganan mencakup kuota posting + kredit AI

Catatan overlap: channel LinkedIn/X/TikTok/FB di sini melengkapi Fase 3; payment gateway StackPosts
(Flutterwave, Paystack, PayU, dll.) bisa disatukan ke Fase 6 dengan pola adapter generik.

---

## Ringkasan Timeline

| Fase | Modul | Estimasi | Prasyarat |
|------|-------|----------|-----------|
| 1 | Website Widget | 3-4 hari | — |
| 2 | Appointment | 4-5 hari | FlowEngine (ada) |
| 3 | FB Messenger + Comment | 4-5 hari | pola InstagramService |
| 4 | E-Commerce | 5-6 hari | template WA (ada) |
| 5 | Integrasi no-code | 4-5 hari | Webhook/API (ada) |
| 6 | Payment gateway | 3-4 hari | PaymentGateway dynamic (ada) |

**Total kurang lebih 23-29 hari kerja** untuk menutup semua gap.

## Checklist Standar per Modul

Setiap modul menempuh alur yang sama:

1. Migration -> Model
2. Service (channel/business logic)
3. Controller
4. Route ter-gate `check.subscription`
5. View responsive (mobile + desktop)
6. Seed plan limit / usage limit
7. Screenshot untuk halaman `/docs`
8. Update sitemap / robots bila menambah surface publik
