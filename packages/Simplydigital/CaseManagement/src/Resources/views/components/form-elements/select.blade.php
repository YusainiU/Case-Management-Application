<div>
    <select
        wire:model.live="{{ $model }}"
        id="{{ $name }}"
        name="{{ $name }}"
        {{  $multiple ?? ''  }}
        class="appearance-none text-black block w-full border border-gray-700 rounded py-3 px-4 mb-3 leading-tight focus:outline-none focus:bg-white focus:border-gray-500"
    >
        <option value="">-- {{ $placeholder ?? 'Select an option' }} --</option>
        @foreach($options as $key => $value)
            <option value="{{ $key }}">{{ $value }}</option>
        @endforeach
    </select>
</div>