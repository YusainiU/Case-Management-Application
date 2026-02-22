@php
    $label = "text-gray-500 text-sm";
    $p = "font-semibold text-gray-700";
    $contentGrid = "mt-6 grid grid-cols-1 md:grid-cols-2 gap-6 text-left";    
@endphp

<div class="pt-20 pb-10 px-6 text-center">

    {{ $user }}

    <h2 class="text-2xl font-bold text-gray-800">
        {{ $user->name }}
    </h2>

    <p class="text-gray-500 mt-1">
        {{ $user->email }}
    </p>

    <div class="{{ $contentGrid }}">

        <div>
            <label class="{{ $label }}">Address </label>
            <p class="{{ $p }}">
                @if(!empty($address))
                    @foreach($address as $addr)
                        {!!  $addr ? "$addr <br />" : null !!}
                    @endforeach
                @endif
            </p>
        </div>
             
        <div>
            <label class="{{ $label }}">Phone</label>
            <p class="{{ $p }}">
                {{ $user->phone ?? 'N/A' }}
            </p>
        </div>

        <div>
            <label class="{{ $label }}">Joined</label>
            <p class="{{ $p }}">
                {{ $user->created_at->format('F d, Y') }}
            </p>
        </div>

    </div>

    
    <div class="mt-8 flex flex-col md:flex-row justify-center gap-4">
        <a 
            href="#" 
            wire:click='showResetPasswordSection'
            class="bg-blue-600 text-white px-6 py-2 rounded-lg shadow hover:bg-blue-700 transition">
            Reset Password
        </a>
        <a 
            wire:click='showEditingSection'
            href="#" 
            class="bg-blue-600 text-white px-6 py-2 rounded-lg shadow hover:bg-gray-400 transition"
        >
            Edit Details
        </a>
    </div> 
   

</div>
