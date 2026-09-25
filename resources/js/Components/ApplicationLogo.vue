<template>
    <svg viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg">
        <rect width="40" height="40" rx="10" fill="url(#skillup-brand-grad)" />
        <!-- Dynamic Upward Growth Trendline -->
        <path
            d="M10 27L18 19L23 24L30 14"
            stroke="white"
            stroke-width="2.75"
            stroke-linecap="round"
            stroke-linejoin="round"
        />
        <!-- Arrowhead -->
        <path
            d="M24.5 14H30V19.5"
            stroke="white"
            stroke-width="2.75"
            stroke-linecap="round"
            stroke-linejoin="round"
        />
        <!-- Progress Milestone Nodes -->
        <circle cx="10" cy="27" r="1.75" fill="#A7F3D0" />
        <circle cx="18" cy="19" r="1.75" fill="#A7F3D0" />
        <circle cx="23" cy="24" r="1.75" fill="#A7F3D0" />
        <circle cx="30" cy="14" r="1.75" fill="#FDE047" />
        <defs>
            <linearGradient id="skillup-brand-grad" x1="0" y1="0" x2="40" y2="40" gradientUnits="userSpaceOnUse">
                <stop stop-color="#4F46E5" />
                <stop offset="1" stop-color="#7C3AED" />
            </linearGradient>
        </defs>
    </svg>
</template>
