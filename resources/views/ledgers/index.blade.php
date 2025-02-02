<x-app-layout>
  <div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
      <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6 text-gray-900 dark:text-gray-100">
          <!-- テーブル開始 -->
          <table class="min-w-full bg-white dark:bg-gray-800">
            <thead>
              <tr class="border-b border-gray-200 dark:border-gray-700">
                <th class="px-6 py-3 text-left text-sm font-medium text-gray-500 dark:text-gray-300">日付</th>
                <th class="px-6 py-3 text-left text-sm font-medium text-gray-500 dark:text-gray-300">項目</th>
                <th class="px-6 py-3 text-left text-sm font-medium text-gray-500 dark:text-gray-300">金額</th>
                <th class="px-6 py-3 text-left text-sm font-medium text-gray-500 dark:text-gray-300">編集</th>
              </tr>
            </thead>
            <tbody>
              @foreach ($ledgers as $ledger)
              <tr class="border-b border-gray-200 dark:border-gray-700">
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">{{ $ledger->date }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">{{ $ledger->item }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300 text-right 
                @if($ledger->amount < 0) text-red-500 @endif">
                  {{ number_format($ledger->amount) }} <!-- 桁区切り -->
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-blue-500 hover:text-blue-700">
                  <a href="{{ route('ledgers.edit', $ledger) }}">Edit</a> <!-- 詳細を編集に変更 -->
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
</x-app-layout>