import '../css/chat.css';
import './reverb';

import {
    apiListConversations,
    apiListMessages,
    apiCreateConversationWithText,
    apiCreateConversationWithAudio,
    apiSendText,
    apiSendAudio,
    apiRenameConversation,
    apiDeleteConversation,
} from './backend';

import { createLiveChat } from './live';

document.addEventListener('DOMContentLoaded', () => {
    const app = document.getElementById('chatApp');
    const body = document.body;

    // Sidebar / header
    const sidebarOverlay = document.getElementById('sidebarOverlay');
    const openSidebarBtn = document.getElementById('openSidebarBtn');
    const closeSidebarBtn = document.getElementById('closeSidebarBtn');

    const headerSearchBtn = document.getElementById('headerSearchBtn');
    const chatSearch = document.getElementById('chatSearch');
    const themeToggleBtn = document.getElementById('themeToggleBtn');

    // Top menu
    const chatMenuBtn = document.getElementById('chatMenuBtn');
    const chatActionMenu = document.getElementById('chatActionMenu');

    // Chat list
    const chatList = document.getElementById('chatList');
    const newChatBtn = document.getElementById('newChatBtn');
    const headerTitle = document.querySelector('.chat-header-title');
    const headerSubtitle = document.querySelector('.chat-header-subtitle');

    // Composer / messages
    const chatMessages = document.getElementById('chatMessages');
    const chatInput = document.getElementById('chatInput');
    const sendBtn = document.getElementById('sendBtn');

    // Voice record button
    const recordBtn = document.getElementById('recordBtn');

    const state = {
        theme: localStorage.getItem('chat-theme') || 'dark',
        activeChatEl: null,

        // realtime
        activeSubId: null,
        seenIds: new Set(),

        // record
        record: {
            mediaRecorder: null,
            stream: null,
            chunks: [],
            startTs: 0,
            timerInt: null,
            cancelled: false,
            audioContext: null,
            analyser: null,
            rafId: null,
            durationSec: 0,
        },
    };

    // --------------------------
    // Utils
    // --------------------------
    const isMobile = () => window.innerWidth <= 1024;

    const escapeHtml = (s) =>
        String(s)
            .replaceAll('&', '&amp;')
            .replaceAll('<', '&lt;')
            .replaceAll('>', '&gt;')
            .replaceAll('"', '&quot;')
            .replaceAll("'", '&#039;');

    const nowLabel = () => {
        const d = new Date();
        return `${String(d.getHours()).padStart(2, '0')}:${String(d.getMinutes()).padStart(2, '0')}`;
    };

    const formatTime = (secs) => {
        const mm = String(Math.floor(secs / 60)).padStart(2, '0');
        const ss = String(Math.floor(secs % 60)).padStart(2, '0');
        return `${mm}:${ss}`;
    };

    const formatChatTime = (iso) => {
        if (!iso) return '';
        const d = new Date(iso);
        return `${String(d.getHours()).padStart(2, '0')}:${String(d.getMinutes()).padStart(2, '0')}`;
    };

    function scrollMessagesToBottom() {
        if (!chatMessages) return;
        chatMessages.scrollTop = chatMessages.scrollHeight;
    }

    function getChatTitle(item) {
        return item?.querySelector('.chat-title')?.textContent?.trim() || 'Yeni chat';
    }
    function setChatTitle(item, title) {
        const el = item?.querySelector('.chat-title');
        if (el) el.textContent = title || 'Yeni chat';
        if (item === state.activeChatEl && headerTitle) headerTitle.textContent = title || 'Yeni chat';
    }
    function setChatPreview(item, text) {
        const el = item?.querySelector('.chat-preview');
        if (el) el.textContent = text || '';
    }

    function getActiveConversationId() {
        const v = state.activeChatEl?.dataset?.conversationId;
        return v ? Number(v) : null;
    }
    function setActiveConversationId(id) {
        if (!state.activeChatEl) return;
        state.activeChatEl.dataset.conversationId = String(id);
    }

    // --------------------------
    // Theme
    // --------------------------
    function applyTheme(theme) {
        state.theme = theme === 'light' ? 'light' : 'dark';
        body.setAttribute('data-theme', state.theme);
        localStorage.setItem('chat-theme', state.theme);
        themeToggleBtn?.classList.toggle('active', state.theme === 'light');
    }
    themeToggleBtn?.addEventListener('click', () => applyTheme(state.theme === 'dark' ? 'light' : 'dark'));
    applyTheme(state.theme);

    // --------------------------
    // Sidebar behavior
    // --------------------------
    const openSidebarMobile = () => app?.classList.add('sidebar-open');
    const closeSidebarMobile = () => app?.classList.remove('sidebar-open');

    openSidebarBtn?.addEventListener('click', () => {
        if (isMobile()) openSidebarMobile();
        else app?.classList.toggle('sidebar-collapsed');
    });
    closeSidebarBtn?.addEventListener('click', closeSidebarMobile);
    sidebarOverlay?.addEventListener('click', closeSidebarMobile);

    headerSearchBtn?.addEventListener('click', () => {
        if (isMobile()) openSidebarMobile();
        app?.classList.remove('sidebar-collapsed');
        setTimeout(() => chatSearch?.focus(), 60);
    });

    // --------------------------
    // Messages UI
    // --------------------------
    function removeTypingIfAny() {
        document.querySelectorAll('.message-row.ai.typing').forEach((x) => x.remove());
    }

    function showSystemAiMessage(text) {
        if (!chatMessages) return;
        const row = document.createElement('div');
        row.className = 'message-row ai';
        row.innerHTML = `
      <div class="message-avatar ai-avatar">AI</div>
      <div class="message-bubble-wrap">
        <div class="message-bubble">${escapeHtml(text)}</div>
        <div class="message-meta">AI • ${nowLabel()}</div>
      </div>
    `;
        chatMessages.appendChild(row);
        scrollMessagesToBottom();
    }

    function addTypingIndicator(type = 'text') {
        if (!chatMessages) return null;
        removeTypingIfAny();
        const label = type === 'audio' ? 'Səs hazırlayır...' : 'Yazır...';
        const row = document.createElement('div');
        row.className = 'message-row ai typing';
        row.innerHTML = `
      <div class="message-avatar ai-avatar">AI</div>
      <div class="message-bubble-wrap">
        <div class="message-bubble">
          <span class="typing-dots"><i></i><i></i><i></i></span>
          <span>${label}</span>
        </div>
      </div>
    `;
        chatMessages.appendChild(row);
        scrollMessagesToBottom();
        return row;
    }

    // --------------------------
    // Custom audio player with frequency visualizer
    // --------------------------
    function audioPlayerHTML(label, transcript = '') {
        const hasTranscript = transcript.trim().length > 0;
        return `
            <div class="audio-player-card">
                <button type="button" class="audio-play-btn" aria-label="Play">
                    <svg viewBox="0 0 24 24" class="ap-play-icon"><polygon points="7,4 21,12 7,20"/></svg>
                    <svg viewBox="0 0 24 24" class="ap-pause-icon"><rect x="5" y="4" width="4" height="16"/><rect x="15" y="4" width="4" height="16"/></svg>
                </button>
                <canvas class="audio-wave-canvas" width="240" height="36"></canvas>
                <span class="audio-player-time">0:00</span>
                <audio preload="metadata"></audio>
            </div>
            <div class="audio-message-bottom">
                <span class="audio-message-meta">${label}</span>
                <button type="button" class="audio-transcript-btn${hasTranscript ? '' : ' hidden'}" title="Mətni göstər">
                    <svg viewBox="0 0 24 24"><path d="M4 6h16M4 12h10M4 18h14"/></svg>
                    <span>Çevir</span>
                </button>
            </div>
            <div class="audio-transcript hidden"${hasTranscript ? ` data-transcript="${escapeHtml(transcript)}"` : ''}>${hasTranscript ? escapeHtml(transcript) : ''}</div>
        `;
    }

    function initAudioPlayer(container) {
        if (!container || container.dataset.apInit) return;
        container.dataset.apInit = '1';

        const audio = container.querySelector('audio');
        const canvas = container.querySelector('.audio-wave-canvas');
        const playBtn = container.querySelector('.audio-play-btn');
        const timeEl = container.querySelector('.audio-player-time');
        if (!audio || !canvas || !playBtn) return;

        const ctx = canvas.getContext('2d');
        let audioCtx, analyser, rafId;
        let connected = false;
        const barCount = 32;

        function drawBars(getData) {
            const W = canvas.width;
            const H = canvas.height;
            const cy = H / 2;
            const gap = 2;
            const bw = (W - gap * (barCount - 1)) / barCount;

            ctx.clearRect(0, 0, W, H);

            for (let i = 0; i < barCount; i++) {
                const val = getData(i);
                const bh = Math.max(2, val * H * 0.85);
                const x = i * (bw + gap);
                const y = cy - bh / 2;
                const hue = 120 - val * 120;
                ctx.fillStyle = `hsla(${hue}, 80%, 55%, ${0.5 + val * 0.5})`;
                ctx.beginPath();
                ctx.roundRect(x, y, bw, bh, bw / 2);
                ctx.fill();
            }
        }

        function drawIdle() {
            drawBars((i) => 0.04 + Math.sin(i * 0.7) * 0.03);
        }

        function startVisualize() {
            if (!analyser) return;
            const freqData = new Uint8Array(analyser.frequencyBinCount);
            const step = Math.floor(freqData.length / barCount);

            const loop = () => {
                if (audio.paused || audio.ended) return;
                analyser.getByteFrequencyData(freqData);
                drawBars((i) => {
                    let v = 0;
                    for (let j = 0; j < step; j++) v += freqData[i * step + j];
                    return v / step / 255;
                });
                rafId = requestAnimationFrame(loop);
            };
            loop();
        }

        function updateTime() {
            if (!timeEl) return;
            const t = audio.currentTime || 0;
            const mm = String(Math.floor(t / 60));
            const ss = String(Math.floor(t % 60)).padStart(2, '0');
            timeEl.textContent = `${mm}:${ss}`;
        }

        function setPlayState(playing) {
            playBtn.querySelector('.ap-play-icon').style.display = playing ? 'none' : '';
            playBtn.querySelector('.ap-pause-icon').style.display = playing ? '' : 'none';
        }

        playBtn.addEventListener('click', () => {
            if (audio.paused) {
                // Pause all other players
                document.querySelectorAll('.audio-player-card audio').forEach((a) => {
                    if (a !== audio && !a.paused) a.pause();
                });

                if (!connected) {
                    audioCtx = new (window.AudioContext || window.webkitAudioContext)();
                    const src = audioCtx.createMediaElementSource(audio);
                    analyser = audioCtx.createAnalyser();
                    analyser.fftSize = 128;
                    analyser.smoothingTimeConstant = 0.75;
                    src.connect(analyser);
                    analyser.connect(audioCtx.destination);
                    connected = true;
                }
                audioCtx.resume();
                audio.play();
            } else {
                audio.pause();
            }
        });

        audio.addEventListener('play', () => { setPlayState(true); startVisualize(); });
        audio.addEventListener('pause', () => { setPlayState(false); if (rafId) cancelAnimationFrame(rafId); });
        audio.addEventListener('ended', () => { setPlayState(false); if (rafId) cancelAnimationFrame(rafId); drawIdle(); });
        audio.addEventListener('timeupdate', updateTime);

        drawIdle();
    }

    function initAllAudioPlayers() {
        document.querySelectorAll('.audio-player-card').forEach(initAudioPlayer);
    }

    function appendAiAudioMessage(audioUrl, labelText = 'Səsli cavab', transcript = '') {
        if (!chatMessages) return;
        const row = document.createElement('div');
        row.className = 'message-row ai';
        row.innerHTML = `
      <div class="message-avatar ai-avatar">AI</div>
      <div class="message-bubble-wrap">
        <div class="message-bubble message-bubble--audio">
            <div class="audio-message-card">
                ${audioPlayerHTML('🔊 ' + escapeHtml(labelText), transcript)}
          </div>
        </div>
        <div class="message-meta">AI • ${nowLabel()}</div>
      </div>
    `;
        row.querySelector('audio').src = audioUrl;
        chatMessages.appendChild(row);
        initAudioPlayer(row.querySelector('.audio-player-card'));
        scrollMessagesToBottom();
    }

    function appendUserTextMessage(text) {
        if (!chatMessages) return;

        const userInitial = (document.querySelector('.user-avatar')?.textContent?.trim() || 'U')
            .slice(0, 1)
            .toUpperCase();

        const row = document.createElement('div');
        row.className = 'message-row user';
        row.innerHTML = `
      <div class="message-bubble-wrap">
        <div class="message-bubble">${escapeHtml(text)}</div>
        <div class="message-meta">Sən • ${nowLabel()}</div>
      </div>
      <div class="message-avatar user-avatar">${escapeHtml(userInitial)}</div>
    `;
        chatMessages.appendChild(row);
        scrollMessagesToBottom();
    }

    function appendUserAudioMessage(blob, durationSec = 0) {
        if (!chatMessages) return;

        const userInitial = (document.querySelector('.user-avatar')?.textContent?.trim() || 'U')
            .slice(0, 1)
            .toUpperCase();

        const url = URL.createObjectURL(blob);

        const row = document.createElement('div');
        row.className = 'message-row user';
        row.innerHTML = `
      <div class="message-bubble-wrap">
        <div class="message-bubble message-bubble--audio">
            <div class="audio-message-card">
                ${audioPlayerHTML('🎤 Səsli mesaj • ' + escapeHtml(formatTime(durationSec)))}
          </div>
        </div>
        <div class="message-meta">Sən • ${nowLabel()}</div>
      </div>
      <div class="message-avatar user-avatar">${escapeHtml(userInitial)}</div>
    `;
        row.querySelector('audio').src = url;
        chatMessages.appendChild(row);
        initAudioPlayer(row.querySelector('.audio-player-card'));
        scrollMessagesToBottom();
    }

    function extractMessagesArray(payload) {
        if (Array.isArray(payload?.messages)) return payload.messages;
        if (payload?.messages && Array.isArray(payload.messages.data)) return payload.messages.data;
        if (payload?.conversation?.messages && Array.isArray(payload.conversation.messages)) return payload.conversation.messages;
        return [];
    }

    function renderDraftMessages() {
        if (!chatMessages) return;
        chatMessages.innerHTML = `
      <div class="date-separator"><span>Bu gün</span></div>
      <div class="message-row ai">
        <div class="message-avatar ai-avatar">AI</div>
        <div class="message-bubble-wrap">
          <div class="message-bubble">Salam 👋 Yeni chat hazırdır. Mesajını yaz və ya səsli mesaj göndər.</div>
          <div class="message-meta">AI • ${nowLabel()}</div>
        </div>
      </div>
    `;
        scrollMessagesToBottom();
    }

    function renderMessagesFromServer(messages) {
        if (!chatMessages) return;

        state.seenIds.clear();
        chatMessages.innerHTML = `<div class="date-separator"><span>Bu gün</span></div>`;

        const userInitial = (document.querySelector('.user-avatar')?.textContent?.trim() || 'U')
            .slice(0, 1)
            .toUpperCase();

        messages.forEach((m) => {
            if (!m?.id) return;
            state.seenIds.add(m.id);

            const role = typeof m.role === 'string' ? m.role : m.role?.value;
            const meta = m.meta || {};
            const time = m.created_at ? formatChatTime(m.created_at) : nowLabel();

            if (meta.type === 'audio' && (meta.url || m.audio_path)) {
                const url = meta.url || m.audio_path;
                const transcript = (m.content || '').trim();
                const row = document.createElement('div');
                row.className = `message-row ${role === 'ai' ? 'ai' : 'user'}`;
                row.innerHTML =
                    role === 'ai'
                        ? `
              <div class="message-avatar ai-avatar">AI</div>
              <div class="message-bubble-wrap">
                <div class="message-bubble message-bubble--audio">
                    <div class="audio-message-card">
                        ${audioPlayerHTML('🎤 Audio', transcript)}
                  </div>
                </div>
                <div class="message-meta">AI • ${escapeHtml(time)}</div>
              </div>
            `
                        : `
              <div class="message-bubble-wrap">
                <div class="message-bubble message-bubble--audio">
                  <div class="audio-message-card">
                    ${audioPlayerHTML('🎤 Audio', transcript)}
                  </div>
                </div>
                <div class="message-meta">Sən • ${escapeHtml(time)}</div>
              </div>
              <div class="message-avatar user-avatar">${escapeHtml(userInitial)}</div>
            `;
                row.querySelector('audio').src = url;
                chatMessages.appendChild(row);
                return;
            }

            const text = (m.content || '').trim();
            if (!text) return;

            const row = document.createElement('div');
            row.className = `message-row ${role === 'ai' ? 'ai' : 'user'}`;
            row.innerHTML =
                role === 'ai'
                    ? `
            <div class="message-avatar ai-avatar">AI</div>
            <div class="message-bubble-wrap">
              <div class="message-bubble">${escapeHtml(text)}</div>
              <div class="message-meta">AI • ${escapeHtml(time)}</div>
            </div>
          `
                    : `
            <div class="message-bubble-wrap">
              <div class="message-bubble">${escapeHtml(text)}</div>
              <div class="message-meta">Sən • ${escapeHtml(time)}</div>
            </div>
            <div class="message-avatar user-avatar">${escapeHtml(userInitial)}</div>
          `;
            chatMessages.appendChild(row);
        });

        initAllAudioPlayers();
        scrollMessagesToBottom();
    }

    // --------------------------
    // Realtime subscribe (text + audio)
    // --------------------------
    let onAiAudioReceived = null;

    function subscribeToConversation(conversationId) {
        if (!conversationId || !window.Echo) return;

        if (state.activeSubId) {
            window.Echo.leave(`conversation.${state.activeSubId}`);
        }
        state.activeSubId = conversationId;

        window.Echo
            .private(`conversation.${conversationId}`)
            .listen('.ai.typing', (p) => {
                if (getActiveConversationId() !== p.conversation_id) return;
                addTypingIndicator(p.type || 'text');
            })
            .listen('.message.sent', (p) => {
                if (!p?.id) return;
                if (state.seenIds.has(p.id)) return;
                state.seenIds.add(p.id);

                if (getActiveConversationId() !== p.conversation_id) return;

                removeTypingIfAny();

                const meta = p.meta || {};
                const role = typeof p.role === 'string' ? p.role : p.role?.value;

                if (role === 'ai' && meta.type === 'audio' && meta.url) {
                    appendAiAudioMessage(meta.url, p.content || 'Səsli cavab', p.content || '');
                    setChatPreview(state.activeChatEl, '🔊 Səsli cavab');
                    if (onAiAudioReceived) onAiAudioReceived(meta.url);
                    return;
                }

                if (role === 'ai' && (p.content || '').trim()) {
                    showSystemAiMessage(p.content);
                    setChatPreview(state.activeChatEl, (p.content || '').trim().slice(0, 80));
                    return;
                }
            });
    }

    // --------------------------
    // Conversations (dynamic)
    // --------------------------
    function createDraftChatItem() {
        const btn = document.createElement('button');
        btn.className = 'chat-item';
        btn.type = 'button';
        btn.dataset.conversationId = '';
        btn.innerHTML = `
      <div class="chat-item-top">
        <span class="chat-title">Yeni chat</span>
        <span class="chat-time">İndi</span>
      </div>
      <div class="chat-preview">Sizə necə kömək edə bilərəm?</div>
    `;
        return btn;
    }

    function createConversationItem(c) {
        const btn = document.createElement('button');
        btn.className = 'chat-item';
        btn.type = 'button';
        btn.dataset.conversationId = String(c.id);
        btn.innerHTML = `
      <div class="chat-item-top">
        <span class="chat-title">${escapeHtml(c.title || 'Chat')}</span>
        <span class="chat-time">${escapeHtml(formatChatTime(c.last_message_at))}</span>
      </div>
      <div class="chat-preview"></div>
    `;
        return btn;
    }

    function setActiveChatItem(item) {
        if (!item || !chatList) return;

        chatList.querySelectorAll('.chat-item').forEach((el) => el.classList.remove('active'));
        item.classList.add('active');
        state.activeChatEl = item;

        if (headerTitle) headerTitle.textContent = getChatTitle(item);
        if (headerSubtitle) headerSubtitle.textContent = 'AI Assistant • Online';

        const conversationId = item.dataset.conversationId ? Number(item.dataset.conversationId) : null;

        if (!conversationId) {
            renderDraftMessages();
            return;
        }

        subscribeToConversation(conversationId);

        apiListMessages(conversationId)
            .then((data) => renderMessagesFromServer(extractMessagesArray(data)))
            .catch((e) => showSystemAiMessage(`Xəta: ${e.message}`));

        if (isMobile()) closeSidebarMobile();
    }

    function findDraftItem() {
        return [...(chatList?.querySelectorAll('.chat-item') || [])].find((el) => !el.dataset.conversationId);
    }

    async function renderConversationList(search = '') {
        if (!chatList) return;

        const activeId = getActiveConversationId();
        const data = await apiListConversations(search);
        const items = data?.items || [];

        chatList.innerHTML = '';
        const draft = createDraftChatItem();
        chatList.appendChild(draft);
        items.forEach((c) => chatList.appendChild(createConversationItem(c)));

        if (activeId) {
            const found = [...chatList.querySelectorAll('.chat-item')].find(
                (el) => Number(el.dataset.conversationId) === Number(activeId)
            );
            setActiveChatItem(found || draft);
        } else {
            setActiveChatItem(draft);
        }
    }

    chatList?.addEventListener('click', (e) => {
        const item = e.target.closest('.chat-item');
        if (!item) return;
        setActiveChatItem(item);
    });

    newChatBtn?.addEventListener('click', () => {
        if (!chatList) return;

        const existingDraft = findDraftItem();
        if (existingDraft) {
            setActiveChatItem(existingDraft);
            chatInput?.focus();
            return;
        }

        const draft = createDraftChatItem();
        chatList.prepend(draft);
        setActiveChatItem(draft);
        chatInput?.focus();
    });

    let searchT;
    chatSearch?.addEventListener('input', (e) => {
        clearTimeout(searchT);
        const q = e.target.value || '';
        searchT = setTimeout(() => {
            renderConversationList(q).catch((err) => showSystemAiMessage(`Xəta: ${err.message}`));
        }, 250);
    });

    // --------------------------
    // Top-right menu (rename/delete)
    // --------------------------
    function openChatActionMenu() {
        chatActionMenu?.classList.add('open');
        chatMenuBtn?.classList.add('active');
    }
    function closeChatActionMenu() {
        chatActionMenu?.classList.remove('open');
        chatMenuBtn?.classList.remove('active');
    }

    chatMenuBtn?.addEventListener('click', (e) => {
        e.stopPropagation();
        if (!chatActionMenu) return;
        chatActionMenu.classList.contains('open') ? closeChatActionMenu() : openChatActionMenu();
    });

    document.addEventListener('click', (e) => {
        if (
            chatActionMenu?.classList.contains('open') &&
            !e.target.closest('#chatActionMenu') &&
            !e.target.closest('#chatMenuBtn')
        ) {
            closeChatActionMenu();
        }
    });

    chatActionMenu?.addEventListener('click', async (e) => {
        const btn = e.target.closest('[data-chat-action]');
        if (!btn) return;

        const action = btn.dataset.chatAction;
        const conversationId = getActiveConversationId();

        closeChatActionMenu();

        if (!conversationId) {
            showSystemAiMessage('Draft chat üçün bu əməliyyat yoxdur.');
            return;
        }

        if (action === 'rename') {
            const current = getChatTitle(state.activeChatEl);
            const title = window.prompt('Yeni chat adı:', current);
            if (!title) return;

            try {
                await apiRenameConversation(conversationId, title);
                setChatTitle(state.activeChatEl, title);
                await renderConversationList(chatSearch?.value || '');
            } catch (err) {
                showSystemAiMessage(`Xəta: ${err.message}`);
            }
            return;
        }

        if (action === 'delete') {
            const ok = window.confirm('Bu chat silinsin?');
            if (!ok) return;

            try {
                await apiDeleteConversation(conversationId);
                state.activeChatEl?.remove();
                const draft = findDraftItem() || createDraftChatItem();
                if (!draft.isConnected) chatList.prepend(draft);
                setActiveChatItem(draft);
                await renderConversationList(chatSearch?.value || '');
            } catch (err) {
                showSystemAiMessage(`Xəta: ${err.message}`);
            }
        }
    });

    // --------------------------
    // Text send (realtime will deliver AI)
    // --------------------------
    async function sendTextFlow(text) {
        const typing = addTypingIndicator();

        try {
            let conversationId = getActiveConversationId();

            if (!conversationId) {
                const created = await apiCreateConversationWithText(text);
                conversationId = created?.conversation?.id;

                if (!conversationId) throw new Error('Conversation create response natamamdır.');

                setActiveConversationId(conversationId);
                if (created?.conversation?.title) setChatTitle(state.activeChatEl, created.conversation.title);

                subscribeToConversation(conversationId);
            } else {
                await apiSendText(conversationId, text);
            }

            // realtime gələndə typing silinir; amma ilişməsin:
            setTimeout(() => typing?.remove(), 30000);

            await renderConversationList(chatSearch?.value || '');
        } catch (e) {
            typing?.remove();
            showSystemAiMessage(`Xəta: ${e.message}`);
        }
    }

    const resizeTextarea = () => {
        if (!chatInput) return;
        chatInput.style.height = 'auto';
        chatInput.style.height = Math.min(chatInput.scrollHeight, 180) + 'px';
    };

    const updateSendBtnState = () => {
        if (!sendBtn || !chatInput) return;
        sendBtn.disabled = chatInput.value.trim().length === 0;
    };

    function sendMessage() {
        if (!chatInput) return;
        const text = chatInput.value.trim();
        if (!text) return;

        appendUserTextMessage(text);
        sendTextFlow(text);

        chatInput.value = '';
        resizeTextarea();
        updateSendBtnState();
    }

    sendBtn?.addEventListener('click', sendMessage);
    chatInput?.addEventListener('input', () => {
        resizeTextarea();
        updateSendBtnState();
    });
    chatInput?.addEventListener('keydown', (e) => {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            sendMessage();
        }
    });
    resizeTextarea();
    updateSendBtnState();

    // --------------------------
    // Recording panel helpers (duplicates safe)
    // --------------------------
    function getMainRecordingPanel() {
        return document.querySelector('footer.composer-wrap .recording-panel');
    }
    function closeAllRecordingPanels() {
        document.querySelectorAll('.recording-panel').forEach((p) => {
            p.classList.remove('is-open');
            p.hidden = true;
            p.style.display = 'none';
        });
    }
    function openMainRecordingPanel() {
        closeAllRecordingPanels();
        const p = getMainRecordingPanel();
        if (!p) return;
        p.hidden = false;
        p.classList.add('is-open');
        p.style.display = 'flex';
    }

    function mediaRecorderSupported() {
        return !!(window.MediaRecorder && navigator.mediaDevices?.getUserMedia);
    }

    function updateRecordTimer(elTimer) {
        const secs = Math.floor((Date.now() - state.record.startTs) / 1000);
        state.record.durationSec = secs;
        if (elTimer) elTimer.textContent = formatTime(secs);
    }

    function stopRecordMonitor() {
        if (state.record.rafId) cancelAnimationFrame(state.record.rafId);
        state.record.rafId = null;

        if (state.record.audioContext) state.record.audioContext.close().catch(() => { });
        state.record.audioContext = null;
        state.record.analyser = null;

        // meter reset (transform)
        const panel = getMainRecordingPanel();
        const fill = panel?.querySelector('#recordMeterFill') || panel?.querySelector('.recording-meter-fill');
        if (fill) fill.style.setProperty('transform', 'scaleX(0)', 'important');
    }

    function stopRecordStreamTracks() {
        if (state.record.stream) state.record.stream.getTracks().forEach((t) => t.stop());
        state.record.stream = null;
    }

    function stopRecordTimer() {
        if (state.record.timerInt) clearInterval(state.record.timerInt);
        state.record.timerInt = null;
    }

    async function startRecording() {
        if (!mediaRecorderSupported()) {
            showSystemAiMessage('Bu brauzerdə səs yazma dəstəklənmir.');
            return;
        }

        openMainRecordingPanel();

        const panel = getMainRecordingPanel();
        const recordTimerEl = panel?.querySelector('#recordTimer');

        // ✅ AudioContext-i await-lardan ƏVVƏL yarat (gesture qırılmasın)
        const audioCtx = new (window.AudioContext || window.webkitAudioContext)();
        try {
            await audioCtx.resume();
        } catch { }
        state.record.audioContext = audioCtx;

        try {
            const stream = await navigator.mediaDevices.getUserMedia({ audio: true });

            state.record.stream = stream;
            state.record.chunks = [];
            state.record.cancelled = false;
            state.record.startTs = Date.now();
            state.record.durationSec = 0;

            const mr = new MediaRecorder(stream);
            state.record.mediaRecorder = mr;

            mr.ondataavailable = (e) => {
                if (e.data && e.data.size > 0) state.record.chunks.push(e.data);
            };

            mr.onstop = async () => {
                const duration = state.record.durationSec;
                const cancelled = state.record.cancelled;

                stopRecordTimer();
                stopRecordMonitor();
                stopRecordStreamTracks();

                const chunks = state.record.chunks;

                state.record.mediaRecorder = null;
                state.record.chunks = [];
                state.record.cancelled = false;

                closeAllRecordingPanels();

                if (cancelled || !chunks.length) return;

                const blob = new Blob(chunks, { type: 'audio/webm' });
                appendUserAudioMessage(blob, duration);
                await sendAudioFlow(blob);
            };

            // ✅ meter (RMS + transform scaleX, CSS nə yazsa da işləyir)
            const source = audioCtx.createMediaStreamSource(stream);
            const analyser = audioCtx.createAnalyser();
            analyser.fftSize = 1024;
            analyser.smoothingTimeConstant = 0.0;
            source.connect(analyser);

            state.record.analyser = analyser;

            const data = new Uint8Array(analyser.fftSize);

            // Frequency data for waveform canvas
            const freqData = new Uint8Array(analyser.frequencyBinCount);

            // Canvas waveform setup
            const waveCanvas = document.getElementById('recordWave');
            const waveCtx = waveCanvas ? waveCanvas.getContext('2d') : null;

            const tick = () => {
                if (!state.record.analyser) return;

                state.record.analyser.getByteTimeDomainData(data);

                let sum = 0;
                for (let i = 0; i < data.length; i++) {
                    const v = (data[i] - 128) / 128;
                    sum += v * v;
                }
                const rms = Math.sqrt(sum / data.length);
                const percent = Math.min(100, Math.round(rms * 320));

                const p = getMainRecordingPanel();
                const fill = p?.querySelector('#recordMeterFill') || p?.querySelector('.recording-meter-fill');

                if (fill) {
                    fill.style.setProperty('width', '100%', 'important');
                    fill.style.setProperty('transform', `scaleX(${percent / 100})`, 'important');
                    fill.style.setProperty('transform-origin', 'left', 'important');
                    fill.style.setProperty('display', 'block', 'important');
                }

                // Draw waveform on canvas
                if (waveCtx && waveCanvas) {
                    const W = waveCanvas.width;
                    const H = waveCanvas.height;
                    const centerY = H / 2;

                    state.record.analyser.getByteFrequencyData(freqData);

                    waveCtx.clearRect(0, 0, W, H);

                    const barCount = 60;
                    const gap = 2;
                    const barWidth = (W - gap * (barCount - 1)) / barCount;
                    const step = Math.floor(freqData.length / barCount);

                    for (let i = 0; i < barCount; i++) {
                        // Average a range of frequencies for each bar
                        let val = 0;
                        for (let j = 0; j < step; j++) {
                            val += freqData[i * step + j];
                        }
                        val = val / step / 255;

                        const barH = Math.max(2, val * (H * 0.9));
                        const x = i * (barWidth + gap);
                        const y = centerY - barH / 2;

                        // Gradient color: green -> yellow -> red based on intensity
                        const hue = 120 - val * 120; // 120=green, 60=yellow, 0=red
                        waveCtx.fillStyle = `hsla(${hue}, 80%, 55%, ${0.6 + val * 0.4})`;
                        waveCtx.beginPath();
                        waveCtx.roundRect(x, y, barWidth, barH, barWidth / 2);
                        waveCtx.fill();
                    }
                }

                state.record.rafId = requestAnimationFrame(tick);
            };
            tick();

            mr.start();
            updateRecordTimer(recordTimerEl);
            state.record.timerInt = setInterval(() => updateRecordTimer(recordTimerEl), 250);
        } catch (err) {
            console.error(err);
            showSystemAiMessage('Mikrofon icazəsi alınmadı və ya cihaz tapılmadı.');

            try {
                audioCtx.close();
            } catch { }
            state.record.audioContext = null;

            closeAllRecordingPanels();
        }
    }

    function stopRecording(send = true) {
        if (!state.record.mediaRecorder) return;
        state.record.cancelled = !send;
        try {
            if (state.record.mediaRecorder.state !== 'inactive') state.record.mediaRecorder.stop();
        } catch (err) {
            console.error(err);
        }
    }

    // delegation: record/stop/cancel (ID duplications safe)
    closeAllRecordingPanels();

    document.addEventListener('click', (e) => {
        if (e.target.closest('#recordBtn')) {
            openMainRecordingPanel();
            if (!state.record.mediaRecorder) startRecording();
            return;
        }

        if (e.target.closest('#stopRecordBtn')) {
            stopRecording(true);
            return;
        }

        if (e.target.closest('#cancelRecordBtn')) {
            stopRecording(false);
            closeAllRecordingPanels();
            return;
        }
    });

    // --------------------------
    // Audio send (realtime will deliver AI)
    // --------------------------
    async function sendAudioFlow(blob) {
        const typing = addTypingIndicator();

        try {
            let conversationId = getActiveConversationId();

            if (!conversationId) {
                const created = await apiCreateConversationWithAudio(blob);
                conversationId = created?.conversation?.id;

                if (!conversationId) throw new Error('Audio create response natamamdır.');

                setActiveConversationId(conversationId);
                if (created?.conversation?.title) setChatTitle(state.activeChatEl, created.conversation.title);

                subscribeToConversation(conversationId);
            } else {
                await apiSendAudio(conversationId, blob);
            }

            setTimeout(() => typing?.remove(), 30000);
            await renderConversationList(chatSearch?.value || '');
        } catch (e) {
            typing?.remove();
            showSystemAiMessage(`Xəta: ${e.message}`);
        }
    }

    // --------------------------
    // Transcript toggle (event delegation)
    // --------------------------
    document.addEventListener('click', (e) => {
        const btn = e.target.closest('.audio-transcript-btn');
        if (!btn) return;

        const card = btn.closest('.audio-message-card');
        if (!card) return;

        const transcriptEl = card.querySelector('.audio-transcript');
        if (!transcriptEl) return;

        const isHidden = transcriptEl.classList.contains('hidden');
        transcriptEl.classList.toggle('hidden');
        btn.classList.toggle('active', isHidden);
    });

    // --------------------------
    // Init
    // --------------------------
    renderConversationList().catch((err) => showSystemAiMessage(`Xəta: ${err.message}`));

    // Live voice chat (conversation-independent)
    const liveChat = createLiveChat();

    // Cleanup
    window.addEventListener('beforeunload', () => {
        stopRecordMonitor();
        stopRecordTimer();
        stopRecordStreamTracks();
        liveChat.destroy();
    });
});
