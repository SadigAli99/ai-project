// resources/js/live.js
// Live Voice Chat — conversation-independent, real-time voice with AI

export function createLiveChat() {
    const panel = document.getElementById('livePanel');
    const backdrop = document.getElementById('livePanelBackdrop');
    const openBtn = document.getElementById('openLivePanelBtn');
    const closeBtn = document.getElementById('closeLivePanelBtn');
    const startBtn = document.getElementById('startLiveBtn');
    const stopBtn = document.getElementById('stopLiveBtn');
    const statusChip = document.getElementById('liveStatusChip');
    const statusText = document.getElementById('liveStatusText');
    const timerEl = document.getElementById('liveTimer');
    const meterFill = document.getElementById('liveMeterFill');
    const levelPercent = document.getElementById('liveLevelPercent');
    const thresholdLine = document.getElementById('liveThresholdLine');
    const sensitivityInput = document.getElementById('liveSensitivity');
    const sensitivityValue = document.getElementById('liveSensitivityValue');
    const noiseGateInput = document.getElementById('liveNoiseGate');
    const noiseGateValue = document.getElementById('liveNoiseGateValue');
    const autoMuteCheckbox = document.getElementById('liveAutoMute');
    const eventsContainer = document.getElementById('liveEvents');

    if (!panel || !startBtn || !stopBtn) return { destroy() {} };

    const SILENCE_TIMEOUT = 1500;

    const live = {
        state: 'IDLE',
        stream: null,
        audioContext: null,
        analyser: null,
        mediaRecorder: null,
        chunks: [],
        rafId: null,
        timerStart: 0,
        timerInterval: null,
        silenceStart: 0,
        history: [],
        sessionId: null,
    };

    function csrfToken() {
        return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    }

    function nowLabel() {
        const d = new Date();
        return `${String(d.getHours()).padStart(2, '0')}:${String(d.getMinutes()).padStart(2, '0')}`;
    }

    function formatTimer(ms) {
        const secs = Math.floor(ms / 1000);
        return `${String(Math.floor(secs / 60)).padStart(2, '0')}:${String(secs % 60).padStart(2, '0')}`;
    }

    function logEvent(text, cls = '') {
        if (!eventsContainer) return;
        const div = document.createElement('div');
        div.className = 'live-event' + (cls ? ' ' + cls : '');
        div.innerHTML = `<strong>${nowLabel()}</strong> — ${text}`;
        eventsContainer.prepend(div);
        while (eventsContainer.children.length > 50) eventsContainer.lastChild.remove();
    }

    function getSensitivity() {
        return parseFloat(sensitivityInput?.value || 1.5);
    }

    function getNoiseGate() {
        return parseInt(noiseGateInput?.value || 22, 10) / 100;
    }

    function setStatus(chip, text) {
        if (statusChip) {
            statusChip.className = 'status-chip';
            if (chip) statusChip.classList.add(chip);
        }
        if (statusText) statusText.textContent = text;
    }

    function updateTimer() {
        if (timerEl && live.timerStart) timerEl.textContent = formatTimer(Date.now() - live.timerStart);
    }

    function updateThresholdLine() {
        if (thresholdLine) thresholdLine.style.left = (noiseGateInput?.value || 22) + '%';
    }

    function setButtons(started) {
        if (startBtn) startBtn.disabled = started;
        if (stopBtn) stopBtn.disabled = !started;
    }

    // Panel open/close
    function openPanel() {
        panel?.classList.add('open');
        backdrop?.classList.add('open');
    }

    function closePanel() {
        if (live.state !== 'IDLE') stopLive();
        panel?.classList.remove('open');
        backdrop?.classList.remove('open');
    }

    openBtn?.addEventListener('click', openPanel);
    closeBtn?.addEventListener('click', closePanel);
    backdrop?.addEventListener('click', closePanel);

    // Settings
    sensitivityInput?.addEventListener('input', () => {
        if (sensitivityValue) sensitivityValue.textContent = getSensitivity().toFixed(1) + 'x';
    });
    noiseGateInput?.addEventListener('input', () => {
        if (noiseGateValue) noiseGateValue.textContent = noiseGateInput.value + '%';
        updateThresholdLine();
    });
    updateThresholdLine();

    // Monitor loop (meter + VAD)
    function monitorLoop() {
        if (!live.analyser) return;

        const data = new Uint8Array(live.analyser.fftSize);
        live.analyser.getByteTimeDomainData(data);

        let sum = 0;
        for (let i = 0; i < data.length; i++) {
            const v = (data[i] - 128) / 128;
            sum += v * v;
        }
        let rms = Math.sqrt(sum / data.length) * getSensitivity();
        const percent = Math.min(100, Math.round(rms * 320));
        const gate = getNoiseGate();
        const isSpeech = (percent / 100) > gate;

        if (meterFill) meterFill.style.width = (autoMuteCheckbox?.checked && !isSpeech ? 0 : percent) + '%';
        if (levelPercent) levelPercent.textContent = percent + '%';

        // VAD
        if (live.state === 'LISTENING') {
            if (isSpeech) startRecording();
        } else if (live.state === 'RECORDING') {
            if (isSpeech) {
                live.silenceStart = 0;
            } else {
                if (!live.silenceStart) live.silenceStart = Date.now();
                else if (Date.now() - live.silenceStart >= SILENCE_TIMEOUT) stopRecordingAndSend();
            }
        }

        live.rafId = requestAnimationFrame(monitorLoop);
    }

    function startRecording() {
        live.state = 'RECORDING';
        live.silenceStart = 0;
        live.chunks = [];

        try {
            const mr = new MediaRecorder(live.stream);
            live.mediaRecorder = mr;
            mr.ondataavailable = (e) => { if (e.data?.size > 0) live.chunks.push(e.data); };
            mr.onstop = () => {
                if (live.state !== 'PROCESSING') return;
                const blob = new Blob(live.chunks, { type: 'audio/webm' });
                live.chunks = [];
                live.mediaRecorder = null;
                sendToBackend(blob);
            };
            mr.start();
        } catch (err) {
            logEvent('Yazma xətası: ' + err.message);
            live.state = 'LISTENING';
            return;
        }

        setStatus('detected', 'Danışır...');
        logEvent('Səs algılandı');
    }

    function stopRecordingAndSend() {
        live.state = 'PROCESSING';
        live.silenceStart = 0;

        if (live.mediaRecorder?.state !== 'inactive') {
            try { live.mediaRecorder.stop(); } catch {}
        }

        setStatus('active', 'Emal edilir...');
        logEvent('Fasilə — cavab gözlənilir');
    }

    // Backend call — sinxron HTTP, conversation yoxdur
    async function sendToBackend(blob) {
        try {
            const fd = new FormData();
            fd.append('audio', blob, 'live.webm');
            fd.append('history', JSON.stringify(live.history));
            if (live.sessionId) fd.append('session_id', live.sessionId);

            const res = await fetch('/chat/live/respond', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrfToken(), Accept: 'application/json' },
                body: fd,
            });

            if (!res.ok) throw new Error(`HTTP ${res.status}`);
            const data = await res.json();

            if (data.session_id) live.sessionId = data.session_id;

            if (data.transcript) {
                live.history.push({ role: 'user', content: data.transcript });
                logEvent(`Sən: "${data.transcript}"`);
            }
            if (data.reply) {
                live.history.push({ role: 'assistant', content: data.reply });
                logEvent(`AI: "${data.reply}"`);
            }

            // Tarixçəni qısa saxla
            if (live.history.length > 30) live.history = live.history.slice(-20);

            if (data.audio) {
                playAiAudio(data.audio);
            } else {
                resumeListening();
            }
        } catch (err) {
            logEvent('Xəta: ' + err.message);
            resumeListening();
        }
    }

    function resumeListening() {
        if (live.state === 'IDLE') return;
        live.state = 'LISTENING';
        live.silenceStart = 0;
        setStatus('active', 'Dinləyir...');
    }

    function playAiAudio(base64) {
        live.state = 'PLAYING';
        setStatus('active', 'AI danışır...');

        const bytes = Uint8Array.from(atob(base64), (c) => c.charCodeAt(0));
        const blob = new Blob([bytes], { type: 'audio/mpeg' });
        const url = URL.createObjectURL(blob);
        const audio = new Audio(url);

        audio.addEventListener('ended', () => {
            URL.revokeObjectURL(url);
            resumeListening();
            logEvent('Dinləyir — danışmağa başla');
        });

        audio.addEventListener('error', () => {
            URL.revokeObjectURL(url);
            logEvent('Audio oxuna bilmədi');
            resumeListening();
        });

        audio.play().catch((err) => {
            logEvent('Playback xətası: ' + err.message);
            resumeListening();
        });
    }

    // Start / Stop
    async function startLive() {
        if (live.state !== 'IDLE') return;

        if (!window.MediaRecorder || !navigator.mediaDevices?.getUserMedia) {
            logEvent('Bu brauzerdə dəstəklənmir');
            return;
        }

        const audioCtx = new (window.AudioContext || window.webkitAudioContext)();
        try { await audioCtx.resume(); } catch {}
        live.audioContext = audioCtx;

        try {
            const stream = await navigator.mediaDevices.getUserMedia({ audio: true });
            live.stream = stream;

            const source = audioCtx.createMediaStreamSource(stream);
            const analyser = audioCtx.createAnalyser();
            analyser.fftSize = 1024;
            analyser.smoothingTimeConstant = 0.0;
            source.connect(analyser);
            live.analyser = analyser;

            live.history = [];
            live.timerStart = Date.now();
            live.timerInterval = setInterval(updateTimer, 250);
            updateTimer();

            setButtons(true);
            live.state = 'LISTENING';
            setStatus('active', 'Dinləyir...');
            logEvent('Canlı rejim başladıldı');

            monitorLoop();
        } catch (err) {
            logEvent('Mikrofon xətası: ' + err.message);
            try { audioCtx.close(); } catch {}
            live.audioContext = null;
        }
    }

    function endSessionOnServer() {
        if (!live.sessionId) return;
        const fd = new FormData();
        fd.append('session_id', live.sessionId);
        fd.append('_token', csrfToken());
        navigator.sendBeacon('/chat/live/end', fd);
    }

    function stopLive() {
        if (live.state === 'IDLE') return;
        endSessionOnServer();

        if (live.mediaRecorder?.state !== 'inactive') {
            try { live.mediaRecorder.stop(); } catch {}
        }
        live.mediaRecorder = null;
        live.chunks = [];

        if (live.rafId) cancelAnimationFrame(live.rafId);
        live.rafId = null;

        if (live.audioContext) { try { live.audioContext.close(); } catch {} }
        live.audioContext = null;
        live.analyser = null;

        if (live.stream) live.stream.getTracks().forEach((t) => t.stop());
        live.stream = null;

        if (live.timerInterval) clearInterval(live.timerInterval);
        live.timerInterval = null;
        live.timerStart = 0;

        live.state = 'IDLE';
        live.silenceStart = 0;
        live.history = [];
        live.sessionId = null;

        setButtons(false);
        setStatus('', 'Hazırdır');
        if (timerEl) timerEl.textContent = '00:00';
        if (meterFill) meterFill.style.width = '0%';
        if (levelPercent) levelPercent.textContent = '0%';

        logEvent('Canlı rejim dayandırıldı');
    }

    startBtn.addEventListener('click', startLive);
    stopBtn.addEventListener('click', stopLive);

    return {
        destroy() { stopLive(); },
        isActive() { return live.state !== 'IDLE'; },
    };
}
