<x-app-layout>
    <div>
        <div class="container" style="margin:0 auto;">

            <!-- 前月・翌月ボタン -->
            <div style="height:20px;"></div>
            <div class="d-flex justify-content-between" style="display:flex; justify-content:center;">
                <a href=" {{ route('dashboard', ['year' => $prevMonth->year, 'month' => $prevMonth->month]) }}"
                    class="btn btn-outline-primary">
                    &lt; Prev
                </a>
                <div style="width:30px;"></div>
                <div class=" d-flex justify-content-between" style="display:flex; justify-content:center;">
                    After &nbsp;<strong>{{ $firstDayOfMonth->format('F') }} {{ $firstDayOfMonth->year }}</strong>
                </div>
                <div style="width:30px;"></div>
                <a href="{{ route('dashboard', ['year' => $nextMonth->year, 'month' => $nextMonth->month]) }}"
                    class="btn btn-outline-primary">
                    Next &gt;
                </a>
            </div>


            <div>
                <!-- 新規入力フォーム表示/非表示ボタン -->
                <div class=" mx-auto sm:px-6 lg:px-8 pt-4 pb-4">
                    <!-- ボタンを新規入力フォームの右上に配置 -->
                    <button id="toggleButton"
                        class="absolute top-250 left-0 m-4 w-10 h-10 rounded-full bg-indigo-600 text-white flex items-center justify-center hover:bg-indigo-700 focus:outline-none"
                        style="width:20px; height:20px;">
                        +
                    </button>
                </div>

                <div id="newInput" class="pt-4" style="display: none;">
                    <div class="mx-auto sm:px-6 lg:px-8">
                        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                            <div class="px-6 py-2 text-gray-900 dark:text-gray-100">
                                <table class="min-w-full bg-white dark:bg-gray-800">
                                    <thead>
                                        <tr class="border-b border-gray-200 dark:border-gray-700">
                                            <th
                                                class="px-6 py-3 text-left text-sm font-medium text-gray-500 dark:text-gray-300">
                                                Date</th>
                                            <th
                                                class="px-6 py-3 text-left text-sm font-medium text-gray-500 dark:text-gray-300">
                                                Item</th>
                                            <th
                                                class="px-6 py-3 text-left text-sm font-medium text-gray-500 dark:text-gray-300">
                                                Amount</th>
                                            <th
                                                class="px-6 py-3 text-left text-sm font-medium text-gray-500 dark:text-gray-300">
                                                Repeat</th>
                                            <th
                                                class="px-6 py-3 text-left text-sm font-medium text-gray-500 dark:text-gray-300">
                                                Until</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <form method="POST" action="{{ route('ledgers.store') }}">
                                            @csrf
                                            <!-- 日付 -->
                                            <tr class="border-b border-gray-200 dark:border-gray-700">
                                                <td class="px-2 py-4 whitespace-nowrap text-sm font-medium text-gray-500 dark:text-gray-300"
                                                    style="width:200px; font-size: 20px;">
                                                    <input type="date" name="date" id="date" value="{{ old('date') }}"
                                                        required
                                                        class="mt-1 block sm:min-w-[200px] px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 text-sm font-medium text-gray-500 dark:text-gray-300 dark:border-gray-600"
                                                        style="width:100%;">
                                                    @error('date')
                                                    <p class="mt-2 text-sm text-red-600 dark:text-red-400">
                                                        {{ $message }}
                                                    </p>
                                                    @enderror
                                                </td>
                                                <!-- 項目 -->
                                                <td class="px-2 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300"
                                                    style="min-width:200px;">
                                                    <input type="text" name="item" id="item" value="{{ old('item') }}"
                                                        required
                                                        class="mt-1 block px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 text-sm font-medium text-gray-500 dark:text-gray-300 dark:border-gray-600"
                                                        style="width: 100%; font-size: 20px;">
                                                    @error('item')
                                                    <p class="mt-2 text-sm text-red-600 dark:text-red-400">
                                                        {{ $message }}
                                                    </p>
                                                    @enderror
                                                </td>
                                                <!-- 金額 -->
                                                <td class="px-2 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300 text-right"
                                                    style="min-width:100px;">
                                                    <input type="number" name="amount" id="amount"
                                                        value="{{ old('amount') }}" required
                                                        class="mt-1 block px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 text-sm font-medium text-gray-500 dark:text-gray-3000 dark:border-gray-600"
                                                        style="width:100%; font-size: 20px;">
                                                    @error('amount')
                                                    <p class="mt-2 text-sm text-red-600 dark:text-red-400">
                                                        {{ $message }}
                                                    </p>
                                                    @enderror
                                                </td>
                                                <!-- 繰り返し -->
                                                <td class="px-2 py-4 whitespace-nowrap text-sm font-medium text-gray-500 dark:text-gray-300"
                                                    style="min-width:100px;">
                                                    <div>
                                                        <input type="checkbox" id="repeat_monthly" name="repeat_monthly"
                                                            class=" text-sm font-medium text-gray-500 dark:text-gray-300"
                                                            value="1" onclick="toggleCheckbox(this)"> Monthly
                                                        <br>
                                                        <input type="checkbox" id="repeat_yearly" name="repeat_yearly"
                                                            class=" text-sm font-medium text-gray-500 dark:text-gray-300"
                                                            value="1" onclick="toggleCheckbox(this)"> Yearly
                                                    </div>
                                                </td>
                                                <!-- 繰り返し終了日 -->
                                                <td class="px-2 py-4 whitespace-nowrap text-sm font-medium text-gray-500 dark:text-gray-300"
                                                    style="width:100px;">
                                                    <div class="" id="end_date_group">
                                                        <input type="date"
                                                            class="mt-1 block w-full min-w-[150px] sm:w-auto px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 text-sm font-medium text-gray-500 dark:text-gray-300 dark:border-gray-600"
                                                            style="width:100%;" id="end_date" name="end_date"
                                                            value="{{ old('end_date', old('date')) }}">
                                                        @error('end_date')
                                                        <p class="mt-2 text-sm text-red-600 dark:text-red-400">
                                                            {{ $message }}</p>
                                                        @enderror
                                                    </div>
                                                </td>
                                                <!-- 保存ボタン -->
                                                <td class="px-2 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100"
                                                    style="width:90px; font-size: 20px;">
                                                    <button type="submit"
                                                        class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white font-semibold text-sm rounded-md shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:bg-indigo-700 dark:hover:bg-indigo-600">
                                                        Save
                                                    </button>
                                                </td>
                                            </tr>
                                        </form>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>






                <div class="pt-4 pb-12" style="">
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
                                                style="min-width:200px;">Item</th>
                                            <th
                                                class="px-6 py-3 text-left text-sm font-medium text-gray-500 dark:text-gray-300">
                                                Amount</th>
                                            <th
                                                class="px-6 py-3 text-left text-sm font-medium text-gray-500 dark:text-gray-300">
                                                Balance</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($ledgers as $ledger)
                                        <tr class="border-b border-gray-500 dark:border-gray-500">
                                            <td
                                                class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-500 dark:text-gray-300">
                                                <a href="{{ route('ledgers.edit', $ledger) }}">{{ $ledger->date }}</a>
                                            </td>
                                            <td
                                                class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">
                                                <a href="{{ route('ledgers.edit', $ledger) }}">{{ $ledger->item }}</a>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300 text-right 
                @if($ledger->amount < 0) text-red-500 @endif">
                                                <a
                                                    href="{{ route('ledgers.edit', $ledger) }}">{{ number_format($ledger->amount) }}</a>
                                                <!-- 桁区切り -->
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300 text-right 
                @if($ledger->balance < 0) text-red-500 @endif" style="background-color: rgb(236, 236, 236);">
                                                <a
                                                    href="{{ route('ledgers.edit', $ledger) }}">{{ number_format($ledger->balance) }}</a>
                                                <!-- 桁区切り -->
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                                <!-- テーブル終了 -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <script>
        // ボタンがクリックされたときに、newInputの表示/非表示を切り替え
        document.getElementById('toggleButton').addEventListener('click', function() {
            var newInput = document.getElementById('newInput');
            var toggleButton = document.getElementById('toggleButton');

            if (newInput.style.display === 'none' || newInput.style.display === '') {
                newInput.style.display = 'block'; // 表示
                toggleButton.textContent = '-'; // ボタンのテキストを「-」に変更
            } else {
                newInput.style.display = 'none'; // 非表示
                toggleButton.textContent = '+'; // ボタンのテキストを「+」に変更
            }
        });
        </script>

</x-app-layout>