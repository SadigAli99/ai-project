// resources/js/backend.js
function csrfToken() {
    return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
}

async function parseErr(res) {
    let msg = `HTTP ${res.status}`;
    try {
        const data = await res.json();
        msg = data?.message || msg;
    } catch { }
    return new Error(msg);
}

async function getJson(url) {
    const res = await fetch(url, { headers: { Accept: 'application/json' } });
    if (!res.ok) throw await parseErr(res);
    return res.json();
}

async function postJson(url, body) {
    const res = await fetch(url, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': csrfToken(),
            Accept: 'application/json',
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(body),
    });
    if (!res.ok) throw await parseErr(res);
    return res.json();
}

async function patchJson(url, body) {
    const res = await fetch(url, {
        method: 'PATCH',
        headers: {
            'X-CSRF-TOKEN': csrfToken(),
            Accept: 'application/json',
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(body),
    });
    if (!res.ok) throw await parseErr(res);
    return res.json();
}

async function delJson(url) {
    const res = await fetch(url, {
        method: 'DELETE',
        headers: { 'X-CSRF-TOKEN': csrfToken(), Accept: 'application/json' },
    });
    if (!res.ok) throw await parseErr(res);
    return res.json();
}

async function postForm(url, formData) {
    const res = await fetch(url, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': csrfToken(), Accept: 'application/json' },
        body: formData,
    });
    if (!res.ok) throw await parseErr(res);
    return res.json();
}

// Conversations
export function apiListConversations(search = '') {
    const qs = search ? `?search=${encodeURIComponent(search)}` : '';
    return getJson(`/conversations${qs}`);
}

export function apiCreateConversationWithText(content) {
    const fd = new FormData();
    fd.append('message_type', 'text');
    fd.append('content', content);
    return postForm('/conversations', fd);
}

export function apiCreateConversationWithAudio(blob) {
    const fd = new FormData();
    fd.append('message_type', 'audio');
    fd.append('audio', blob, 'audio.webm');
    return postForm('/conversations', fd);
}

export function apiRenameConversation(conversationId, title) {
    return patchJson(`/conversations/${conversationId}/update`, { title });
}

export function apiDeleteConversation(conversationId) {
    return delJson(`/conversations/${conversationId}/delete`);
}

// Messages
export function apiListMessages(conversationId) {
    return getJson(`/chat/messages/${conversationId}/list`);
}

export function apiSendText(conversationId, content) {
    return postJson(`/chat/messages/${conversationId}/send-text`, { content });
}

export function apiSendAudio(conversationId, blob) {
    const fd = new FormData();
    fd.append('audio', blob, 'audio.webm');
    return postForm(`/chat/message/${conversationId}/send-audio`, fd);
}