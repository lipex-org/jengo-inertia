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
    <div class="min-h-screen bg-base-200 text-base-content">
        <nav class="bg-base-100 border-b border-base-300/80 sticky top-0 z-30 shadow-xs">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <div class="flex">
                        <div class="shrink-0 flex items-center">
                            <Link href="/" class="flex items-center gap-2 group">
                                <div class="w-8 h-8 bg-gradient-to-tr from-primary to-secondary rounded-lg flex items-center justify-center shadow-md shadow-primary/20">
                                    <span class="text-sm font-black text-white italic">J</span>
                                </div>
                                <span class="text-xl font-black tracking-tighter text-base-content hidden sm:block">JENGO</span>
                            </Link>
                        </div>

                        <div class="hidden space-x-4 sm:-my-px sm:ml-10 sm:flex">
                            <Link
                                href="/dashboard"
                                :class="[
                                    $page.url === '/dashboard' 
                                    ? 'border-primary text-primary' 
                                    : 'border-transparent text-base-content/70 hover:text-base-content hover:border-base-300',
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
                                <span class="text-sm font-bold text-base-content leading-none">{{ user.username }}</span>
                                <span class="text-xs text-base-content/60">{{ user.email }}</span>
                            </div>
                            <div class="h-8 w-px bg-base-300 hidden md:block"></div>
                            <Link 
                                href="/logout" 
                                method="post" 
                                as="button" 
                                class="btn btn-ghost btn-sm text-base-content/70 hover:text-error transition-colors"
                            >
                                Sign Out
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </nav>

        <header v-if="header" class="bg-base-100 border-b border-base-300/80">
            <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                <h2 class="font-black text-2xl text-base-content tracking-tight">
                    {{ header }}
                </h2>
            </div>
        </header>

        <main class="max-w-7xl mx-auto py-12 px-4 sm:px-6 lg:px-8">
            <slot />
        </main>
    </div>
</template>
