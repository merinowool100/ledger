<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Accounts') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    @if(session('success'))
                        <div class="mb-4 text-green-600">{{ session('success') }}</div>
                    @endif

                    <form method="POST" action="{{ route('accounts.store') }}" class="mb-6">
                        @csrf
                        <div class="flex space-x-2">
                            <input type="text" name="name" placeholder="New account name" required
                                class="px-4 py-2 border rounded w-full">
                            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded">Add</button>
                        </div>
                        @error('name')
                            <p class="text-red-600 text-sm">{{ $message }}</p>
                        @enderror
                    </form>

                    <table class="min-w-full bg-white dark:bg-gray-800">
                        <thead>
                            <tr class="border-b">
                                <th class="px-6 py-3 text-left">Name</th>
                                <th class="px-6 py-3"></th>
                                <th class="px-6 py-3"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($accounts as $acct)
                                <tr class="border-b">
                                    <td class="px-6 py-4">{{ $acct->name }}</td>
                                    <td class="px-6 py-4">
                                        <form method="POST" action="{{ route('accounts.update', $acct) }}">
                                            @csrf
                                            @method('PUT')
                                            <input type="text" name="name" value="{{ $acct->name }}" required
                                                class="px-2 py-1 border rounded">
                                            <button type="submit" class="px-2 py-1 bg-green-600 text-white rounded">Rename</button>
                                        </form>
                                    </td>
                                    <td class="px-6 py-4">
                                        <form method="POST" action="{{ route('accounts.destroy', $acct) }}">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="px-2 py-1 bg-red-600 text-white rounded" onclick="return confirm('Delete this account?');">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>