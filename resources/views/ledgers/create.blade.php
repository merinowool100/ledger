<x-app-layout>
  <x-slot name="header">
    <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
      {{ __('Record 作成') }}
    </h2>
  </x-slot>

  <div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
      <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6 text-gray-900 dark:text-gray-100">
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
                <input type="checkbox" id="repeat_monthly" name="repeat_monthly" value="1" onclick="toggleCheckbox(this)"> Monthly
                <input type="checkbox" id="repeat_yearly" name="repeat_yearly" value="1" onclick="toggleCheckbox(this)"> Yearly

                <!-- 繰り返し終了日フィールド -->
                <div class="" id="end_date_group" style="display: none;">
                  <label for="end_date">Repeat Until</label>
                  <input type="date" class="form-control" id="end_date" name="end_date" value="{{ old('end_date', old('date')) }}">
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
        </div>
      </div>
    </div>
  </div>

  <script>
  // ページ読み込み時に、繰り返しオプションが選ばれていない場合、end_date を今日の日付に設定
  // document.addEventListener('DOMContentLoaded', function() {
  //   const repeatMonthlyChecked = document.getElementById('repeat_monthly').checked;
  //   const repeatYearlyChecked = document.getElementById('repeat_yearly').checked;

    // 繰り返し設定が選ばれていない場合、end_date に今日の日付を設定
    // if (!repeatMonthlyChecked && !repeatYearlyChecked) {
    //   const today = new Date();
    //   const todayString = today.toISOString().split('T')[0];  // YYYY-MM-DD 形式に変換
    //   endDateField.value = todayString;
    // }

    // 繰り返しオプションの選択状態によって end_date フィールドを表示・非表示に
    // if (repeatMonthlyChecked || repeatYearlyChecked) {
    //   endDateGroup.style.display = 'block';
    // } else {
    //   endDateGroup.style.display = 'none';
    // }
  // });

  // チェックボックスが選択された時の処理
  function toggleCheckbox(selectedCheckbox) {
    const repeatMonthlyChecked = document.getElementById('repeat_monthly').checked;
    const repeatYearlyChecked = document.getElementById('repeat_yearly').checked;
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
</script>


</x-app-layout>
