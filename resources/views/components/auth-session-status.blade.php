@props(['status'])

@if ($status)
    <div class="mb-4 font-medium text-sm text-red-600">
        {{ $status }}
    </div>
@endif
