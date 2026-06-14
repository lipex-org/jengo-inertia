<script setup>
import { Head, usePage } from '@inertiajs/vue3'
import AppLayout from '../Layouts/AppLayout.vue'
import { computed } from 'vue'

const page = usePage()
const user = computed(() => page.props.auth?.user)
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout header="Dashboard">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <!-- Welcome Card -->
            <div class="lg:col-span-2 jengo-card p-8 jengo-gradient text-white relative overflow-hidden">
                <div class="relative z-10">
                    <h3 class="text-3xl font-black mb-2 tracking-tight">Welcome home, {{ user?.username }}!</h3>
                    <p class="text-white/80 max-w-md">You are successfully signed into your Jengo application. Ready to build something amazing today?</p>
                </div>
                
                <!-- Decorative Icon -->
                <div class="absolute -right-12 -bottom-12 w-64 h-64 bg-white/10 rounded-full blur-2xl"></div>
                <div class="absolute right-8 top-1/2 -translate-y-1/2 opacity-20 hidden lg:block">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-32 h-32" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.628.282a2 2 0 01-1.806 0l-.628-.282a6 6 0 00-3.86-.517l-2.387.477a2 2 0 00-1.022.547m0 0l2.132 2.132a2 2 0 002.828 0l.707-.707a2 2 0 012.828 0l.707.707a2 2 0 002.828 0l2.132-2.132m0 0L20 15V9a2 2 0 00-2-2h-2V5a3 3 0 00-3-3H9a3 3 0 00-3 3v2H4a2 2 0 00-2 2v6a2 2 0 002 2l2 2m0 0l2 2a2 2 0 002.828 0l.707-.707a2 2 0 012.828 0l.707.707a2 2 0 002.828 0l2 2" />
                    </svg>
                </div>
            </div>

            <!-- Stats Card -->
            <div class="jengo-card p-8 flex flex-col justify-between">
                <div>
                    <span class="text-slate-400 text-xs font-bold uppercase tracking-wider">Account Status</span>
                    <div class="flex items-center gap-2 mt-2">
                        <span class="w-3 h-3 rounded-full bg-emerald-500 shadow-lg shadow-emerald-500/20"></span>
                        <span class="text-xl font-black text-slate-900 tracking-tight">Verified</span>
                    </div>
                </div>
                
                <div class="mt-8 pt-6 border-t border-slate-100 flex items-center justify-between text-sm">
                    <span class="text-slate-500 font-medium">Session ID</span>
                    <code class="px-2 py-1 bg-slate-50 rounded text-brand-primary font-bold">ACTIVE</code>
                </div>
            </div>

            <!-- Feature Card 1 -->
            <div class="jengo-card p-6 flex items-start gap-4">
                <div class="w-12 h-12 rounded-xl bg-blue-50 flex items-center justify-center text-blue-600 shrink-0">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" />
                    </svg>
                </div>
                <div>
                    <h4 class="font-bold text-slate-900">Custom Controllers</h4>
                    <p class="text-slate-500 text-sm mt-1">Your auth logic is fully overridden and ready for Inertia JSON requests.</p>
                </div>
            </div>

            <!-- Feature Card 2 -->
            <div class="jengo-card p-6 flex items-start gap-4">
                <div class="w-12 h-12 rounded-xl bg-purple-50 flex items-center justify-center text-purple-600 shrink-0">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01" />
                    </svg>
                </div>
                <div>
                    <h4 class="font-bold text-slate-900">Branded Design</h4>
                    <p class="text-slate-500 text-sm mt-1">Custom CSS variables and design tokens help set Jengo apart.</p>
                </div>
            </div>

            <!-- Feature Card 3 -->
            <div class="jengo-card p-6 flex items-start gap-4">
                <div class="w-12 h-12 rounded-xl bg-pink-50 flex items-center justify-center text-pink-600 shrink-0">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                    </svg>
                </div>
                <div>
                    <h4 class="font-bold text-slate-900">Shield Integration</h4>
                    <p class="text-slate-500 text-sm mt-1">Authentication is rock solid with full page reloads on sign out.</p>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
