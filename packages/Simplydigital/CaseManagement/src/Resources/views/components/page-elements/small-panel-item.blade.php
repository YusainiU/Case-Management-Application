@props([
    'title' => 'Text Here',
    'containerClass' => 'flex items-center gap-2 px-3 py-2 rounded-lg',
    'containerBg' => 'bg-red-50',
    'containerBorder' => 'border-red-100',
    'bulletClass' => 'w-3 h-3 rounded-full',
    'bulletBg' => 'bg-red-600',
    'paraClass' => 'text-xs',
    'paraText' => 'text-gray-500',     
])

<div @class([$containerClass, $containerBg, $containerBorder])>
    <span @class([$bulletClass, $bulletBg])></span>
    <div>
        <p @class([$paraClass, $paraText])>{{ $title }}</p>
    </div>
</div>
