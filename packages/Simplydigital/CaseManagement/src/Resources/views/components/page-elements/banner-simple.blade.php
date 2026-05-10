@php
  $border = "border-gray-500";
  $bg = "bg-gray-500";
  $text = "text-white";
  $iconColor = "text-gray-500";
  if(isset($mode)) {
    if($mode == 'info'){
      $border = " border-blue-500";
      $bg = "bg-blue-500";
      $iconColor = "text-blue-500";
    } 
    if($mode == 'important')
    {
      $border = "border-red-700";
      $bg = "bg-red-700";
      $text = "text-white";
      $iconColor = "text-red-700";      
    }
  }  
@endphp


<div class="{{ $bg }} border-t border-b {{ $border }} {{ $text }} px-4 py-3" role="alert">
  <p class="font-bold">{{ $header }}</p>
  <p class="text-sm">
    {{ $message }}
  </p>
</div>
