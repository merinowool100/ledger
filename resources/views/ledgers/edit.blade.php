<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Record 編集') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <form method="POST" action="{{ route('ledgers.update', $ledger) }}">
                        @csrf
                        @method('PUT')
                        <!-- PUTメソッドを使用 -->

                        <div class="grid grid-cols-1 gap-6">
                            <!-- 日付 -->
                            <div>
                                <label for="date"
                                    class="block text-sm font-medium text-gray-700 dark:text-gray-300">Date</label>
                                <input type="date" name="date" id="date" value="{{ old('date', $ledger->date) }}"
                                    required
                                    class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-600">
                                @error('date')
                                <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- 項目 -->
                            <div>
                                <label for="item"
                                    class="block text-sm font-medium text-gray-700 dark:text-gray-300">Item</label>
                                <input type="text" name="item" id="item" value="{{ old('item', $ledger->item) }}"
                                    required
                                    class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-600">
                                @error('item')
                                <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- 金額 -->
                            <div>
                                <label for="amount"
                                    class="block text-sm font-medium text-gray-700 dark:text-gray-300">Amount</label>
                                <input type="number" name="amount" id="amount"
                                    value="{{ old('amount', $ledger->amount) }}" required
                                    class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-600">
                                @error('amount')
                                <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- 以降の日付をまとめて更新または削除するかのチェックボックス -->
                            @if($ledger->group_id !== null)
                            <!-- group_idがnullでない場合のみ表示 -->
                            <div>
                                <label for="apply_to_later_dates" class="inline-flex items-center">
                                    <input type="checkbox" name="apply_to_later_dates" id="apply_to_later_dates"
                                        {{ old('apply_to_later_dates', $ledger->group_id ? 'checked' : '') }}>
                                    Apply to later dates as well
                                </label>
                            </div>
                            @endif

                            <!-- 更新ボタン -->
                            <div class="flex justify-end space-x-4">
                                <button type="submit" name="action" value="update"
                                    class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white font-semibold text-sm rounded-md shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:bg-indigo-700 dark:hover:bg-indigo-600">
                                    Update
                                </button>

                                <!-- 削除ボタン -->
                                <button type="submit" name="action" value="delete"
                                    class="inline-flex items-center px-4 py-2 bg-red-600 text-white font-semibold text-sm rounded-md shadow-sm hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 dark:bg-red-700 dark:hover:bg-red-600">
                                    Delete
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>