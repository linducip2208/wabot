(function() {
    var WABOT_WIDGET = {
        baseUrl: '{{ url('/') }}',
        embedKey: '{{ $widget->embed_key }}',
        greeting: @json($widget->greeting_message),
        offlineMessage: @json($widget->offline_message),
        themeColor: '{{ $widget->theme_color }}',
        position: '{{ $widget->position }}',
        buttonIcon: '{{ $widget->button_icon }}',
        channels: @json($channels),
    };

    var ICONS = {
        whatsapp: '<svg viewBox="0 0 24 24" width="18" height="18" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>',
        telegram: '<svg viewBox="0 0 24 24" width="18" height="18" fill="currentColor"><path d="M11.944 0A12 12 0 000 12a12 12 0 0012 12 12 12 0 0012-12A12 12 0 0012 0a12 12 0 00-12 12 12 12 0 0012 12 12 12 0 0012-12A12 12 0 0012 0a12 12 0 00-.056 0zm4.962 7.224c.1-.002.321.023.465.14a.506.506 0 01.171.325c.016.093.036.306.02.472-.18 1.898-.962 6.502-1.36 8.627-.168.9-.499 1.201-.82 1.23-.696.065-1.225-.46-1.9-.902-1.056-.693-1.653-1.124-2.678-1.8-1.185-.78-.417-1.21.258-1.91.177-.184 3.247-2.977 3.307-3.23.007-.032.014-.15-.056-.212s-.174-.041-.249-.024c-.106.024-1.793 1.14-5.061 3.345-.48.33-.913.49-1.302.48-.428-.008-1.252-.241-1.865-.44-.752-.245-1.349-.374-1.297-.789.027-.216.325-.437.893-.663 3.498-1.524 5.83-2.529 6.998-3.014 3.332-1.386 4.025-1.627 4.476-1.635z"/></svg>',
        instagram: '<svg viewBox="0 0 24 24" width="18" height="18" fill="currentColor"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zM12 0C8.741 0 8.333.014 7.053.072 2.695.272.273 2.69.073 7.052.014 8.333 0 8.741 0 12c0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98C8.333 23.986 8.741 24 12 24c3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98C15.668.014 15.259 0 12 0zm0 5.838a6.162 6.162 0 100 12.324 6.162 6.162 0 000-12.324zM12 16a4 4 0 110-8 4 4 0 010 8zm6.406-11.845a1.44 1.44 0 100 2.881 1.44 1.44 0 000-2.881z"/></svg>',
        messenger: '<svg viewBox="0 0 24 24" width="18" height="18" fill="currentColor"><path d="M12 0C5.373 0 0 4.975 0 11.111c0 3.497 1.745 6.616 4.472 8.652V24l4.086-2.242a12.54 12.54 0 003.442.464c6.627 0 12-4.974 12-11.111C24 4.975 18.627 0 12 0zm1.193 14.963l-3.056-3.259-5.963 3.259L10.732 8.2l3.132 3.259L19.75 8.2l-6.557 6.763z"/></svg>',
    };

    var CHANNEL_ICONS = {
        whatsapp: ICONS.whatsapp,
        telegram: ICONS.telegram,
        instagram: ICONS.instagram,
        messenger: ICONS.messenger,
    };

    var CHANNEL_URLS = {
        whatsapp: function(id) { return 'https://wa.me/' + id.replace(/[^0-9]/g, ''); },
        telegram: function(id) { return 'https://t.me/' + id.replace('@', ''); },
        instagram: function(id) { return 'https://instagram.com/' + id.replace('@', ''); },
        messenger: function(id) { return 'https://m.me/' + id; },
    };

    var cssStr = '@keyframes wabot-pulse { 0%,100% { box-shadow: 0 0 0 0 C } 50% { box-shadow: 0 0 0 12px transparent } }'
        + '@keyframes wabot-slide-up { 0% { opacity:0; transform:translateY(16px) } 100% { opacity:1; transform:translateY(0) } }'
        + '@keyframes wabot-fade-in { 0% { opacity:0 } 100% { opacity:1 } }'
        + '.wabot-btn { position:fixed; z-index:9999; width:56px; height:56px; border-radius:50%; border:none; cursor:pointer; '
        + 'display:flex; align-items:center; justify-content:center; color:#fff; font-size:24px; '
        + 'animation:wabot-pulse 2.2s ease-in-out infinite; box-shadow:0 4px 16px rgba(0,0,0,.18); '
        + 'transition:transform .2s,box-shadow .2s; }'
        + '.wabot-btn:hover { transform:scale(1.08); box-shadow:0 6px 24px rgba(0,0,0,.24); }'
        + '.wabot-btn:active { transform:scale(.95); }'
        + '.wabot-popup { position:fixed; z-index:9998; bottom:88px; width:340px; max-width:calc(100vw - 24px); '
        + 'background:#fff; border-radius:16px; box-shadow:0 8px 40px rgba(0,0,0,.16); '
        + 'overflow:hidden; font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,sans-serif; '
        + 'animation:wabot-slide-up .3s cubic-bezier(.16,1,.3,1); display:none; }'
        + '.wabot-popup.open { display:block; }'
        + '.wabot-popup-header { color:#fff; padding:20px 18px; }'
        + '.wabot-popup-header h3 { margin:0 0 4px; font-size:16px; font-weight:700; }'
        + '.wabot-popup-header p { margin:0; font-size:13px; opacity:.9; line-height:1.4; }'
        + '.wabot-popup-body { padding:16px 18px; }'
        + '.wabot-channels { display:flex; flex-direction:column; gap:8px; }'
        + '.wabot-channel-btn { display:flex; align-items:center; gap:10px; width:100%; padding:12px 14px; '
        + 'border:1.5px solid #e5e7eb; border-radius:12px; background:#fff; cursor:pointer; '
        + 'font-size:14px; font-weight:500; color:#374151; transition:all .15s; }'
        + '.wabot-channel-btn:hover { border-color:C; background: rgba(C, .05); }'
        + '.wabot-channel-btn svg { flex-shrink:0; }'
        + '.wabot-divider { display:flex; align-items:center; gap:12px; margin:14px 0; color:#9ca3af; font-size:12px; }'
        + '.wabot-divider::before, .wabot-divider::after { content:""; flex:1; border-top:1px solid #e5e7eb; }'
        + '.wabot-form { display:flex; flex-direction:column; gap:10px; }'
        + '.wabot-form input, .wabot-form textarea { width:100%; box-sizing:border-box; padding:10px 14px; '
        + 'border:1.5px solid #e5e7eb; border-radius:10px; font-size:13px; font-family:inherit; '
        + 'outline:none; transition:border-color .15s; }'
        + '.wabot-form input:focus, .wabot-form textarea:focus { border-color:C; }'
        + '.wabot-form textarea { resize:vertical; min-height:70px; }'
        + '.wabot-form button { padding:10px 20px; border:none; border-radius:10px; color:#fff; '
        + 'font-size:13px; font-weight:600; cursor:pointer; transition:all .15s; }'
        + '.wabot-form button:hover { filter:brightness(1.1); }'
        + '.wabot-form button:active { transform:scale(.97); }'
        + '.wabot-close { position:absolute; top:12px; right:14px; background:rgba(255,255,255,.25); '
        + 'border:none; color:#fff; width:28px; height:28px; border-radius:50%; cursor:pointer; '
        + 'display:flex; align-items:center; justify-content:center; font-size:14px; transition:background .15s; }'
        + '.wabot-close:hover { background:rgba(255,255,255,.4); }'
        + '.wabot-success { text-align:center; padding:24px 18px; animation:wabot-fade-in .3s; }'
        + '.wabot-success-icon { font-size:40px; margin-bottom:10px; }'
        + '.wabot-success-msg { font-size:14px; color:#374151; font-weight:500; }'
        + '.wabot-backdrop { position:fixed; inset:0; z-index:9997; background:rgba(0,0,0,.35); display:none; }'
        + '.wabot-backdrop.open { display:block; }'
        + '@media (max-width:400px) { '
        + '.wabot-popup { bottom:80px; left:12px !important; right:12px !important; width:auto; max-width:none; }'
        + '}';

    cssStr = cssStr.replace(/C/g, WABOT_WIDGET.themeColor);
    var style = document.createElement('style');
    style.textContent = cssStr;
    document.head.appendChild(style);

    var btn = document.createElement('button');
    btn.className = 'wabot-btn';
    btn.style.background = WABOT_WIDGET.themeColor;
    btn.style[WABOT_WIDGET.position === 'bottom-left' ? 'left' : 'right'] = '20px';
    btn.style.bottom = '20px';
    btn.innerHTML = (function() {
        switch (WABOT_WIDGET.buttonIcon) {
            case 'chat': return '💬';
            case 'headset': return '🎧';
            case 'question': return '❓';
            case 'message': return '✉️';
            default: return '💬';
        }
    })();

    var backdrop = document.createElement('div');
    backdrop.className = 'wabot-backdrop';

    var popup = document.createElement('div');
    popup.className = 'wabot-popup';
    popup.style[WABOT_WIDGET.position === 'bottom-left' ? 'left' : 'right'] = '20px';
    popup.innerHTML = buildPopupContent();

    function buildPopupContent() {
        var channelsHtml = '';
        if (WABOT_WIDGET.channels && WABOT_WIDGET.channels.length > 0) {
            channelsHtml = '<div class="wabot-channels">';
            for (var i = 0; i < WABOT_WIDGET.channels.length; i++) {
                var ch = WABOT_WIDGET.channels[i];
                if (ch.type && ch.id) {
                    var label = ch.label || ch.type.charAt(0).toUpperCase() + ch.type.slice(1);
                    var url = CHANNEL_URLS[ch.type] ? CHANNEL_URLS[ch.type](ch.id) : '#';
                    var icon = CHANNEL_ICONS[ch.type] || '';
                    channelsHtml += '<a href="' + url + '" target="_blank" rel="noopener" class="wabot-channel-btn">'
                        + icon + '<span>' + label + '</span>' + '</a>';
                }
            }
            channelsHtml += '</div>';
        }

        var hasChannels = WABOT_WIDGET.channels && WABOT_WIDGET.channels.length > 0;
        var hasOffline = WABOT_WIDGET.offlineMessage && WABOT_WIDGET.offlineMessage.length > 0;
        var showDivider = hasChannels && hasOffline;

        var dividerHtml = showDivider
            ? '<div class="wabot-divider"><span>or</span></div>'
            : (hasChannels ? '' : '');

        var greeting = WABOT_WIDGET.greeting || 'Hi! How can we help you?';

        var offlineFormHtml = '';
        if (hasOffline) {
            var ofm = WABOT_WIDGET.offlineMessage;
            offlineFormHtml = dividerHtml
                + '<form class="wabot-form" id="wabot-lead-form">'
                + '<input type="text" name="name" placeholder="Your name" required>'
                + '<textarea name="message" placeholder="' + ofm + '"></textarea>'
                + '<button type="submit" style="background:' + WABOT_WIDGET.themeColor + '">Send Message</button>'
                + '</form>';
        }

        return '<div class="wabot-popup-header" style="background:' + WABOT_WIDGET.themeColor + '">'
            + '<button class="wabot-close" id="wabot-close">&times;</button>'
            + '<h3>Chat with us</h3>'
            + '<p>' + greeting + '</p>'
            + '</div>'
            + '<div class="wabot-popup-body">'
            + channelsHtml
            + offlineFormHtml
            + '</div>';
    }

    document.body.appendChild(backdrop);
    document.body.appendChild(popup);
    document.body.appendChild(btn);

    function openPopup() {
        popup.classList.add('open');
        backdrop.classList.add('open');
    }

    function closePopup() {
        popup.classList.remove('open');
        backdrop.classList.remove('open');
        var success = popup.querySelector('.wabot-success');
        if (success) success.remove();
        var form = popup.querySelector('#wabot-lead-form');
        if (form) {
            form.style.display = '';
            form.reset();
        }
    }

    btn.addEventListener('click', function(e) {
        e.stopPropagation();
        if (popup.classList.contains('open')) {
            closePopup();
        } else {
            openPopup();
        }
    });

    backdrop.addEventListener('click', closePopup);

    popup.addEventListener('click', function(e) {
        var closeBtn = e.target.closest('#wabot-close');
        if (closeBtn) {
            closePopup();
        }
    });

    popup.addEventListener('submit', function(e) {
        var form = e.target.closest('#wabot-lead-form');
        if (!form) return;
        e.preventDefault();
        var fd = new FormData(form);
        var btn = form.querySelector('button[type="submit"]');
        btn.disabled = true;
        btn.textContent = 'Sending...';
        fetch(WABOT_WIDGET.baseUrl + '/widget/' + WABOT_WIDGET.embedKey + '/lead', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
            body: JSON.stringify(Object.fromEntries(fd)),
        }).then(function(r) { return r.json(); }).then(function(data) {
            form.style.display = 'none';
            var success = document.createElement('div');
            success.className = 'wabot-success';
            success.innerHTML = '<div class="wabot-success-icon">✅</div><div class="wabot-success-msg">Message sent! We\'ll get back to you soon.</div>';
            popup.querySelector('.wabot-popup-body').appendChild(success);
            setTimeout(closePopup, 3000);
        }).catch(function() {
            btn.disabled = false;
            btn.textContent = 'Send Message';
            alert('Failed to send. Please try again.');
        });
    });
})();
