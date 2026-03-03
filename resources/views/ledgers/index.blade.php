<x-app-layout>
    <div>
        <div class="container" style="margin:0 auto;">

            <!-- Header: latest confirmed transaction anchor -->
            <div style="height:20px;"></div>
            <div class="d-flex justify-content-between" style="display:flex; justify-content:space-between; align-items:center;">
                <div style="font-size:24px; font-weight:600;">Cashflow</div>
                <div class="text-sm text-gray-600">Latest confirmed: <strong>{{ $latestConfirmed }}</strong></div>
            </div>


            <div>
                <!-- New Transaction Form Toggle -->
                <div class="mb-4 flex justify-between items-center">
                    <button id="toggleButton"
                        class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white font-semibold text-sm rounded-md hover:bg-indigo-700 focus:outline-none transition">
                        <span id="toggleText">+ New Transaction</span>
                    </button>
                </div>

                <!-- New Entry Form (Hidden by default) -->
                <div id="newInput" class="mb-6" style="display: none;">
                    <div class="mx-auto">
                        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                            <div class="px-6 py-6 text-gray-900 dark:text-gray-100">
                                @if($allAccounts->isEmpty())
                                    <div class="text-center text-red-600">
                                        Please <a href="{{ route('accounts.index') }}" class="underline font-semibold">create an account</a> before adding transactions.
                                    </div>
                                @else
                                    <form method="POST" action="{{ route('ledgers.store') }}" id="newTransactionForm">
                                        @csrf
                                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-6 gap-4">
                                            <!-- Date -->
                                            <div>
                                                <label for="date" class="block text-sm font-medium mb-1">Date *</label>
                                                <input type="date" name="date" id="date" value="{{ old('date') }}"
                                                    required
                                                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-300">
                                                @error('date')
                                                    <p class="text-red-600 dark:text-red-400 text-xs mt-1">{{ $message }}</p>
                                                @enderror
                                            </div>

                                            <!-- Account -->
                                            <div>
                                                <label for="account_id" class="block text-sm font-medium mb-1">Account *</label>
                                                <select name="account_id" id="account_id" required
                                                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-300">
                                                    <option value="">-- select --</option>
                                                    @foreach($allAccounts as $acct)
                                                        <option value="{{ $acct->id }}" {{ old('account_id')==$acct->id ? 'selected' : '' }}>{{ $acct->name }}</option>
                                                    @endforeach
                                                </select>
                                                @error('account_id')
                                                    <p class="text-red-600 dark:text-red-400 text-xs mt-1">{{ $message }}</p>
                                                @enderror
                                            </div>

                                            <!-- Item -->
                                            <div>
                                                <label for="item" class="block text-sm font-medium mb-1">Item *</label>
                                                <input type="text" name="item" id="item" value="{{ old('item') }}"
                                                    placeholder="e.g. Salary"
                                                    required
                                                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-300">
                                                @error('item')
                                                    <p class="text-red-600 dark:text-red-400 text-xs mt-1">{{ $message }}</p>
                                                @enderror
                                            </div>

                                            <!-- Amount -->
                                            <div>
                                                <label for="amount" class="block text-sm font-medium mb-1">Amount *</label>
                                                <input type="number" name="amount" id="amount"
                                                    placeholder="0"
                                                    value="{{ old('amount') }}" required
                                                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-300">
                                                @error('amount')
                                                    <p class="text-red-600 dark:text-red-400 text-xs mt-1">{{ $message }}</p>
                                                @enderror
                                            </div>

                                            <!-- Repeat -->
                                            <div>
                                                <label class="block text-sm font-medium mb-1">Repeat</label>
                                                <div class="flex gap-2 pt-2">
                                                    <label class="inline-flex items-center">
                                                        <input type="checkbox" name="repeat_monthly" id="repeat_monthly"
                                                            value="1" onclick="toggleCheckbox(this)"
                                                            class="rounded">
                                                        <span class="text-sm ml-1">Monthly</span>
                                                    </label>
                                                    <label class="inline-flex items-center">
                                                        <input type="checkbox" name="repeat_yearly" id="repeat_yearly"
                                                            value="1" onclick="toggleCheckbox(this)"
                                                            class="rounded">
                                                        <span class="text-sm ml-1">Yearly</span>
                                                    </label>
                                                </div>
                                            </div>

                                            <!-- Until (shown only if repeat selected) -->
                                            <div id="until_wrapper" style="display: none;">
                                                <label for="end_date" class="block text-sm font-medium mb-1">Until</label>
                                                <input type="date" id="end_date" name="end_date"
                                                    value="{{ old('end_date', old('date')) }}"
                                                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-300">
                                                @error('end_date')
                                                    <p class="text-red-600 dark:text-red-400 text-xs mt-1">{{ $message }}</p>
                                                @enderror
                                            </div>
                                        </div>

                                        <!-- Submit Button -->
                                        <div class="mt-4 flex justify-end gap-2">
                                            <button type="button" onclick="document.getElementById('newInput').style.display='none'; document.getElementById('toggleText').textContent='+ New Transaction';"
                                                class="inline-flex items-center px-4 py-2 border border-gray-300 text-gray-700 font-semibold text-sm rounded-md hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700">
                                                Cancel
                                            </button>
                                            <button type="submit"
                                                class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white font-semibold text-sm rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:bg-indigo-700 dark:hover:bg-indigo-600">
                                                Save Transaction
                                            </button>
                                        </div>
                                    </form>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>








                <div class="pt-4 pb-12" style="">
                <!-- filters and pagination controls -->
                <div class="mb-4 flex items-center space-x-4">
                    <form method="GET" class="flex items-center space-x-2">
                        <input type="hidden" name="year" value="{{ $year }}">
                        <input type="hidden" name="month" value="{{ $month }}">
                        <label for="account" class="text-sm text-gray-500">Account</label>
                        <select name="account" id="account" onchange="this.form.submit()" class="border rounded">
                            <option value="">All</option>
                            @foreach($allAccounts as $acct)
                                <option value="{{ $acct->id }}" {{ request('account')==$acct->id ? 'selected' : '' }}>{{ $acct->name }}</option>
                            @endforeach
                        </select>
                        <label for="perPage" class="text-sm text-gray-500">Show</label>
                        <select name="perPage" id="perPage" onchange="this.form.submit()" class="border rounded">
                            <option value="10" {{ $perPage==10?'selected':'' }}>10</option>
                            <option value="30" {{ $perPage==30?'selected':'' }}>30</option>
                            <option value="50" {{ $perPage==50?'selected':'' }}>50</option>
                        </select>
                        <span class="text-sm text-gray-500">entries</span>
                    </form>
                </div>
                <!-- total assets (from cache) -->
                <div class="mb-4 flex justify-between items-center">
                    <div>
                        <strong>Total liquid assets:</strong>
                        {{ number_format($totalLiquidAssets ?? 0) }}
                    </div>
                    <a href="{{ route('ledgers.export') }}?account={{ request('account') }}&perPage={{ $perPage }}" class="inline-flex items-center px-4 py-2 bg-green-600 text-white font-semibold text-sm rounded-md hover:bg-green-700">
                        📥 CSV Export
                    </a>
                </div>
                    <div class="mx-auto sm:px-6 lg:px-8">
                        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                            <div class="px-6 py-2 text-gray-900 dark:text-gray-100">
                                <!-- テーブル開始 -->
                                <table class="min-w-full bg-white dark:bg-gray-800">
                                    <thead>
                                        <tr class="border-b border-gray-200 dark:border-gray-700">
                                            <th class="px-6 py-3 text-left text-sm font-medium text-gray-500 dark:text-gray-300"
                                                style="width:200px;">Date</th>
                                            <th class="px-6 py-3 text-left text-sm font-medium text-gray-500 dark:text-gray-300"
                                                style="min-width:120px;">Account</th>
                                            <th class="px-6 py-3 text-left text-sm font-medium text-gray-500 dark:text-gray-300"
                                                style="min-width:200px;">Item</th>
                                            <th
                                                class="px-6 py-3 text-left text-sm font-medium text-gray-500 dark:text-gray-300">
                                                Amount</th>
                                            <th
                                                class="px-6 py-3 text-left text-sm font-medium text-gray-500 dark:text-gray-300">
                                                Balance</th>
                                            <th
                                                class="px-6 py-3 text-left text-sm font-medium text-gray-500 dark:text-gray-300"
                                                style="width:140px;">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($ledgers as $ledger)
                                        <tr class="border-b border-gray-500 dark:border-gray-500 hover:bg-gray-50 dark:hover:bg-gray-700">
                                            <td
                                                class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">
                                                {{ $ledger->date }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                                {{ optional($ledger->account)->name }}
                                            </td>
                                            <td
                                                class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                                {{ $ledger->item }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100 text-right 
                @if($ledger->amount < 0) text-red-600 font-semibold @endif">
                                                {{ number_format($ledger->amount) }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100 text-right font-semibold
                @if($ledger->balance < 0) text-red-600 @endif" style="background-color: rgb(236, 236, 236);">
                                                {{ number_format($ledger->balance) }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                                <div class="flex gap-2">
                                                    <a href="{{ route('ledgers.edit', $ledger) }}" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300 font-medium">Edit</a>
                                                    <form method="POST" action="{{ route('ledgers.destroy', $ledger) }}" class="inline" onsubmit="return confirm('Are you sure?');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300 font-medium">Delete</button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                                <!-- テーブル終了 -->
                                <div class="mt-4">
                                    {{ $ledgers->links() }}
                                </div>
                                <!-- forecast chart -->
                                <div class="mt-6">
                                    <canvas id="forecastChart" height="200"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script>
        // forecast chart data — build sorted dataset client-side for correct cumulative series
        (function() {
            const ctx = document.getElementById('forecastChart');
            if (!ctx) return;

            // prepare ledger rows as [{date, amount}]
            <?php $rows = $ledgers->map(function($l){ return ['date' => (string)$l->date, 'amount' => (float)$l->amount]; }); ?>
            const ledgerRows = {!! json_encode($rows) !!};

            // sort ascending by date then build cumulative
            ledgerRows.sort((a,b)=> new Date(a.date) - new Date(b.date));
            const labels = ledgerRows.map(r=>r.date);
            const data = [];
            let cumulative = 0;
            ledgerRows.forEach(r=>{ cumulative += Number(r.amount||0); data.push(cumulative); });

            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Cumulative',
                        data: data,
                        borderColor: 'rgba(75, 192, 192, 1)',
                        backgroundColor: 'rgba(75, 192, 192, 0.08)',
                        tension: 0.15,
                        fill: true,
                    }]
                },
                options: {
                    responsive: true,
                    interaction: { mode: 'index', intersect: false },
                    plugins: { legend: { display: false } },
                    scales: { y: { beginAtZero: true, ticks: { callback: v => Number(v).toLocaleString() } } }
                }
            });
        })();

        // Toggle new transaction form visibility
        document.getElementById('toggleButton').addEventListener('click', function() {
            const newInput = document.getElementById('newInput');
            const toggleText = document.getElementById('toggleText');

            if (newInput.style.display === 'none' || newInput.style.display === '') {
                newInput.style.display = 'block';
                toggleText.textContent = '- Close Form';
            } else {
                newInput.style.display = 'none';
                toggleText.textContent = '+ New Transaction';
            }
        });

        // Show/hide "Until" field when repeat checkboxes are toggled
        function toggleCheckbox(checkbox) {
            const repeatMonthly = document.getElementById('repeat_monthly').checked;
            const repeatYearly = document.getElementById('repeat_yearly').checked;
            const untilWrapper = document.getElementById('until_wrapper');

            if (repeatMonthly || repeatYearly) {
                untilWrapper.style.display = 'block';
            } else {
                untilWrapper.style.display = 'none';
            }

            // Only allow one repeat type
            if (checkbox.checked) {
                if (checkbox.id === 'repeat_monthly') {
                    document.getElementById('repeat_yearly').checked = false;
                } else {
                    document.getElementById('repeat_monthly').checked = false;
                }
            }
        }
        </script>

@include('ledgers._conflict_modal')

<script>
// helper: send patches to server via AJAX and handle 409
async function pushPatches(patches) {
    try {
        const res = await fetch('/sync', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') },
            body: JSON.stringify({ ledgers: patches })
        });

        if (res.status === 409) {
            const json = await res.json();
            if (json.conflicts && json.conflicts.length) {
                // fetch server copy for first conflict and show modal
                const tx = json.conflicts[0].transaction_id;
                const serverRes = await fetch(`/sync/tx/${tx}`);
                const serverJson = await serverRes.json();
                const localObj = patches.find(p => p.transaction_id === tx) || {};
                showConflictModal(localObj, serverJson.ledger);
            }
            return { ok: false, conflicts: true };
        }

        const body = await res.json();
        return { ok: true, body };
    } catch (e) {
        console.error(e);
        return { ok: false, error: e };
    }
}

function showConflictModal(localObj, serverObj) {
    const localEl = document.getElementById('conflict_fields_local');
    const serverEl = document.getElementById('conflict_fields_server');
    const selectorEl = document.getElementById('conflict_field_selector');
    localEl.textContent = JSON.stringify(localObj, null, 2);
    serverEl.textContent = JSON.stringify(serverObj, null, 2);
    selectorEl.innerHTML = '';

    const keys = new Set([...Object.keys(serverObj || {}), ...Object.keys(localObj || {})]);
    keys.forEach(key => {
        // skip metadata fields we don't want to merge manually
        if (['updated_at', 'created_at'].includes(key)) return;
        const localVal = localObj[key] === undefined ? '' : String(localObj[key]);
        const serverVal = serverObj[key] === undefined ? '' : String(serverObj[key]);
        const row = document.createElement('div');
        row.style.minWidth = '220px';
        row.style.border = '1px solid #eee';
        row.style.padding = '6px';
        row.style.borderRadius = '4px';
        const title = document.createElement('div');
        title.style.fontWeight = '600';
        title.textContent = key;
        const choices = document.createElement('div');
        choices.style.marginTop = '6px';
        const radioLocal = document.createElement('input');
        radioLocal.type = 'radio';
        radioLocal.name = 'field_' + key;
        radioLocal.value = 'local';
        const labelLocal = document.createElement('label');
        labelLocal.style.marginRight = '8px';
        labelLocal.appendChild(radioLocal);
        labelLocal.appendChild(document.createTextNode(' Local: ' + localVal));

        const radioServer = document.createElement('input');
        radioServer.type = 'radio';
        radioServer.name = 'field_' + key;
        radioServer.value = 'server';
        const labelServer = document.createElement('label');
        labelServer.appendChild(radioServer);
        labelServer.appendChild(document.createTextNode(' Server: ' + serverVal));

        // default selection: prefer local when differs, otherwise server
        if (localVal !== serverVal) radioLocal.checked = true; else radioServer.checked = true;

        choices.appendChild(labelLocal);
        choices.appendChild(document.createElement('br'));
        choices.appendChild(labelServer);

        row.appendChild(title);
        row.appendChild(choices);
        selectorEl.appendChild(row);
    });

    document.getElementById('conflictModal').style.display = 'block';
}

document.getElementById('conflictClose').addEventListener('click', function(){
    document.getElementById('conflictModal').style.display = 'none';
});
document.getElementById('conflictKeepServer').addEventListener('click', function(){
    // just refresh to pull server state
    document.getElementById('conflictModal').style.display = 'none';
    location.reload();
});
document.getElementById('conflictApplyMerged').addEventListener('click', async function(){
    const selectorEl = document.getElementById('conflict_field_selector');
    const radios = selectorEl.querySelectorAll('input[type=radio]');
    const grouped = {};
    radios.forEach(r => {
        const name = r.name.replace(/^field_/, '');
        if (!grouped[name]) grouped[name] = selectorEl.querySelector('input[name="' + r.name + '"]:checked').value;
    });
    // rebuild merged object using server as base
    const server = JSON.parse(document.getElementById('conflict_fields_server').textContent || '{}');
    const local = JSON.parse(document.getElementById('conflict_fields_local').textContent || '{}');
    const merged = Object.assign({}, server);
    Object.keys(grouped).forEach(k => {
        if (grouped[k] === 'local') merged[k] = local[k];
        else merged[k] = server[k];
    });
    // bump version and mark force
    merged.version = (Math.max(Number(local.version||0), Number(server.version||0)) || 0) + 1;
    merged.force = true;
    // ensure transaction_id is present
    if (!merged.transaction_id && local.transaction_id) merged.transaction_id = local.transaction_id;

    await pushPatches([merged]);
    document.getElementById('conflictModal').style.display = 'none';
    location.reload();
});
</script>

</x-app-layout>