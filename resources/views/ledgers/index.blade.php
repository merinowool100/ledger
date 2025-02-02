<x-app-layout>

  <button onclick="openModal('create')" class="px-4 py-2 bg-indigo-600 text-white rounded-md shadow-md hover:bg-indigo-700 focus:outline-none">
    New Record
  </button>

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

  <!-- Modal for create -->
  <div id="ledgerModal" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 hidden">
    <div class="bg-white p-6 rounded-lg shadow-lg w-1/3">
      <h3 class="text-xl font-semibold mb-4" id="modalTitle">Record 作成</h3>
      <form method="POST" action="{{ route('ledgers.store') }}">
        @csrf

        <div class="grid grid-cols-1 gap-6">
          <!-- 日付 -->
          <div>
            <label for="date" class="block text-sm font-medium text-gray-700 dark:text-gray-300">日付</label>
            <input type="date" name="date" id="date" value="{{ old('date') }}" required class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-600">
            @error('date')
            <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
          </div>

          <!-- 項目 -->
          <div>
            <label for="item" class="block text-sm font-medium text-gray-700 dark:text-gray-300">項目</label>
            <input type="text" name="item" id="item" value="{{ old('item') }}" required class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-600">
            @error('item')
            <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
          </div>

          <!-- 金額 -->
          <div>
            <label for="amount" class="block text-sm font-medium text-gray-700 dark:text-gray-300">金額</label>
            <input type="number" name="amount" id="amount" value="{{ old('amount') }}" required class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-600">
            @error('amount')
            <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
          </div>

          <!-- 繰り返しボックス -->
          <div>
            <!-- チェックボックスにして、どちらか一方だけ選べるようにする -->
            <input type="checkbox" id="repeat_monthly" name="repeat_monthly" value="1" onclick="toggleCheckbox(this)"> Monthly
            <input type="checkbox" id="repeat_yearly" name="repeat_yearly" value="1" onclick="toggleCheckbox(this)"> Yearly

            <!-- 繰り返し終了日フィールド -->
            <div class="" id="end_date_group" style="display: none;">
              <label for="end_date">Repeat Until</label>
              <input type="date" class="form-control" id="end_date" name="end_date">
              @error('end_date')
              <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
              @enderror
            </div>
          </div>

          <!-- 保存ボタン -->
          <div class="flex justify-end">
            <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white font-semibold text-sm rounded-md shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:bg-indigo-700 dark:hover:bg-indigo-600">
              保存
            </button>
          </div>
        </div>
      </form>
      <button onclick="closeModal()" class="absolute top-2 right-2 text-gray-600">×</button>
    </div>
  </div>

  <script>
    // チェックボックスが選択された時の処理
    function toggleCheckbox(selectedCheckbox) {
      // どちらかのチェックボックスが選択されているか確認
      const repeatMonthlyChecked = document.getElementById('repeat_monthly').checked;
      const repeatYearlyChecked = document.getElementById('repeat_yearly').checked;

      // 繰り返し終了日フィールドを取得
      const endDateGroup = document.getElementById('end_date_group');

      // 月次または年次が選択されていれば終了日を表示
      if (repeatMonthlyChecked || repeatYearlyChecked) {
        endDateGroup.style.display = 'block';
      } else {
        endDateGroup.style.display = 'none';
      }

      // 他のチェックボックスを解除
      if (selectedCheckbox.checked) {
        if (selectedCheckbox.id === 'repeat_monthly') {
          document.getElementById('repeat_yearly').checked = false;
        } else {
          document.getElementById('repeat_monthly').checked = false;
        }
      }
    }

    // モーダルを開く関数
    function openModal(type) {
      document.getElementById('ledgerModal').classList.remove('hidden');
    }

    // モーダルを閉じる関数
    function closeModal() {
      document.getElementById('ledgerModal').classList.add('hidden');
    }
  </script>



</x-app-layout>