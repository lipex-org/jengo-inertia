<script setup>
import { Link } from '@inertiajs/vue3'
</script>

<template>
    <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-slate-50 relative overflow-hidden">
        <!-- Background decorative elements -->
        <div class="absolute -top-24 -left-24 w-96 h-96 bg-brand-primary/10 rounded-full blur-3xl"></div>
        <div class="absolute -bottom-24 -right-24 w-96 h-96 bg-brand-secondary/10 rounded-full blur-3xl"></div>
        
        <div class="z-10">
            <Link href="/" class="flex items-center gap-2 group">
                <div class="w-12 h-12 jengo-gradient rounded-xl flex items-center justify-center shadow-lg shadow-brand-primary/20 group-hover:scale-110 transition-transform duration-300">
                    <span class="text-2xl font-black text-white italic">J</span>
                </div>
                <span class="text-3xl font-black tracking-tighter text-slate-900">JENGO</span>
            </Link>
        </div>

        <div class="w-full sm:max-w-md mt-8 px-8 py-10 jengo-card z-10 mx-4">
            <slot />
        </div>
        
        <div class="mt-8 text-slate-400 text-sm z-10">
            &copy; {{ new Date().getFullYear() }} Jengo Framework. Built for speed.
        </div>
    </div>
</template>
