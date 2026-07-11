<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $__env->yieldContent('title', 'Chat'); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script src="https://cdn.socket.io/4.7.4/socket.io.min.js"></script>
    <style>
        body { font-family: 'Inter', sans-serif; }
        [x-cloak] { display: none !important; }
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #c4c4c4; border-radius: 3px; }
    </style>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: { 50: '#eff6ff', 100: '#dbeafe', 200: '#bfdbfe', 300: '#93c5fd', 400: '#60a5fa', 500: '#3b82f6', 600: '#2563eb', 700: '#1d4ed8', 800: '#1e40af', 900: '#1e3a8a' },
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-gray-100 h-screen overflow-hidden">

<div class="flex h-full">
    
    <aside class="hidden lg:flex flex-col w-[260px] bg-[#1e293b] flex-shrink-0">
        <div class="flex items-center gap-3 px-5 h-14 border-b border-white/10 flex-shrink-0">
            <i class="fas fa-paper-plane text-brand-400 text-lg"></i>
            <span class="text-white font-extrabold text-lg tracking-tight">WABot</span>
        </div>
        <nav class="flex-1 overflow-y-auto py-3 px-3 space-y-1">
            <a href="<?php echo e(route('chat.index')); ?>" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-gray-300 <?php echo e(request()->is('chat*') || request()->is('/') && !request()->is('dashboard*') ? 'bg-brand-500/15 text-brand-400 border-l-[3px] border-brand-500' : 'hover:bg-white/5'); ?>">
                <i class="fas fa-comments w-4 text-center"></i> Chat
            </a>
            <a href="<?php echo e(route('dashboard.stats')); ?>" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-gray-300 hover:bg-white/5">
                <i class="fas fa-chart-pie w-4 text-center"></i> Dashboard
            </a>
            <div class="pt-3 mt-1 border-t border-white/10"></div>
            <a href="<?php echo e(route('sessions.index')); ?>" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-gray-300 hover:bg-white/5">
                <i class="fas fa-mobile-alt w-4 text-center"></i> Sesi / Agen
            </a>
            <a href="<?php echo e(route('autoreplies.index')); ?>" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-gray-300 hover:bg-white/5">
                <i class="fas fa-robot w-4 text-center"></i> Auto-Reply
            </a>
            <a href="<?php echo e(route('contacts.index')); ?>" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-gray-300 hover:bg-white/5">
                <i class="fas fa-address-book w-4 text-center"></i> Kontak
            </a>
            <a href="<?php echo e(route('campaigns.index')); ?>" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-gray-300 hover:bg-white/5">
                <i class="fas fa-bullhorn w-4 text-center"></i> Kampanye
            </a>
            <a href="<?php echo e(route('servers.index')); ?>" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-gray-300 hover:bg-white/5">
                <i class="fas fa-server w-4 text-center"></i> Server
            </a>
        </nav>
        <div class="p-3 border-t border-white/10 flex items-center gap-3">
            <div class="w-8 h-8 rounded-full bg-brand-500 flex items-center justify-center text-white text-xs font-bold">
                <?php echo e(strtoupper(substr(Auth::user()->name, 0, 2))); ?>

            </div>
            <div class="flex-1 min-w-0">
                <div class="text-sm font-medium text-white truncate"><?php echo e(Auth::user()->name); ?></div>
            </div>
            <form action="<?php echo e(route('logout')); ?>" method="POST">
                <?php echo csrf_field(); ?>
                <button class="text-gray-500 hover:text-red-400 transition"><i class="fas fa-sign-out-alt text-sm"></i></button>
            </form>
        </div>
    </aside>

    
    <div class="flex-1 flex flex-col min-w-0">
        <?php echo $__env->yieldContent('chat_content'); ?>
    </div>
</div>


<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('chatApp', (initialContactId, initialPhone, socketServerUrl, socketApiKey) => ({
        activeContact: null,
        messages: [],
        contacts: [],
        sessions: [],
        autoreplies: [],
        searchQuery: '',
        newMessage: '',
        sessionId: '',
        sending: false,
        pollTimer: null,
        sessionName: '',
        showEditModal: false,
        editName: '',
        editPhone: '',
        activeTab: 'all',
        sessionsData: [],
        contactsData: [],
        instagramAccountsData: [],
        telegramAccountsData: [],
        metaAccountsData: [],
        accounts: {},
        socket: null,
        socketConnected: false,
        pendingSocketMsgIds: new Set(),

        get isSessionChannel() {
            const ch = this.activeContact?.channel;
            return !ch || ch === 'whatsapp' || ch === 'baileys' || ch === 'meta';
        },

        channelIcon(channel) {
            if (channel === 'instagram') return '<i class="fab fa-instagram text-[10px] text-white"></i>';
            if (channel === 'telegram') return '<i class="fab fa-telegram-plane text-[10px] text-white"></i>';
            return '<i class="fab fa-whatsapp text-[10px] text-white"></i>';
        },

        channelDotClass(channel) {
            if (channel === 'instagram') return 'bg-pink-500';
            if (channel === 'telegram') return 'bg-blue-500';
            return 'bg-green-500';
        },

        get filteredContacts() {
            let list = this.contactsData.length ? this.contactsData : this.contacts;
            if (this.activeTab !== 'all') {
                list = list.filter(c => c.last_session_id == this.activeTab);
            }
            if (!this.searchQuery) return list;
            const q = this.searchQuery.toLowerCase();
            return list.filter(c =>
                (c.name && c.name.toLowerCase().includes(q)) ||
                (c.display_phone && c.display_phone.includes(q)) ||
                (c.last_message && c.last_message.toLowerCase().includes(q))
            );
        },

        async init() {
            if (this.contactsData.length) {
                this.contacts = this.contactsData;
            } else {
                await this.loadContacts();
            }
            if (this.instagramAccountsData.length) {
                this.accounts.instagram = this.instagramAccountsData;
            }
            if (this.telegramAccountsData.length) {
                this.accounts.telegram = this.telegramAccountsData;
            }
            if (initialContactId) {
                const c = this.contacts.find(x => x.id == initialContactId);
                if (c) this.openChat(c);
            }
            this.connectSocket();
            this.startPolling();
        },

        connectSocket() {
            if (!socketServerUrl) return;
            try {
                this.socket = io(socketServerUrl, {
                    auth: { apiKey: socketApiKey },
                    transports: ['websocket', 'polling'],
                    reconnectionDelay: 3000,
                    reconnectionDelayMax: 10000,
                });
                this.socket.on('connect', () => {
                    this.socketConnected = true;
                });
                this.socket.on('disconnect', () => {
                    this.socketConnected = false;
                });
                this.socket.on('message.received', (data) => {
                    this.handleSocketMessage(data);
                });
            } catch (e) {
                console.warn('Socket.io connection failed, using polling fallback');
            }
        },

        handleSocketMessage(data) {
            const msgKey = `${data.phone}_${data.timestamp}`;
            if (this.pendingSocketMsgIds.has(msgKey)) return;
            this.pendingSocketMsgIds.add(msgKey);

            const cleanPhone = (data.phone || '').replace(/@.*$/, '');
            const foundContact = this.contacts.find(c =>
                (c.phone && c.phone.replace(/@.*$/, '') === cleanPhone) ||
                (c.display_phone && c.display_phone.replace(/^0/, '62') === cleanPhone)
            );

            if (this.activeContact) {
                const activeCleanPhone = (this.activeContact.phone || '').replace(/@.*$/, '');
                if (activeCleanPhone === cleanPhone ||
                    (this.activeContact.display_phone && this.activeContact.display_phone.replace(/^0/, '62') === cleanPhone)) {
                    this.messages.push({
                        id: 'socket_' + Date.now(),
                        direction: 'in',
                        message: data.message,
                        status: 'delivered',
                        time: new Date(data.timestamp * 1000).toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' }),
                        date: new Date(data.timestamp * 1000).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' }),
                        full_time: new Date(data.timestamp * 1000).toISOString(),
                    });
                    this.$nextTick(() => this.scrollToBottom());
                }
            }

            if (foundContact) {
                foundContact.last_message = data.message;
                foundContact.last_time = new Date(data.timestamp * 1000).toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
                foundContact.last_direction = 'in';
            }

            setTimeout(() => this.pendingSocketMsgIds.delete(msgKey), 10000);
        },

        async loadContacts() {
            try {
                const res = await fetch('/api/chat/contacts');
                const data = await res.json();
                this.contacts = data.contacts || [];
                this.contactsData = data.contacts || [];
            } catch (e) { console.error(e); }
        },

        async openChat(contact) {
            this.activeContact = contact;
            this.sessionId = '';
            this.sessionName = '';
            this.editName = contact.name !== contact.display_phone ? contact.name : '';
            this.editPhone = contact.display_phone || '';
            try {
                const res = await fetch(`/chat/${contact.id}`);
                const data = await res.json();
                this.messages = data.messages || [];
                this.sessions = data.sessions || [];
                this.autoreplies = data.autoreplies || [];
                if (data.contact?.channel) {
                    this.activeContact.channel = data.contact.channel;
                }
                if (data.accounts) {
                    if (data.accounts.instagram) this.accounts.instagram = data.accounts.instagram;
                    if (data.accounts.telegram) this.accounts.telegram = data.accounts.telegram;
                }
                if (this.isSessionChannel && this.sessions.length > 0) {
                    this.sessionId = this.sessions[0].session_id;
                    this.sessionName = this.sessions[0].name;
                }
                this.$nextTick(() => this.scrollToBottom());
            } catch (e) { console.error(e); }
        },

        async saveContact() {
            if (!this.activeContact) return;
            try {
                const res = await fetch(`/chat/${this.activeContact.id}/update`, {
                    method: 'PUT',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>' },
                    body: JSON.stringify({ name: this.editName || this.activeContact.name, display_phone: this.editPhone }),
                });
                const data = await res.json();
                if (data.ok) {
                    this.activeContact.name = data.contact.name;
                    this.activeContact.display_phone = data.contact.display_phone;
                    const idx = this.contacts.findIndex(c => c.id === this.activeContact.id);
                    if (idx >= 0) {
                        this.contacts[idx].name = data.contact.name;
                        this.contacts[idx].display_phone = data.contact.display_phone;
                    }
                    this.showEditModal = false;
                }
            } catch (e) { console.error(e); }
        },

        async sendMessage() {
            if (!this.newMessage.trim() || this.sending) return;
            if (this.isSessionChannel && !this.sessionId) return;
            this.sending = true;
            const text = this.newMessage.trim();
            this.newMessage = '';
            try {
                const body = { message: text };
                if (this.sessionId) body.session_id = this.sessionId;
                const res = await fetch(`/chat/${this.activeContact.id}/send`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>' },
                    body: JSON.stringify(body),
                });
                const data = await res.json();
                if (data.ok) {
                    this.messages.push(data.message);
                    this.activeContact.last_message = text;
                    this.activeContact.last_time = data.message.time;
                    this.activeContact.last_direction = 'out';
                    this.$nextTick(() => this.scrollToBottom());
                } else { alert('Gagal: ' + (data.error || 'Unknown')); }
            } catch (e) { console.error(e); }
            this.sending = false;
        },

        async poll() {
            if (!this.activeContact) { await this.loadContacts(); return; }
            try {
                const latest = this.messages.length > 0 ? this.messages[this.messages.length - 1].full_time : null;
                const params = latest ? `?since=${encodeURIComponent(latest)}` : '';
                const res = await fetch(`/api/chat/messages/${this.activeContact.id}${params}`);
                const data = await res.json();
                if (data.messages && data.messages.length > 0) {
                    const ids = new Set(this.messages.map(m => m.id));
                    const news = data.messages.filter(m => !ids.has(m.id));
                    if (news.length > 0) { this.messages.push(...news); this.$nextTick(() => this.scrollToBottom()); }
                }
                await this.loadContacts();
            } catch (e) { console.error(e); }
        },

        startPolling() { this.pollTimer = setInterval(() => this.poll(), 2000); },
        scrollToBottom() { const el = document.getElementById('messageArea'); if (el) el.scrollTop = el.scrollHeight; },
        avatarColor(str) {
            const colors = ['#2563eb','#7c3aed','#db2777','#ea580c','#059669','#0891b2','#4f46e5','#9333ea','#dc2626','#d97706'];
            let hash = 0;
            for (let i = 0; i < (str||'').length; i++) hash = str.charCodeAt(i) + ((hash << 5) - hash);
            return colors[Math.abs(hash) % colors.length];
        },
        initials(str) { return (str || '?').replace(/[^a-zA-Z0-9]/g, '').substring(0, 2).toUpperCase() || '?'; },
    }));
});
</script>
</body>
</html>
<?php /**PATH D:\project laravel\wabot\resources\views\layouts\chat.blade.php ENDPATH**/ ?>