@php
    
    $outsideDiv = "max-w-4xl mx-auto bg-white shadow-lg rounded-2xl overflow-hidden";
    $headerOutsideDiv = "bg-linear-to-r from-blue-500 to-indigo-600 h-32 relative";
    $headerInsideDiv = "absolute -bottom-16 left-1/2 transform -translate-x-1/2";
    $img = "w-32 h-32 rounded-full border-4 border-white shadow-lg object-cover";
    $hrefUpload = "absolute bottom-0 right-0 bg-blue-600 text-white p-2 rounded-full shadow hover:bg-blue-700";
@endphp


<div>
    <x-usermanagement::modal-frame title="User Profile" widthsize="" cancelThisWith="closeProfile">

        {{-- min-h-screen bg-gray-100 py-10 --}}
        <div class="">
            {{-- max-w-4xl mx-auto bg-white shadow-lg rounded-2xl overflow-hidden --}}
            <div class="{{ $outsideDiv }}">

                {{-- Header --}}
                <div class="{{ $headerOutsideDiv }}">
                    <div class="{{ $headerInsideDiv }}">

                        {{-- Profile Image Frame --}}
                        <div class="relative">
                            <img 
                                src="{{ $avatar ?  route('usermanagement.private.image', ['path' => $avatar]) : asset('images/default-avatar.png') }}"
                                alt=""
                                class="{{ $img }}"
                            />

                            {{-- Optional edit icon --}}
                            <a 
                                href="#" 
                                wire:click='showUploadSection'
                                class="{{ $hrefUpload }}"
                            >
                                ✏️
                            </a>
                        </div>

                    </div>
                </div>

                @if ($showDefault)                    
                    <x-usermanagement::user-profile-section :user=$user :details=$userDetails :address=$userAddress />
                @endif                

                @if ($showUpload)
                    <x-usermanagement::upload />
                @endif

                @if($showEditing)
                    <x-usermanagement::update-user-details :details="$userDetails" />
                @endif

                @if($showResetPassword)
                    <x-usermanagement::reset-password />
                @endif

            </div>
        </div>
    </x-usermanagement::modal>
</div>
