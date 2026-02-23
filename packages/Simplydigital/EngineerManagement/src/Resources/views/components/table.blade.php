@php
    $table = Config::get('engineer-management.tableClasses.table');
    $thead = Config::get('engineer-management.table.thead');
    $tbody = Config::get('engineer-management.table.tbody');
@endphp

<div x-cloak>
    <div class="mt-5 mb-5 overflow-x-auto bg-white rounded-lg shadow overflow-y-auto relative">
        <table class="{{ $table }}">
            <thead class="{{ $thead }} sticky top-0">
				{{ $tableColumns }}
            </thead>
            <tbody class="{{ $tbody }}">
				{{ $tableRows }}
            </tbody>
        </table>
    </div>
</div>