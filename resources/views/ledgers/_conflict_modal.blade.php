<div id="conflictModal" style="display:none; position:fixed; left:0; top:0; right:0; bottom:0; background:rgba(0,0,0,0.5); z-index:9999;">
    <div style="max-width:800px; margin:80px auto; background:#fff; padding:16px; border-radius:6px;">
        <h3>Conflict detected</h3>
        <p>Server and local versions differ. Choose which to keep or review details.</p>
        <div style="display:flex; gap:12px;">
            <div style="flex:1;">
                <h4>Your change</h4>
                <div id="conflict_fields_local" style="background:#f7f7f7; padding:8px;"></div>
            </div>
            <div style="flex:1;">
                <h4>Server</h4>
                <div id="conflict_fields_server" style="background:#f7f7f7; padding:8px;"></div>
            </div>
        </div>
        <div style="margin-top:12px;">
            <p>Select per-field source and press <strong>Apply merged</strong> to send merged patch to server.</p>
            <div id="conflict_field_selector" style="display:flex; gap:12px; flex-wrap:wrap; margin-top:8px;"></div>
        </div>
        <div style="text-align:right; margin-top:12px;">
            <button id="conflictClose" class="inline-flex items-center px-4 py-2 bg-gray-300 rounded">Close</button>
            <button id="conflictApplyMerged" class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded">Apply merged</button>
            <button id="conflictKeepServer" class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded">Use Server</button>
        </div>
    </div>
</div>
