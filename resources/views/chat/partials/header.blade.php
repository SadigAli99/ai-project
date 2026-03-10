<header class="chat-header">
    <div class="chat-header-left">
        <button class="icon-btn" id="openSidebarBtn" type="button" aria-label="Menu">
            <svg viewBox="0 0 24 24" aria-hidden="true">
                <path d="M4 7h16M4 12h16M4 17h16" />
            </svg>
        </button>

        <div>
            <h1 class="chat-header-title">Yeni chat</h1>
            <p class="chat-header-subtitle">AI Assistant • Online</p>
        </div>
    </div>

    <div class="chat-header-actions">
        <button class="icon-btn" id="themeToggleBtn" type="button" title="Tema dəyiş">
            <svg id="themeIconMoon" viewBox="0 0 24 24" aria-hidden="true">
                <path d="M21 12.8A9 9 0 1 1 11.2 3a7 7 0 0 0 9.8 9.8z" />
            </svg>
        </button>

        <button class="header-live-btn" id="openLivePanelBtn" type="button" title="Canlı səs rejimi">
            <span class="live-dot"></span>
            <span>Live</span>
        </button>

        <button class="icon-btn" id="headerSearchBtn" type="button" title="Axtarış">
            <svg viewBox="0 0 24 24" aria-hidden="true">
                <path d="M21 21l-4.35-4.35" />
                <circle cx="11" cy="11" r="6" />
            </svg>
        </button>

        <div class="dropdown-wrap">
            <button class="icon-btn" id="chatMenuBtn" type="button" title="Daha çox">
                <svg viewBox="0 0 24 24" aria-hidden="true">
                    <circle cx="12" cy="5" r="1.5" />
                    <circle cx="12" cy="12" r="1.5" />
                    <circle cx="12" cy="19" r="1.5" />
                </svg>
            </button>

            <div class="dropdown-menu" id="chatActionMenu">
                <button type="button" class="dropdown-item" data-chat-action="rename">
                    <span>✏️</span>
                    <span>Chat adını dəyiş</span>
                </button>
                <button type="button" class="dropdown-item" data-chat-action="duplicate">
                    <span>📄</span>
                    <span>Chat-i kopyala (UI)</span>
                </button>
                <div class="menu-divider"></div>
                <button type="button" class="dropdown-item danger" data-chat-action="delete">
                    <span>🗑️</span>
                    <span>Chat-i sil</span>
                </button>
            </div>
        </div>
    </div>
</header>
