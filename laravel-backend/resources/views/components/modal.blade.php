@props(['id', 'title'])

<div id="{{ $id }}" tabindex="-1" aria-hidden="true" class="hidden fixed inset-0 z-50 overflow-y-auto">
    <!-- Backdrop -->
    <div class="fixed inset-0 bg-black bg-opacity-50"></div>
    <!-- Modal Content -->
    <div class="relative flex items-center justify-center min-h-screen">
    <div class="relative w-full max-w-[800px] bg-white rounded-lg shadow dark:bg-gray-700">
    <!-- Modal Header -->
            <div class="flex items-center justify-between p-4 border-b dark:border-gray-600">
                <h3 class="text-xl font-semibold text-gray-900 dark:text-white">{{ $title }}</h3>
                <button type="button" class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm p-1.5 dark:hover:bg-gray-600 dark:hover:text-white" data-modal-hide="{{ $id }}">
                    <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6" />
                    </svg>
                    <span class="sr-only">Close modal</span>
                </button>
            </div>
            <!-- Modal Body -->
            <div class="p-6">
                {{ $slot }}
            </div>
            <!-- Modal Footer -->
            @isset($footer)
            <div class="flex items-center p-4 border-t dark:border-gray-600">
                {{ $footer }}
            </div>
            @endisset
        </div>
    </div>
</div>