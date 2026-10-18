@props([
    'data_description' => 'No records found',
    'info_description' => 'Try changing your search or create a new record.' ,
])

<tr>
    <td colspan="4" class="px-6 py-16 text-center">
        <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-xl bg-slate-100 text-slate-400">
            <flux:icon name="exclamation-triangle" class="h-6 w-6" />
        </div>

        <h3 class="mt-4 text-sm font-semibold text-slate-800">
            {{ $data_description }}
        </h3>

        <p class="mt-1 text-sm text-slate-500">
            {{ $info_description }}
        </p>
    </td>
</tr>
