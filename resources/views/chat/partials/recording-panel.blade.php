<div class="recording-panel" id="recordingPanel" hidden>
    <div class="recording-left">
        <span class="pulse-dot"></span>
        <span class="recording-text">Səs yazılır...</span>
        <span class="recording-timer" id="recordTimer">00:00</span>
    </div>

    <div class="recording-meter">
        <div class="recording-meter-fill" id="recordMeterFill"></div>
    </div>

    <canvas id="recordWave" class="record-wave" width="600" height="60"></canvas>

    <div class="recording-actions">
        <button type="button" class="recording-btn ghost" id="cancelRecordBtn">Ləğv et</button>
        <button type="button" class="recording-btn primary" id="stopRecordBtn">Bitir &
            Göndər</button>
    </div>
</div>
