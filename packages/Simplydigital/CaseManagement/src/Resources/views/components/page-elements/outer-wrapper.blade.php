<div
    {{ $attributes->merge([
        'class' => 'bg-gray-50 text-slate-900 p-4 flex flex-col gap-4 dark:bg-gray-950 dark:text-slate-100',
    ]) }} 
    
>
    {{ $slot }}
</div>