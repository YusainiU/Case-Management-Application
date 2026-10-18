@props([
    'label' => 'Delete',
    'recordId',
])

<button type="button" x-data
    @click="if (confirm('Are you sure you want to delete this user?')) $wire.delete({{ $recordId }})"
    class="inline-flex items-center justify-center rounded-lg border border-red-100 bg-white px-3 py-2 text-xs font-semibold text-red-600 transition hover:border-red-200 hover:bg-red-50 hover:text-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-1">
    <flux:icon name="trash" class="h-3.5 w-3.5 mr-2" />
    {{ $label }}
</button>
