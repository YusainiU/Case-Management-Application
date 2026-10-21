<!-- ============================================= -->
<!--  PRIMARY ICON  (app icon / favicon / avatar)  -->
<!-- ============================================= -->

<svg viewBox="0 0 200 42" class="w-full h-full" xmlns="http://www.w3.org/2000/svg" role="img" aria-label="SimplyDigital" {{ $attributes }}>
  
  <style>
    /* Light mode (default) */
    .sd-simply { fill: #1E3A8A; }
    .sd-digital{ fill: #2563EB; }
    .cube-top  { fill: #60A5FA; }
    .cube-left { fill: #2563EB; }
    .cube-right{ fill: #1E3A8A; }

    /* Dark mode */
    @media (prefers-color-scheme: dark) {
      .sd-simply { fill: #E8EEFF !important; }
      .sd-digital{ fill: #60A5FA !important; }
      .cube-top  { fill: #93C5FD !important; }
      .cube-left { fill: #60A5FA !important; }
      .cube-right{ fill: #3B82F6 !important; }
    }
  </style>
  

  <!-- Icon -->
  <g fill="none" stroke-width="3.2" stroke-linecap="round" stroke-linejoin="round">
    <path class="sd-s"
          d="M22 12 C10 10, 6 18, 10 20
             C14 23, 18 25, 22 28
             C24 32, 16 38, 10 36"
          stroke="#1E3A8A"/>
    <path class="sd-d"
          d="M34 12 V 36 H 44 L 52 28 V 20 L 44 12 Z"
          stroke="#2563EB"/>
  </g>

  <!-- Wordmark -->
  <text x="2" y="30"
        font-family="Inter, 'Helvetica Neue', Arial, sans-serif"
        font-size="16" letter-spacing="-0.3">
    <tspan class="sd-simply"  font-weight="400">Simply</tspan><tspan class="sd-digital" font-weight="700">Digital</tspan>
  </text>

    <!-- Small 3D cube -->
  <g transform="translate(108,17)">
    <!-- Top face -->
    <path class="cube-top"   d="M7 0 L14 3.5 L7 7 L0 3.5 Z"/>
    <!-- Left face -->
    <path class="cube-left"  d="M0 3.5 L7 7 L7 14 L0 10.5 Z"/>
    <!-- Right face -->
    <path class="cube-right" d="M7 7 L14 3.5 L14 10.5 L7 14 Z"/>
  </g>

</svg>


{{-- <svg viewBox="0 0 40 42" class="w-full h-full" xmlns="http://www.w3.org/2000/svg" {{ $attributes }}>
  <!-- Container -->
  <rect x="1" y="1" width="38" height="26" rx="6" stroke="#1E3A8A" stroke-width="2"/>

  <!-- People -->
  <circle cx="14" cy="12" r="3" fill="#2563EB"/>
  <circle cx="20" cy="10" r="3.5" fill="#2563EB"/>
  <circle cx="26" cy="12" r="3" fill="#2563EB"/>

  <!-- Arrow -->
  <path d="M8 20 L15 15 L21 19 L32 9" stroke="#2563EB" stroke-width="2.5" fill="none"/>
  <path d="M30 9 H32 V11" stroke="#2563EB" stroke-width="2.5" fill="none"/>

  <!-- Text -->
  <text x="20" y="40" text-anchor="middle" font-size="12" font-weight="bold" fill="#1E3A8A">
    CRM
  </text>
</svg> --}}

    