@props(['name'])

{!! file_get_contents(resource_path("icons/{$name}.svg")) !!}