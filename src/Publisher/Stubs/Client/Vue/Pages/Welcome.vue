<script setup>
import { Head, Link, usePage } from '@inertiajs/vue3'
import { computed } from 'vue'

const page = usePage()
const user = computed(() => page.props.auth?.user)
</script>

<template>

    <Head title="Jengo - The Powerhouse Framework" />

    <div class="min-h-screen bg-slate-50 relative overflow-hidden flex flex-col">
        <!-- Background decorative elements -->
        <div class="absolute -top-48 -left-48 w-lg h-128 bg-brand-primary/10 rounded-full blur-3xl"></div>
        <div class="absolute top-1/2 -right-48 w-lg h-128 bg-brand-secondary/10 rounded-full blur-3xl"></div>

        <!-- Navigation -->
        <nav class="relative z-10 px-6 py-8 flex justify-between items-center max-w-7xl mx-auto w-full">
            <div class="flex items-center gap-2 group">
                <div
                    class="w-10 h-10 jengo-gradient rounded-lg flex items-center justify-center shadow-lg shadow-brand-primary/20">
                    <span class="text-xl font-black text-white italic">J</span>
                </div>
                <span class="text-2xl font-black tracking-tighter text-slate-900">JENGO</span>
            </div>

            <div class="flex items-center gap-6">
                <template v-if="user">
                    <Link href="/dashboard" class="jengo-button py-2! px-5! text-sm">
                        Dashboard
                    </Link>
                </template>
                <template v-else>
                    <Link href="/login"
                        class="text-sm font-bold text-slate-600 hover:text-brand-primary transition-colors">
                        Log in
                    </Link>
                    <Link href="/register" class="jengo-button py-2! px-5! text-sm">
                        Get Started
                    </Link>
                </template>
            </div>
        </nav>

        <!-- Hero Section -->
        <main class="relative z-10 grow flex flex-col items-center justify-center px-6 text-center">
            <div
                class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-brand-primary/10 border border-brand-primary/20 mb-8">
                <span class="w-2 h-2 rounded-full bg-brand-primary animate-pulse"></span>
                <span class="text-xs font-bold text-brand-primary uppercase tracking-widest">Version 1.0 is here</span>
            </div>

            <h1 class="text-6xl md:text-8xl font-black text-slate-900 tracking-tight mb-6 leading-none">
                Build faster with <span class="text-transparent bg-clip-text jengo-gradient">Jengo</span>
            </h1>

            <p class="max-w-2xl text-xl text-slate-500 mb-12 leading-relaxed">
                The ultimate CodeIgniter 4 powerhouse. Seamlessly integrated with Inertia.js and Vue 3 to help you ship
                beautiful applications in record time.
            </p>

            <div class="flex flex-col sm:flex-row gap-4 items-center justify-center">
                <Link href="/register" class="jengo-button text-lg px-10 py-4">
                    Start Building
                </Link>
                <a href="https://github.com/jengophp" target="_blank"
                    class="px-10 py-4 rounded-xl font-semibold text-slate-700 bg-white border border-slate-200 hover:bg-slate-50 transition-all flex items-center gap-2">
                    Documentation
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M14 5l7 7m0 0l-7 7m7-7H3" />
                    </svg>
                </a>
            </div>

            <div class="mt-24 grid grid-cols-1 md:grid-cols-3 gap-8 max-w-5xl w-full text-left">
                <div class="jengo-card p-8">
                    <div class="w-12 h-12 bg-blue-50 rounded-xl flex items-center justify-center mb-6 text-blue-600">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-slate-900 mb-2">Lightning Fast</h3>
                    <p class="text-slate-500 text-sm">Powered by Vite and CodeIgniter 4, Jengo is optimized for the best
                        developer experience.</p>
                </div>

                <div class="jengo-card p-8">
                    <div
                        class="w-12 h-12 bg-purple-50 rounded-xl flex items-center justify-center mb-6 text-purple-600">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-slate-900 mb-2">Shield Security</h3>
                    <p class="text-slate-500 text-sm">Robust authentication and authorization built directly into the
                        core using
                        CI Shield.</p>
                </div>

                <div class="jengo-card p-8">
                    <div
                        class="w-12 h-12 bg-emerald-50 rounded-xl flex items-center justify-center mb-6 text-emerald-600">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-slate-900 mb-2">Modern Stack</h3>
                    <p class="text-slate-500 text-sm">Leverage Inertia.js and Vue 3 to build reactive SPAs without the
                        complexity of an API.</p>
                </div>
            </div>
        </main>

        <footer class="relative z-10 py-12 text-center text-slate-400 text-sm">
            &copy; {{ new Date().getFullYear() }} JengoPHP. All rights reserved.
        </footer>
    </div>
</template>
