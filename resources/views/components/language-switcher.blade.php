@props([
    'languageData' => null,
    'type' => 'dropdown', // dropdown, buttons, select
    'showFlag' => true,
    'showName' => true,
    'showNativeName' => false,
    'class' => '',
    'size' => 'md' // sm, md, lg
])

@php
    $languageData = $languageData ?? \App\Helpers\LanguageHelper::getLanguageSwitcherData();
    $current = $languageData['current'] ?? null;
    $available = $languageData['available'] ?? [];
    
    $sizeClasses = [
        'sm' => 'btn-sm',
        'md' => '',
        'lg' => 'btn-lg'
    ];
    
    $sizeClass = $sizeClasses[$size] ?? '';
@endphp

@if($current && count($available) > 1)
    @if($type === 'dropdown')
        <div class="dropdown {{ $class }}">
            <button class="btn btn-light {{ $sizeClass }} dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                @if($showFlag && $current['flag_icon_html'])
                    {!! $current['flag_icon_html'] !!}
                @endif
                
                @if($showName)
                    <span class="ms-2">{{ $current['name'] }}</span>
                @elseif($showNativeName)
                    <span class="ms-2">{{ $current['native_name'] }}</span>
                @else
                    <span class="ms-2">{{ strtoupper($current['code']) }}</span>
                @endif
            </button>
            
            <ul class="dropdown-menu">
                @foreach($available as $language)
                    <li>
                        <a class="dropdown-item {{ $language['code'] === $current['code'] ? 'active' : '' }}" 
                           href="{{ $language['url'] }}"
                           @if($language['code'] === $current['code']) aria-current="true" @endif>
                            @if($showFlag && $language['flag_icon_html'])
                                {!! $language['flag_icon_html'] !!}
                            @endif
                            
                            @if($showName)
                                <span class="ms-2">{{ $language['name'] }}</span>
                            @elseif($showNativeName)
                                <span class="ms-2">{{ $language['native_name'] }}</span>
                            @else
                                <span class="ms-2">{{ strtoupper($language['code']) }}</span>
                            @endif
                            
                            @if($language['code'] === $current['code'])
                                <i class="fas fa-check ms-auto"></i>
                            @endif
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>
        
    @elseif($type === 'buttons')
        <div class="btn-group {{ $class }}" role="group" aria-label="{{ __('app.language') }}">
            @foreach($available as $language)
                <a href="{{ $language['url'] }}" 
                   class="btn {{ $language['code'] === $current['code'] ? 'btn-primary' : 'btn-outline-primary' }} {{ $sizeClass }}"
                   @if($language['code'] === $current['code']) aria-current="true" @endif
                   title="{{ $language['name'] }}">
                    @if($showFlag && $language['flag_icon_html'])
                        {!! $language['flag_icon_html'] !!}
                    @endif
                    
                    @if($showName)
                        <span class="ms-1">{{ $language['name'] }}</span>
                    @elseif($showNativeName)
                        <span class="ms-1">{{ $language['native_name'] }}</span>
                    @else
                        <span class="ms-1">{{ strtoupper($language['code']) }}</span>
                    @endif
                </a>
            @endforeach
        </div>
        
    @elseif($type === 'select')
        <select class="form-select {{ $sizeClass }} {{ $class }}" 
                onchange="window.location.href = this.value"
                aria-label="{{ __('app.language') }}">
            @foreach($available as $language)
                <option value="{{ $language['url'] }}" 
                        {{ $language['code'] === $current['code'] ? 'selected' : '' }}>
                    @if($showName)
                        {{ $language['name'] }}
                    @elseif($showNativeName)
                        {{ $language['native_name'] }}
                    @else
                        {{ strtoupper($language['code']) }}
                    @endif
                </option>
            @endforeach
        </select>
        
    @elseif($type === 'flags')
        <div class="language-flags {{ $class }}">
            @foreach($available as $language)
                <a href="{{ $language['url'] }}" 
                   class="language-flag {{ $language['code'] === $current['code'] ? 'active' : '' }}"
                   title="{{ $language['name'] }}"
                   @if($language['code'] === $current['code']) aria-current="true" @endif>
                    @if($language['flag_icon_html'])
                        {!! $language['flag_icon_html'] !!}
                    @else
                        <span class="language-code">{{ strtoupper($language['code']) }}</span>
                    @endif
                </a>
            @endforeach
        </div>
        
        <style>
            .language-flags {
                display: flex;
                gap: 0.5rem;
                align-items: center;
            }
            
            .language-flag {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                width: 2rem;
                height: 2rem;
                border-radius: 0.25rem;
                text-decoration: none;
                transition: all 0.2s ease;
                border: 2px solid transparent;
            }
            
            .language-flag:hover {
                transform: scale(1.1);
                border-color: var(--bs-primary);
            }
            
            .language-flag.active {
                border-color: var(--bs-primary);
                box-shadow: 0 0 0 0.2rem rgba(var(--bs-primary-rgb), 0.25);
            }
            
            .language-flag .language-code {
                font-size: 0.75rem;
                font-weight: bold;
                color: var(--bs-body-color);
            }
            
            .language-flag img,
            .language-flag .flag-icon {
                width: 1.5rem;
                height: auto;
            }
        </style>
        
    @elseif($type === 'minimal')
        <div class="language-minimal {{ $class }}">
            @if($showFlag && $current['flag_icon_html'])
                {!! $current['flag_icon_html'] !!}
            @endif
            
            <select class="form-select form-select-sm border-0 bg-transparent" 
                    onchange="window.location.href = this.value"
                    style="width: auto; padding-right: 2rem;">
                @foreach($available as $language)
                    <option value="{{ $language['url'] }}" 
                            {{ $language['code'] === $current['code'] ? 'selected' : '' }}>
                        {{ strtoupper($language['code']) }}
                    </option>
                @endforeach
            </select>
        </div>
        
    @endif
@endif

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Add smooth transition for language switching
    const languageLinks = document.querySelectorAll('.language-flag, .dropdown-item[href*="lang="], .btn[href*="lang="]');
    
    languageLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            // Add loading state
            const loadingText = '{{ __("app.loading") }}';
            if (this.classList.contains('btn')) {
                const originalText = this.innerHTML;
                this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> ' + loadingText;
                this.disabled = true;
                
                // Restore original state if navigation fails
                setTimeout(() => {
                    this.innerHTML = originalText;
                    this.disabled = false;
                }, 5000);
            }
        });
    });
});
</script>
@endpush
