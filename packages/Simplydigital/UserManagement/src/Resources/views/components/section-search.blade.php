  @props([
    'title' => 'Record Title',
    'description' => 'Record description',
    'label' =>'Search Record',
  ])
  
  <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-base font-semibold text-slate-800">
                    {{ $title }}
                </h2>
                <p class="mt-1 text-sm text-slate-500">
                    {{ $description }}
                </p>
            </div>

            <div class="w-full sm:max-w-sm">
                <label for="user-search" class="sr-only">{{ $label }}</label>
                <div class="relative">
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="1.8">
                            <circle cx="11" cy="11" r="7" />
                            <path d="m20 20-4-4" />
                        </svg>
                    </div>

                    <x-input id="user-search" name="filter"
                        class="w-full !rounded-lg !border-slate-200 !py-2.5 !pl-10 !text-sm focus:!border-violet-500 focus:!ring-violet-500"
                        placeholder="Search by name or email..." wire:model.live="filter" />
                </div>
            </div>
        </div>
    </div>