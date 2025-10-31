{{-- Analytics Menu Sidebar --}}
@php
    $analyticsMenu = config('analytics_menu.menu', []);
    $currentRoute = Route::currentRouteName();
@endphp

<div class="menu-item">
    <div class="menu-content pt-8 pb-2">
        <span class="menu-section text-muted text-uppercase fs-8 ls-1">Phân Tích</span>
    </div>
</div>

@foreach($analyticsMenu as $menuItem)
    @if(isset($menuItem['children']) && count($menuItem['children']) > 0)
        {{-- Menu with children --}}
        @php
            $isActive = false;
            foreach($menuItem['children'] as $child) {
                if(isset($child['route']) && $currentRoute === $child['route']) {
                    $isActive = true;
                    break;
                }
            }
        @endphp
        
        <div data-kt-menu-trigger="click" class="menu-item menu-accordion {{ $isActive ? 'here show' : '' }}">
            <span class="menu-link">
                <span class="menu-icon">
                    <i class="{{ $menuItem['icon'] }} fs-2"></i>
                </span>
                <span class="menu-title">{{ $menuItem['title'] }}</span>
                <span class="menu-arrow"></span>
            </span>
            
            <div class="menu-sub menu-sub-accordion {{ $isActive ? 'show' : '' }}">
                @foreach($menuItem['children'] as $child)
                    <div class="menu-item">
                        <a class="menu-link {{ $currentRoute === $child['route'] ? 'active' : '' }}" 
                           href="{{ route($child['route']) }}">
                            <span class="menu-bullet">
                                <span class="bullet bullet-dot"></span>
                            </span>
                            <span class="menu-title">{{ $child['title'] }}</span>
                        </a>
                    </div>
                @endforeach
            </div>
        </div>
    @else
        {{-- Single menu item --}}
        <div class="menu-item">
            <a class="menu-link {{ $currentRoute === $menuItem['route'] ? 'active' : '' }}" 
               href="{{ route($menuItem['route']) }}">
                <span class="menu-icon">
                    <i class="{{ $menuItem['icon'] }} fs-2"></i>
                </span>
                <span class="menu-title">{{ $menuItem['title'] }}</span>
            </a>
        </div>
    @endif
@endforeach

