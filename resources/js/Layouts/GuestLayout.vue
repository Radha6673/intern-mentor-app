<script setup>
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <div
        class="flex min-h-screen flex-col items-center bg-gray-100 pt-6 sm:justify-center sm:pt-0"
    >
        <div>
            <Link href="/" class="flex flex-col items-center gap-2.5 group">
                <ApplicationLogo class="h-16 w-16 shadow-lg shadow-indigo-500/20 rounded-2xl transition-transform group-hover:scale-105" />
                <span class="font-black text-2xl tracking-tight text-gray-900 group-hover:text-indigo-600 transition-colors">
                    Skill<span class="text-indigo-600">Up</span>
                </span>
            </Link>
        </div>

        <div
            class="mt-6 w-full overflow-hidden bg-white px-6 py-4 shadow-md sm:max-w-md sm:rounded-lg"
        >
            <slot />
        </div>
    </div>
</template>
