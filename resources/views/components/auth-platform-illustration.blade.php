{{--
    Auth Platform Illustration - Mindfulness wellness visual.

    Mindfulness context: 4-week mindfulness program for stress management.
    Visual language: organic, calming — lotus flower center (peace/rebirth),
    surrounding leaves (growth/nature), gentle waves (breath/flow), and
    soft circular meditation rings (focus/mindfulness).

    Uses currentColor so it inherits the white/primary-100 text color of
    the green gradient branding panel. Inline SVG (no external asset dependency).
--}}
<svg class="w-full h-auto max-w-md mx-auto" viewBox="0 0 480 320" xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" aria-hidden="true">

    {{-- Outer meditation rings (concentric circles — focus/mindfulness) --}}
    <circle cx="240" cy="160" r="140" stroke-width="0.8" opacity="0.12"/>
    <circle cx="240" cy="160" r="110" stroke-width="0.8" opacity="0.18"/>
    <circle cx="240" cy="160" r="80" stroke-width="0.8" opacity="0.24"/>

    {{-- Gentle waves (breath/flow) — bottom --}}
    <path d="M 40 250 Q 120 230 200 250 T 360 250 T 440 250" stroke-width="1.5" opacity="0.35" stroke-linecap="round"/>
    <path d="M 40 265 Q 120 245 200 265 T 360 265 T 440 265" stroke-width="1.2" opacity="0.25" stroke-linecap="round"/>
    <path d="M 40 280 Q 120 260 200 280 T 360 280 T 440 280" stroke-width="1" opacity="0.18" stroke-linecap="round"/>

    {{-- Gentle waves (breath/flow) — top --}}
    <path d="M 40 70 Q 120 50 200 70 T 360 70 T 440 70" stroke-width="1.2" opacity="0.2" stroke-linecap="round"/>
    <path d="M 40 55 Q 120 35 200 55 T 360 55 T 440 55" stroke-width="1" opacity="0.14" stroke-linecap="round"/>

    {{-- Lotus flower (center — peace/rebirth/mindfulness) --}}
    <g transform="translate(240, 160)" opacity="0.9">

        {{-- Center petal (upright) --}}
        <path d="M 0 -55 Q -12 -30 0 0 Q 12 -30 0 -55 Z"
              stroke-width="2" stroke-linecap="round" stroke-linejoin="round" fill="currentColor" fill-opacity="0.12"/>

        {{-- Left inner petal --}}
        <path d="M -8 -45 Q -28 -25 -22 5 Q -10 -10 -8 -45 Z"
              stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" fill="currentColor" fill-opacity="0.08"/>

        {{-- Right inner petal --}}
        <path d="M 8 -45 Q 28 -25 22 5 Q 10 -10 8 -45 Z"
              stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" fill="currentColor" fill-opacity="0.08"/>

        {{-- Left outer petal (wider, lower) --}}
        <path d="M -15 -35 Q -42 -15 -38 15 Q -22 0 -15 -35 Z"
              stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" fill="currentColor" fill-opacity="0.05"/>

        {{-- Right outer petal (wider, lower) --}}
        <path d="M 15 -35 Q 42 -15 38 15 Q 22 0 15 -35 Z"
              stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" fill="currentColor" fill-opacity="0.05"/>

        {{-- Left bottom petal (low, wide) --}}
        <path d="M -20 -20 Q -48 -2 -45 25 Q -28 8 -20 -20 Z"
              stroke-width="1.3" stroke-linecap="round" stroke-linejoin="round" fill="currentColor" fill-opacity="0.03"/>

        {{-- Right bottom petal (low, wide) --}}
        <path d="M 20 -20 Q 48 -2 45 25 Q 28 8 20 -20 Z"
              stroke-width="1.3" stroke-linecap="round" stroke-linejoin="round" fill="currentColor" fill-opacity="0.03"/>

        {{-- Lotus base (water line / pedestal) --}}
        <path d="M -45 25 Q 0 35 45 25" stroke-width="1.8" stroke-linecap="round" opacity="0.5"/>
        <path d="M -38 32 Q 0 42 38 32" stroke-width="1.3" stroke-linecap="round" opacity="0.35"/>

        {{-- Center dot (focus point / third eye) --}}
        <circle cx="0" cy="-15" r="3.5" fill="currentColor" opacity="0.85"/>
        <circle cx="0" cy="-15" r="6" stroke-width="1" opacity="0.4"/>
    </g>

    {{-- Floating leaves (growth/nature) — scattered around lotus --}}
    {{-- Top-left leaf --}}
    <g transform="translate(95, 100) rotate(-35)" opacity="0.5">
        <path d="M 0 0 Q -8 -18 0 -36 Q 8 -18 0 0 Z" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" fill="currentColor" fill-opacity="0.08"/>
        <path d="M 0 0 L 0 -32" stroke-width="0.8" opacity="0.5"/>
    </g>

    {{-- Top-right leaf --}}
    <g transform="translate(385, 95) rotate(40)" opacity="0.5">
        <path d="M 0 0 Q -8 -18 0 -36 Q 8 -18 0 0 Z" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" fill="currentColor" fill-opacity="0.08"/>
        <path d="M 0 0 L 0 -32" stroke-width="0.8" opacity="0.5"/>
    </g>

    {{-- Bottom-left small leaf --}}
    <g transform="translate(110, 220) rotate(-60)" opacity="0.4">
        <path d="M 0 0 Q -6 -14 0 -28 Q 6 -14 0 0 Z" stroke-width="1.3" stroke-linecap="round" stroke-linejoin="round" fill="currentColor" fill-opacity="0.06"/>
        <path d="M 0 0 L 0 -24" stroke-width="0.7" opacity="0.5"/>
    </g>

    {{-- Bottom-right small leaf --}}
    <g transform="translate(370, 215) rotate(65)" opacity="0.4">
        <path d="M 0 0 Q -6 -14 0 -28 Q 6 -14 0 0 Z" stroke-width="1.3" stroke-linecap="round" stroke-linejoin="round" fill="currentColor" fill-opacity="0.06"/>
        <path d="M 0 0 L 0 -24" stroke-width="0.7" opacity="0.5"/>
    </g>

    {{-- Soft glow / aura around lotus (radial) --}}
    <circle cx="240" cy="160" r="55" stroke-width="0.5" opacity="0.2" stroke-dasharray="2 4"/>
    <circle cx="240" cy="160" r="65" stroke-width="0.5" opacity="0.12" stroke-dasharray="2 6"/>

    {{-- Small floating particles (calm/spaciousness) --}}
    <circle cx="140" cy="140" r="2" fill="currentColor" opacity="0.4"/>
    <circle cx="340" cy="150" r="2" fill="currentColor" opacity="0.4"/>
    <circle cx="180" cy="200" r="1.5" fill="currentColor" opacity="0.3"/>
    <circle cx="310" cy="195" r="1.5" fill="currentColor" opacity="0.3"/>
    <circle cx="240" cy="90" r="1.5" fill="currentColor" opacity="0.35"/>
    <circle cx="240" cy="230" r="1.5" fill="currentColor" opacity="0.3"/>

</svg>
