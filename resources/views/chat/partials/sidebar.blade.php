{{-- Sidebar --}}
<aside class="sidebar" id="sidebar">
    <div class="sidebar-top">
        <div class="brand">
            <span class="brand-dot"></span>
            <span>{{ config('app.name', 'MyApp') }} AI</span>
        </div>

        <button class="icon-btn mobile-only" id="closeSidebarBtn" type="button" aria-label="Bağla">
            ✕
        </button>
    </div>

    <button class="new-chat-btn" type="button" id="newChatBtn">
        <span class="plus">＋</span>
        <span>Yeni chat</span>
    </button>

    <div class="search-box">
        <svg viewBox="0 0 24 24" aria-hidden="true">
            <path d="M21 21l-4.35-4.35" />
            <circle cx="11" cy="11" r="6" />
        </svg>
        <input type="text" placeholder="Chat axtar..." id="chatSearch">
    </div>

    <div class="chat-list" id="chatList">
    </div>

    <div class="sidebar-bottom">
        <div class="user-card">
            <div class="avatar">{{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}</div>
            <div class="user-meta">
                <div class="user-name">{{ auth()->user()->name ?? 'İstifadəçi' }}</div>
                <div class="user-email">{{ auth()->user()->email ?? 'user@example.com' }}</div>
            </div>
        </div>
    </div>
</aside>
