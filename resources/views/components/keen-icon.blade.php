@props([
    'name' => 'home',
    'style' => 'duotone', // duotone, outline, solid
    'size' => 'fs-2', // fs-1, fs-2, fs-3, fs-4, fs-5, fs-6, fs-7, fs-8, fs-9
    'class' => '',
    'color' => ''
])

@php
    $iconClass = "ki-{$style} ki-{$name} {$size}";
    if ($class) {
        $iconClass .= " {$class}";
    }
    if ($color) {
        $iconClass .= " text-{$color}";
    }
@endphp

<i class="{{ $iconClass }}">
    @if($style === 'duotone')
        <span class="path1"></span>
        <span class="path2"></span>
        @if(in_array($name, ['package', 'delivery', 'trash', 'wallet', 'medal-star', 'calendar-8', 'rocket', 'flash']))
            <span class="path3"></span>
            @if(in_array($name, ['delivery', 'trash', 'wallet', 'medal-star', 'calendar-8']))
                <span class="path4"></span>
                @if(in_array($name, ['delivery', 'trash', 'medal-star', 'calendar-8']))
                    <span class="path5"></span>
                    @if(in_array($name, ['calendar-8']))
                        <span class="path6"></span>
                    @endif
                @endif
            @endif
        @endif
    @endif
</i>
