{{-- Rename Chat Modal --}}
<div class="modal-backdrop" id="renameModalBackdrop"></div>
<div class="modal-card" id="renameModal" role="dialog" aria-modal="true" aria-labelledby="renameModalTitle">
    <div class="modal-header">
        <h3 id="renameModalTitle">Chat adını dəyiş</h3>
        <button type="button" class="icon-btn modal-close-btn" id="renameModalCloseBtn" title="Bağla">✕</button>
    </div>
    <div class="modal-body">
        <label class="modal-label" for="renameChatInput">Yeni başlıq</label>
        <input type="text" id="renameChatInput" class="modal-input" maxlength="80"
            placeholder="Məs: Laravel Socialite setup" />
    </div>
    <div class="modal-footer">
        <button type="button" class="modal-btn ghost" id="renameModalCancelBtn">Ləğv et</button>
        <button type="button" class="modal-btn primary" id="renameModalSaveBtn">Yadda saxla</button>
    </div>
</div>
