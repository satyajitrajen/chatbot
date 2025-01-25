@if ($type === 'error' && $messages)
    <div class="bg-red-100 text-red-800 px-4 py-3 rounded-lg mb-4">
        @if (is_array($messages))
            <ul class="list-disc pl-5">
                @foreach ($messages as $message)
                    <li>{{ $message }}</li>
                @endforeach
            </ul>
        @else
            {{ $messages }}
        @endif
    </div>
@endif

@if ($type === 'success' && $messages)
    <div class="bg-green-100 text-green-800 px-4 py-3 rounded-lg mb-4">
        {{ $messages }}
    </div>
@endif
