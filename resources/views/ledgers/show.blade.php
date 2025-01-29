<x-app-layout>
  <x-slot name="header">
    <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
      {{ __('Record 詳細') }}
    </h2>
  </x-slot>

  <div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
      <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6 text-gray-900 dark:text-gray-100">
          <!-- 詳細表示部分 -->
          <div class="mb-6">
            <p class="text-lg font-medium text-gray-900 dark:text-gray-100">日付: {{ $ledger->date }}</p>
            <p class="text-lg font-medium text-gray-900 dark:text-gray-100">項目: {{ $ledger->item }}</p>
            <p class="text-lg font-medium text-gray-900 dark:text-gray-100">金額: ¥{{ number_format($ledger->amount) }}</p>
          </div>

          <!-- 投稿者情報 -->
          <div class="mb-6">
            <p class="text-sm text-gray-600 dark:text-gray-400">投稿者: {{ $ledger->user->name }}</p>
          </div>

          <!-- 編集と削除のリンク -->
          <div class="flex space-x-4">
            <a href="{{ route('ledgers.edit', $ledger) }}" class="text-indigo-600 hover:text-indigo-900">編集</a>

            <form action="{{ route('ledgers.destroy', $ledger) }}" method="POST" onsubmit="return confirm('本当に削除しますか？');">
              @csrf
              @method('DELETE')
              <button type="submit" class="text-red-600 hover:text-red-900">削除</button>
            </form>
          </div>

          <!-- 戻るボタン -->
          <div class="mt-6">
            <a href="{{ route('ledgers.index') }}" class="text-blue-500 hover:text-blue-700">一覧に戻る</a>
          </div>
        </div>
      </div>
    </div>
  </div>
</x-app-layout>
