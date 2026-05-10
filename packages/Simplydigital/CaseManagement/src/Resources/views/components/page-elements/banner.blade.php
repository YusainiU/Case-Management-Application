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


<section class="flex flex-col flex-wrap pt-12">
  <div class="flex flex-row flex-wrap justify-center">
    <div class="flex justify-center text-center m-2 h-24 w-64">
      <div class="shrink-0 rounded-full bg-gray-100 {{ $iconColor }} w-24 h-24 border {{ $border }} z-10 flex items-center justify-center">       
          <x-casemanagement-ui-icon name="quote-left" />        
      </div>
      <div class="flex flex-col text-left {{ $bg }} {{ $text }} text-xs self-center pl-16 pr-4 py-2 -ml-12">
        <h3 class="text-lg">{{ $header }}</h3>
        <p class="w-64 text-xs overflow-y-hidden overflow-x-auto">{{ $message }}</p>
      </div>
    </div>
  </div>
</section>