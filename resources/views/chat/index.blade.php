<!DOCTYPE html>
<html lang="az">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>AI Chat</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- Vite --}}
    @vite('resources/js/chat.js')
</head>

<body data-theme="dark">
    <div class="chat-app" id="chatApp" data-text-url="/chat/messages/text" data-audio-url="/chat/messages/audio">

        {{-- Mobile overlay --}}
        <div class="sidebar-overlay" id="sidebarOverlay"></div>

        @include('chat.partials.sidebar')

        {{-- Main Chat Area --}}
        <main class="chat-main">
            @include('chat.partials.header')

            @include('chat.partials.messages')

            {{-- Composer --}}
            <footer class="composer-wrap">

                @include('chat.partials.recording-panel')
                @include('chat.partials.composer')

                <div class="composer-note">
                    AI cavabları yoxlanmalıdır • Həssas məlumat paylaşmayın
                </div>
            </footer>
        </main>
    </div>

    @include('chat.partials.rename-chat')

    @include('chat.partials.live-chat')
</body>

</html>
