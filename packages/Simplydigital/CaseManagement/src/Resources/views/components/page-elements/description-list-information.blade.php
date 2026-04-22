@props([
    'title',
    'description',
    'items', 
])

<div class="">
  <div class="px-4 sm:px-0">
    <h3 class="text-base/7 font-semibold text-gray-900">{{ $title }}</h3>
    <p class="mt-1 max-w-2xl text-sm/6 text-gray-500">{{ $description }}</p>
  </div>
  <div class="mt-6 border-t border-gray-100">
    <div class="grid grid-cols-2 gap-4">
      @foreach($items as $item)
        <div>
          <p class="text-sm font-medium text-gray-900">{{ $item['label'] }}</p>
          <p class="mt-1 text-sm text-gray-700">{{ $item['value'] }}</p>
        </div>
      @endforeach
    </div>
  </div>
</div>
