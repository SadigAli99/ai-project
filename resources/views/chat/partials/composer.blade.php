<form class="composer" id="chatComposer" onsubmit="return false;">
    <button type="button" class="composer-icon-btn" title="Fayl əlavə et">
        <svg viewBox="0 0 24 24" aria-hidden="true">
            <path d="M12 5v14M5 12h14" />
        </svg>
    </button>

    <div class="composer-input-wrap">
        <textarea id="chatInput" rows="1" placeholder="Mesajınızı yazın..."></textarea>

        <div class="composer-tools">

            <button type="button" class="composer-tool-btn">
                <svg viewBox="0 0 24 24" aria-hidden="true">
                    <circle cx="12" cy="12" r="9" />
                    <path d="M9 10h.01M15 10h.01" />
                    <path d="M8.5 14c.8 1.2 2 2 3.5 2s2.7-.8 3.5-2" />
                </svg>
            </button>

            <button type="button" class="composer-tool-btn" id="recordBtn" title="Səsli mesaj">
                <svg viewBox="0 0 24 24" aria-hidden="true">
                    <rect x="9" y="3" width="6" height="11" rx="3" />
                    <path d="M5 11a7 7 0 0 0 14 0" />
                    <path d="M12 18v3" />
                </svg>
            </button>
        </div>
    </div>

    <button type="button" class="send-btn" id="sendBtn" title="Göndər">
        <svg viewBox="0 0 24 24" aria-hidden="true">
            <path d="M22 2L11 13" />
            <path d="M22 2L15 22L11 13L2 9L22 2Z" />
        </svg>
    </button>
</form>
