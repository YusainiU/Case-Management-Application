<?php

namespace Simplydigital\EngineerManagement\View\Components;

use Closure;
use Illuminate\View\Component;
use Illuminate\Contracts\View\View;

class Icon extends Component
{
    public string $name;
    public static array $cache = [];

    /**
     * Create a new component instance.
     */
    public function __construct(string $name)
    {
        $this->name = $name;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        //return view('components.icon');
        $path = resource_path("/Simplydigital/EngineerManagement/icons/{$this->name}.svg");

        // If the SVG file doesn't exist, return an empty icon
        if (!file_exists($path)) {
            return "<!-- Icon {$this->name} not found -->";
        }

        if (!isset(static::$cache[$path])) {
            static::$cache[$path] = file_get_contents($path);
        }

        return function (array $data) use ($path) {

            //$svg = file_get_contents($path);
             $svg = static::$cache[$path];

            // Merge Tailwind classes (like size and color) into the svg element
            $attributes = $data['attributes']->merge([
                'class' => $data['attributes']->get('class')
            ]);

            // Add Tailwind classes to the SVG tag
            $svg = preg_replace(
                '/<svg\b([^>]*)>/',
                '<svg$1 ' . $attributes . '>',
                $svg
            );

            return $svg;
        };
    }
}