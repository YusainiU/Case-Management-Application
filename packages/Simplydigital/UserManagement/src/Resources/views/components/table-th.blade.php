@props([
    'items' => [],
])

<thead class="bg-slate-50">
    <tr>
        @if(count($items))
            @foreach($items as $item)
                <th scope="col" class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                    {{ $item }}
                </th>
            @endforeach
        @endif
    </tr>
</thead>
