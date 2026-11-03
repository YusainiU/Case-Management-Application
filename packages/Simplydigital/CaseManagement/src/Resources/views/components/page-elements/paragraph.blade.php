@props([
  'textColor' => 'text-gray-700 dark:text-gray-300',
])
<p class="mb-4 text-body {{ $textColor }}">
    {{ $slot }}
</p>