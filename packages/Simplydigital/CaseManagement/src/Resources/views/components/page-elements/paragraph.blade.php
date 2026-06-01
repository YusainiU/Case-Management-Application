@props([
  'textColor' => 'text-gray-700',
])
<p class="mb-4 text-body {{ $textColor }}">
    {{ $slot }}
</p>