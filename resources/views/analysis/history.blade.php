<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">История анализа</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white p-6 rounded shadow">
                <h3 class="text-2xl font-semibold mb-4">Ваши аудиофайлы</h3>

                @if(session('success'))
                    <div class="mb-4 p-3 bg-green-100 text-green-800 rounded">{{ session('success') }}</div>
                @endif
                @if(session('error'))
                    <div class="mb-4 p-3 bg-red-100 text-red-800 rounded">{{ session('error') }}</div>
                @endif

                <table class="w-full table-auto border-collapse">
                    <thead>
                    <tr class="text-left bg-gray-100">
                        <th class="p-2">Название</th>
                        <th class="p-2">Дата загрузки</th>
                        <th class="p-2">Действия</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($files as $file)
                        <tr class="border-b">
                            <td class="p-2">{{ $file->file_name }}</td>
                            <td class="p-2">{{ $file->created_at->format('d.m.Y H:i') }}</td>
                            <td class="p-2 space-x-2">
                                <a href="{{ route('analysis.show', $file) }}" class="text-blue-600 hover:underline">Посмотреть</a>
                                <form method="POST" action="{{ route('analysis.destroy', $file) }}" class="inline">
                                    @csrf @method('DELETE')
                                    <button onclick="return confirm('Удалить запись?')" class="text-red-600 hover:underline">Удалить</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>

                <div class="mt-4">{{ $files->links() }}</div>
            </div>
        </div>
    </div>
</x-app-layout>
