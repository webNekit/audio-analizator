<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Главная') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="bg-white p-6 rounded shadow">
                    <p class="mb-2"><strong>Имя:</strong> {{ auth()->user()->name }}</p>
                    <p class="mb-4"><strong>Email:</strong> {{ auth()->user()->email }}</p>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
