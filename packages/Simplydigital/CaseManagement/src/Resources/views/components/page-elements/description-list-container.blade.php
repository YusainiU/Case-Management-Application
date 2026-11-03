<dl {{ $attributes->merge([
    'class' => 'w-full divide-y divide-slate-100 text-slate-800 dark:divide-slate-700 dark:text-slate-200',
]) }}>
    {{ $slot }}
</dl>