
@props([
    'actions' => [],
    'containerClass' => 'flex flex-col gap-3 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:flex-row sm:flex-wrap sm:items-center',
    'buttonClass' => 'inline-flex items-center justify-center gap-2 rounded-lg border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition hover:border-violet-200 hover:bg-violet-50 hover:text-violet-700 focus:outline-none focus:ring-2 focus:ring-violet-500 focus:ring-offset-2',
    'iconClass' => 'h-4 w-4',
])

<div {{ $attributes->class([$containerClass]) }}>
    @foreach ($actions as $action)
        @if (($action['visible'] ?? true) && ($action['type'] ?? 'button') === 'button')
            <button
                type="button"
                @if (!empty($action['wire']))
                    wire:click="{{ $action['wire'] }}"
                @endif
                @if (!empty($action['id']))
                    id="{{ $action['id'] }}"
                @endif
                @if (!empty($action['disabled']))
                    disabled
                @endif
                @class([
                    $buttonClass,
                    $action['class'] ?? '',
                    'ml-auto' => $action['align'] ?? false,
                    'cursor-not-allowed opacity-50' => $action['disabled'] ?? false,
                ])
            >
                @if (!empty($action['icon']))
                    <flux:icon
                        :name="$action['icon']"
                        :class="$action['icon_class'] ?? $iconClass"
                    />
                @endif

                {{ $action['label'] ?? 'Action' }}
            </button>
        @endif
    @endforeach
</div>

