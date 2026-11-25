<?php

if (!function_exists('hpayIcon')) {
    /**
     * Inline stroke icon used across the HPay admin UI.
     * Returns an <svg> string for the given key (empty svg if unknown).
     */
    function hpayIcon(string $n): string
    {
        $p = [
            'dashboard' => '<rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/>',
            'shield'    => '<path d="M12 3l7 3v5c0 4.6-3 7.8-7 9-4-1.2-7-4.4-7-9V6z"/>',
            'users'     => '<circle cx="9" cy="8" r="3"/><path d="M3.5 20c0-3 2.7-5 5.5-5s5.5 2 5.5 5"/><path d="M16 5.2a3 3 0 010 5.6"/><path d="M18 15.3c1.9.6 3.5 2.1 3.5 4.7"/>',
            'user'      => '<circle cx="12" cy="8" r="3.5"/><path d="M5 20c0-3.5 3.1-6 7-6s7 2.5 7 6"/>',
            'store'     => '<path d="M4 9l1.2-4.5h13.6L20 9"/><path d="M5 9v10h14V9"/><path d="M4 9a2 2 0 004 0 2 2 0 004 0 2 2 0 004 0 2 2 0 004 0"/><path d="M10 19v-5h4v5"/>',
            'payin'     => '<path d="M12 3v11"/><path d="M8 10l4 4 4-4"/><rect x="4" y="18" width="16" height="3" rx="1"/>',
            'settle'    => '<path d="M4 8h12l-3-3"/><path d="M20 16H8l3 3"/>',
            'bank'      => '<path d="M3 9l9-5 9 5"/><path d="M5 9.5v8M9.5 9.5v8M14.5 9.5v8M19 9.5v8"/><path d="M3.5 21h17"/>',
            'card'      => '<rect x="3" y="5" width="18" height="14" rx="2.5"/><path d="M3 10h18"/>',
            'sliders'   => '<path d="M4 7h8M16 7h4M4 12h4M12 12h8M4 17h12M19 17h1"/><circle cx="14" cy="7" r="2"/><circle cx="10" cy="12" r="2"/><circle cx="17.5" cy="17" r="2"/>',
            'phone'     => '<rect x="7" y="3" width="10" height="18" rx="2.5"/><path d="M10.5 18h3"/>',
            'message'   => '<path d="M4 5h16v11H9l-4 4z"/>',
        ];

        return '<svg class="ic" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">'.($p[$n] ?? '').'</svg>';
    }
}
