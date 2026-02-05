<div>
    <select
        wire:model="{{ $model }}"
        id="{{ $name }}"
        name="{{ $name }}"
        class="appearance-none text-black block w-full border border-gray-700 rounded py-3 px-4 mb-3 leading-tight focus:outline-none focus:bg-white focus:border-gray-500"
    >
        <option value="">-- {{ $placeholder ?? 'Select an option' }} --</option>
        @foreach($options as $value)
            <option value="{{ $value['id'] }}">{{ $value['name'] }}</option>
        @endforeach
    </select>
</div>