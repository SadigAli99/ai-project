{{-- Live Voice Panel (UI-only) --}}
<div class="live-panel-backdrop" id="livePanelBackdrop"></div>
<aside class="live-panel" id="livePanel" aria-hidden="true">
    <div class="live-panel-header">
        <div>
            <h3>Canlı Səs Rejimi <span class="badge-mini">UI</span></h3>
            <p>Backend qoşulmadan əvvəl live səs UI + həssaslıq ayarlarının test paneli</p>
        </div>
        <button class="icon-btn" id="closeLivePanelBtn" type="button" title="Bağla">✕</button>
    </div>

    <div class="live-status-card">
        <div class="live-status-top">
            <div class="status-chip" id="liveStatusChip">
                <span class="live-dot"></span>
                <span id="liveStatusText">Hazırdır</span>
            </div>
            <div class="live-timer" id="liveTimer">00:00</div>
        </div>

        <div class="meter-area">
            <div class="meter-label-row">
                <span>Mic Level</span>
                <span id="liveLevelPercent">0%</span>
            </div>
            <div class="meter-track">
                <div class="meter-fill" id="liveMeterFill"></div>
                <div class="meter-threshold" id="liveThresholdLine"></div>
            </div>
        </div>

        <div class="live-actions">
            <button type="button" class="modal-btn primary" id="startLiveBtn">Canlı rejimi başlat</button>
            <button type="button" class="modal-btn ghost" id="stopLiveBtn" disabled>Dayandır</button>
        </div>
    </div>

    <div class="live-settings-grid">
        <div class="setting-card">
            <div class="setting-title">Səs həssaslığı</div>
            <div class="setting-sub">Mikrofon siqnalını UI meter üçün gücləndirir</div>
            <div class="range-row">
                <input type="range" id="liveSensitivity" min="0.5" max="3" step="0.1" value="1.5">
                <span class="range-value" id="liveSensitivityValue">1.5x</span>
            </div>
        </div>

        <div class="setting-card">
            <div class="setting-title">Səs eşiyi (Noise gate)</div>
            <div class="setting-sub">Hansı səviyyədən sonra səs “aktiv” sayılsın</div>
            <div class="range-row">
                <input type="range" id="liveNoiseGate" min="0" max="100" step="1" value="22">
                <span class="range-value" id="liveNoiseGateValue">22%</span>
            </div>
        </div>

        <div class="setting-card">
            <div class="setting-title">Canlı rejim davranışı</div>
            <div class="toggle-list">
                <label class="switch-row">
                    <span>Auto mute (səssizlikdə)</span>
                    <input type="checkbox" id="liveAutoMute">
                    <span class="switch-ui"></span>
                </label>
                <label class="switch-row">
                    <span>Push-to-talk UI</span>
                    <input type="checkbox" id="livePushToTalk">
                    <span class="switch-ui"></span>
                </label>
                <label class="switch-row">
                    <span>Həssas meter mode</span>
                    <input type="checkbox" id="liveSensitiveMeter" checked>
                    <span class="switch-ui"></span>
                </label>
            </div>
        </div>

        <div class="setting-card">
            <div class="setting-title">Canlı hadisələr (UI log)</div>
            <div class="live-events" id="liveEvents">
                <div class="live-event muted">Panel hazırdır. “Canlı rejimi başlat” düyməsinə vur.</div>
            </div>
        </div>
    </div>
</aside>
