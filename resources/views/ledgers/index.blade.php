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
                                    <form method="POST" action="{{ route('ledgers.store') }}" id="newTransactionForm" onsubmit="assembleDateField(event)">
                                        @csrf
                                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-6 gap-4">
                                            <!-- Date -->
                                            <div>
                                                <label class="block text-sm font-medium mb-1">Date *</label>
                                                <div class="flex gap-2">
                                                    <!-- Year -->
                                                    <select name="date_year" id="date_year" required
                                                        class="flex-1 px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-300">
                                                        @foreach($yearOptions as $y)
                                                            <option value="{{ $y }}" {{ $y == $defaultYear ? 'selected' : '' }}>{{ $y }}</option>
                                                        @endforeach
                                                    </select>
                                                    <!-- Month -->
                                                    <select name="date_month" id="date_month" required
                                                        class="flex-1 px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-300">
                                                        @foreach($monthOptions as $m)
                                                            <option value="{{ str_pad($m, 2, '0', STR_PAD_LEFT) }}" {{ $m == $defaultMonth ? 'selected' : '' }}>{{ str_pad($m, 2, '0', STR_PAD_LEFT) }}</option>
                                                        @endforeach
                                                    </select>
                                                    <!-- Day -->
                                                    <select name="date_day" id="date_day" required
                                                        class="flex-1 px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-300">
                                                        @foreach($dayOptions as $d)
                                                            <option value="{{ str_pad($d, 2, '0', STR_PAD_LEFT) }}" {{ $d == $defaultDay ? 'selected' : '' }}>{{ str_pad($d, 2, '0', STR_PAD_LEFT) }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                @error('date')
                                                    <p class="text-red-600 dark:text-red-400 text-xs mt-1">{{ $message }}</p>
                                                @enderror
                                                <!-- Hidden input that combines the three dropdowns -->
                                                <input type="hidden" name="date" id="date" value="">
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
                <div class="mb-3 flex items-center space-x-2">
                    <form method="GET" class="flex items-center space-x-2">
                        <input type="hidden" name="year" value="{{ $year }}">
                        <input type="hidden" name="month" value="{{ $month }}">
                        <!-- Account Filter -->
                        <select name="account" id="account" onchange="this.form.submit()" class="border border-gray-400 rounded px-2 py-1 text-xs">
                            <option value="">All Accounts</option>
                            @foreach($allAccounts as $acct)
                                <option value="{{ $acct->id }}" {{ request('account')==$acct->id ? 'selected' : '' }}>{{ $acct->name }}</option>
                            @endforeach
                        </select>
                        <!-- Item Filter -->
                        <select name="item" id="item" onchange="this.form.submit()" class="border border-gray-400 rounded px-2 py-1 text-xs">
                            <option value="">All Items</option>
                            @foreach($allItems as $itemName)
                                <option value="{{ $itemName }}" {{ request('item')==$itemName ? 'selected' : '' }}>{{ $itemName }}</option>
                            @endforeach
                        </select>
                        <!-- Per-page selector -->
                        <select name="perPage" id="perPage" onchange="this.form.submit()" class="border border-gray-400 rounded px-2 py-1 text-xs">
                            <option value="15" {{ $perPage==15?'selected':'' }}>15</option>
                            <option value="30" {{ $perPage==30?'selected':'' }}>30</option>
                            <option value="50" {{ $perPage==50?'selected':'' }}>50</option>
                            <option value="100" {{ $perPage==100?'selected':'' }}>100</option>
                        </select>
                    </form>
                </div>
                <!-- total assets (from cache) -->
                <div class="mb-4 flex justify-between items-center">
                    <div>
                        <strong>Total liquid assets:</strong>
                        {{ number_format($totalLiquidAssets ?? 0) }}
                    </div>
                    <div class="flex gap-2">
                        <button id="viewTableBtn" onclick="switchToTable()" class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white font-semibold text-sm rounded-md hover:bg-indigo-700">
                            📊 Table View
                        </button>
                        <button id="viewChartBtn" onclick="switchToChart()" class="inline-flex items-center px-4 py-2 bg-gray-600 text-white font-semibold text-sm rounded-md hover:bg-gray-700">
                            📈 Chart View
                        </button>
                        <a href="{{ route('ledgers.export') }}?account={{ request('account') }}" class="inline-flex items-center px-4 py-2 bg-green-600 text-white font-semibold text-sm rounded-md hover:bg-green-700">
                            📥 CSV Export
                        </a>
                    </div>
                </div>
                    <div class="mx-auto">
                        <div class="bg-white dark:bg-gray-900 overflow-hidden shadow-sm sm:rounded-lg" id="tableSection">
                            <div class="overflow-x-auto">
                                <!-- テーブル開始 -->
                                <table class="w-full text-xs border-collapse">
                                    <thead>
                                        <tr class="bg-gray-200 dark:bg-gray-800 border-b border-gray-400 dark:border-gray-600">
                                            <th class="border border-gray-400 dark:border-gray-600 px-2 py-1 text-left font-semibold text-gray-700 dark:text-gray-300" style="width:80px;">Date</th>
                                            <th class="border border-gray-400 dark:border-gray-600 px-2 py-1 text-left font-semibold text-gray-700 dark:text-gray-300" style="width:90px;">Account</th>
                                            <th class="border border-gray-400 dark:border-gray-600 px-2 py-1 text-left font-semibold text-gray-700 dark:text-gray-300" style="width:150px;">Item</th>
                                            <th class="border border-gray-400 dark:border-gray-600 px-2 py-1 text-right font-semibold text-gray-700 dark:text-gray-300" style="width:90px;">Amount</th>
                                            <!-- Per-account balance columns -->
                                            @foreach($allAccounts as $acct)
                                                <th class="border border-gray-400 dark:border-gray-600 px-2 py-1 text-right font-semibold text-gray-700 dark:text-gray-300">{{ $acct->name }}</th>
                                            @endforeach
                                            <th class="border border-gray-400 dark:border-gray-600 px-2 py-1 text-right font-semibold text-gray-700 dark:text-gray-300" style="width:120px;">Total Assets</th>
                                            <th class="border border-gray-400 dark:border-gray-600 px-2 py-1 text-center font-semibold text-gray-700 dark:text-gray-300" style="width:70px;">Status</th>
                                            <th class="border border-gray-400 dark:border-gray-600 px-2 py-1 text-center font-semibold text-gray-700 dark:text-gray-300" style="width:70px;">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($ledgers as $index => $ledger)
                                        <tr class="border-b border-gray-300 dark:border-gray-700 hover:bg-blue-50 dark:hover:bg-gray-800 @if($index % 2 == 0) bg-gray-50 dark:bg-gray-900 @else bg-white dark:bg-gray-800 @endif">
                                            <td class="border border-gray-300 dark:border-gray-700 px-2 py-1 text-gray-900 dark:text-gray-100 whitespace-nowrap">
                                                {{ $ledger->date->format('Y-m-d') }}
                                            </td>
                                            <td class="border border-gray-300 dark:border-gray-700 px-2 py-1 text-gray-900 dark:text-gray-100">
                                                {{ optional($ledger->account)->name }}
                                            </td>
                                            <td class="border border-gray-300 dark:border-gray-700 px-2 py-1 text-gray-900 dark:text-gray-100">
                                                {{ $ledger->item }}
                                            </td>
                                            <td class="border border-gray-300 dark:border-gray-700 px-2 py-1 text-right text-gray-900 dark:text-gray-100 font-mono 
                @if($ledger->amount < 0) text-red-600 dark:text-red-400 @endif">
                                                <input type="number" class="amount-input w-20 text-right border border-gray-300 dark:border-gray-500 px-1 py-0.5 text-xs rounded"
                                                    value="{{ $ledger->amount }}" 
                                                    data-ledger-id="{{ $ledger->id }}"
                                                    @if($ledger->status === 'confirmed') disabled @endif>
                                            </td>
                                            <!-- Per-account balances for this row -->
                                            @php $rowBal = $rowBalances[$ledger->id] ?? []; @endphp
                                            @foreach($allAccounts as $acct)
                                                @php $val = $rowBal[$acct->id] ?? null; @endphp
                                                <td class="border border-gray-300 dark:border-gray-700 px-2 py-1 text-right text-gray-900 dark:text-gray-100 font-mono bg-gray-50 dark:bg-gray-800
                @if($val !== null && $val < 0) text-red-600 dark:text-red-400 @endif">
                                                    @if($val !== null)
                                                        {{ number_format($val) }}
                                                    @else
                                                        -
                                                    @endif
                                                </td>
                                            @endforeach
                                            <!-- total assets at this row -->
                                            <td class="border border-gray-300 dark:border-gray-700 px-2 py-1 text-right text-gray-900 dark:text-gray-100 font-mono font-semibold bg-gray-200 dark:bg-gray-700">
                                                {{ number_format(collect($rowBal ?: [])->sum()) }}
                                            </td>
                                            <td class="border border-gray-300 dark:border-gray-700 px-2 py-1 text-center">
                                                <span class="px-1.5 py-0.5 rounded text-xs font-semibold
                                                    @if($ledger->status === 'confirmed')
                                                        bg-green-200 text-green-800 dark:bg-green-900 dark:text-green-200
                                                    @else
                                                        bg-yellow-200 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200
                                                    @endif
                                                ">
                                                    {{ ucfirst($ledger->status) }}
                                                </span>
                                            </td>
                                            <td class="border border-gray-300 dark:border-gray-700 px-2 py-1 text-center text-xs">
                                                <div class="flex gap-1 justify-center">
                                                    @if($ledger->status === 'confirmed')
                                                        <span class="text-gray-400">Edit</span>
                                                    @else
                                                        <a href="{{ route('ledgers.edit', $ledger) }}" class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300 font-semibold text-xs">Edit</a>
                                                    @endif
                                                    
                                                    @if($ledger->status === 'pending')
                                                        <form method="POST" action="{{ route('ledgers.confirm', $ledger) }}" class="inline" onsubmit="return confirm('Confirm?');">
                                                            @csrf
                                                            <button type="submit" class="text-green-600 hover:text-green-900 dark:text-green-400 dark:hover:text-green-300 font-semibold text-xs">✓</button>
                                                        </form>
                                                    @endif
                                                    
                                                    <form method="POST" action="{{ route('ledgers.destroy', $ledger) }}" class="inline" onsubmit="return confirm('Delete?');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300 font-semibold text-xs">×</button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                                <!-- テーブル終了 -->
                                <!-- forecast chart -->
                                <div class="mt-6" id="chartSection" style="display:none;">
                                    <div id="chartPlaceholder" class="text-center text-gray-500" style="display:none;">
                                        No data to display on chart.
                                    </div>
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
        // forecast chart data — build sorted dataset client-side for correct cumulative series with actual vs planned and year-based coloring
        (function() {
            const ctx = document.getElementById('forecastChart');
            if (!ctx) return;

            // prepare ledger rows including status
            <?php $rows = $chartData; ?>
            const ledgerRows = {!! json_encode($rows) !!};

            // show placeholder when no rows
            if (ledgerRows.length === 0) {
                document.getElementById('chartPlaceholder').style.display = 'block';
                return;
            }

            // sort ascending by date then build cumulative for two series
            ledgerRows.sort((a,b)=> new Date(a.date) - new Date(b.date));
            const labels = ledgerRows.map(r=>r.date);

            const allData = [];
            const confirmedData = [];
            let cumAll = 0;
            let cumConfirmed = 0;
            ledgerRows.forEach(r=>{
                cumAll += Number(r.amount||0);
                allData.push(cumAll);
                if (r.status === 'confirmed') {
                    cumConfirmed += Number(r.amount||0);
                }
                confirmedData.push(cumConfirmed);
            });

            // helper to color segments by how many years ago
            const segmentColor = ctx => {
                const idx = ctx.p0DataIndex;
                const date = new Date(labels[idx]);
                const now = new Date();
                const years = (now - date) / (1000*60*60*24*365);
                if (years <= 1) return 'rgba(75,192,192,1)';
                if (years <= 2) return 'rgba(54,162,235,1)';
                if (years <= 3) return 'rgba(255,205,86,1)';
                if (years <= 4) return 'rgba(153,102,255,1)';
                return 'rgba(201,203,207,1)';
            };

            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [
                        {
                            label: 'All (planned)',
                            data: allData,
                            borderColor: 'rgba(75, 192, 192, 1)',
                            backgroundColor: 'rgba(75, 192, 192, 0.08)',
                            tension: 0.15,
                            fill: false,
                            segment: { borderColor: segmentColor }
                        },
                        {
                            label: 'Confirmed',
                            data: confirmedData,
                            borderColor: 'rgba(54, 162, 235, 1)',
                            backgroundColor: 'rgba(54, 162, 235, 0.08)',
                            tension: 0.15,
                            fill: false,
                            segment: { borderColor: segmentColor }
                        }
                    ]
                },
                options: {
                    responsive: true,
                    interaction: { mode: 'index', intersect: false },
                    plugins: { legend: { display: true } },
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

function switchToTable() {
    document.getElementById('tableSection').style.display = 'block';
    document.getElementById('chartSection').style.display = 'none';
    document.getElementById('viewTableBtn').classList.add('bg-indigo-600', 'hover:bg-indigo-700');
    document.getElementById('viewTableBtn').classList.remove('bg-gray-600', 'hover:bg-gray-700');
    document.getElementById('viewChartBtn').classList.add('bg-gray-600', 'hover:bg-gray-700');
    document.getElementById('viewChartBtn').classList.remove('bg-indigo-600', 'hover:bg-indigo-700');
}

function switchToChart() {
    document.getElementById('tableSection').style.display = 'none';
    document.getElementById('chartSection').style.display = 'block';
    document.getElementById('viewChartBtn').classList.add('bg-indigo-600', 'hover:bg-indigo-700');
    document.getElementById('viewChartBtn').classList.remove('bg-gray-600', 'hover:bg-gray-700');
    document.getElementById('viewTableBtn').classList.add('bg-gray-600', 'hover:bg-gray-700');
    document.getElementById('viewTableBtn').classList.remove('bg-indigo-600', 'hover:bg-indigo-700');
}

function assembleDateField(event) {
    const year = document.getElementById('date_year').value;
    const month = document.getElementById('date_month').value;
    const day = document.getElementById('date_day').value;
    document.getElementById('date').value = `${year}-${month}-${day}`;
}

// Update day options when month/year changes
document.getElementById('date_year')?.addEventListener('change', updateDayOptions);
document.getElementById('date_month')?.addEventListener('change', updateDayOptions);

function updateDayOptions() {
    const year = parseInt(document.getElementById('date_year').value);
    const month = parseInt(document.getElementById('date_month').value);
    const daySelect = document.getElementById('date_day');
    const currentDay = parseInt(daySelect.value);
    
    // Calculate days in month
    const daysInMonth = new Date(year, month, 0).getDate();
    
    // Store current options
    const oldOptions = Array.from(daySelect.options).map(opt => opt.value);
    
    // Remove all options
    while (daySelect.options.length > 0) {
        daySelect.remove(0);
    }
    
    // Add new options
    for (let d = 1; d <= daysInMonth; d++) {
        const padded = String(d).padStart(2, '0');
        const option = document.createElement('option');
        option.value = padded;
        option.textContent = padded;
        if (d === currentDay || (oldOptions.length > 0 && padded === oldOptions[oldOptions.length - 1])) {
            option.selected = true;
        }
        daySelect.appendChild(option);
    }
}

// Inline amount editing
document.querySelectorAll('.amount-input').forEach(input => {
    input.addEventListener('blur', function() {
        if (this.disabled) return; // Skip if confirmed
        
        const ledgerId = this.getAttribute('data-ledger-id');
        const newAmount = this.value;
        
        if (!newAmount || isNaN(newAmount)) {
            alert('Invalid amount');
            return;
        }
        
        // PATCH request to update
        fetch(`/ledgers/${ledgerId}/update-inline`, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                amount: parseInt(newAmount)
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Reload to show updated balance
                location.reload();
            } else {
                alert('Error updating');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error updating');
        });
    });
});
</script>

</x-app-layout>