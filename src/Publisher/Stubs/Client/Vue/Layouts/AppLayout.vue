<script setup>
import { Link, usePage } from '@inertiajs/vue3'
import { computed } from 'vue'

defineProps({
    header: String,
})

const page = usePage()
const user = computed(() => page.props.auth?.user)
</script>

<template>
    <div class="min-h-screen bg-slate-50">
        <nav class="bg-white border-b border-slate-100 sticky top-0 z-30">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <div class="flex">
                        <div class="shrink-0 flex items-center">
                            <Link href="/" class="flex items-center gap-2 group">
                                <div class="w-8 h-8 jengo-gradient rounded-lg flex items-center justify-center shadow-lg shadow-brand-primary/20">
                                    <span class="text-sm font-black text-white italic">J</span>
                                </div>
                                <span class="text-xl font-black tracking-tighter text-slate-900 hidden sm:block">JENGO</span>
                            </Link>
                        </div>

                        <div class="hidden space-x-4 sm:-my-px sm:ml-10 sm:flex">
                            <Link
                                href="/dashboard"
                                :class="[
                                    $page.url === '/dashboard' 
                                    ? 'border-brand-primary text-slate-900' 
                                    : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300',
                                    'inline-flex items-center px-1 pt-1 border-b-2 text-sm font-bold transition duration-150 ease-in-out'
                                ]"
                            >
                                Dashboard
                            </Link>
                        </div>
                    </div>

                    <div class="flex items-center ml-6">
                        <div v-if="user" class="flex items-center gap-4">
                            <div class="hidden md:flex flex-col items-end">
                                <span class="text-sm font-bold text-slate-900 leading-none">{{ user.username }}</span>
                                <span class="text-xs text-slate-400">{{ user.email }}</span>
                            </div>
                            <div class="h-8 w-px bg-slate-100 hidden md:block"></div>
                            <Link 
                                href="/logout" 
                                method="post" 
                                as="button" 
                                class="text-sm font-bold text-slate-500 hover:text-red-600 transition-colors"
                            >
                                Sign Out
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </nav>

        <header v-if="header" class="bg-white border-b border-slate-100">
            <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                <h2 class="font-black text-2xl text-slate-900 tracking-tight">
                    {{ header }}
                </h2>
            </div>
        </header>

        <main class="max-w-7xl mx-auto py-12 px-4 sm:px-6 lg:px-8">
            <slot />
        </main>
    </div>
</template>
