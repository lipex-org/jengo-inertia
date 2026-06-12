<script setup>
import { Link, usePage } from '@inertiajs/vue3'

defineProps({
    header: String,
})

const page = usePage()
</script>

<template>
    <div class="min-h-screen bg-gray-100">
        <nav class="bg-white border-b border-gray-100">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <div class="flex">
                        <div class="shrink-0 flex items-center">
                            <Link href="/" class="text-2xl font-bold text-indigo-600">
                                JENGO
                            </Link>
                        </div>

                        <div class="hidden space-x-8 sm:-my-px sm:ml-10 sm:flex">
                            <Link
                                href="/dashboard"
                                class="inline-flex items-center px-1 pt-1 border-b-2 border-indigo-400 text-sm font-medium leading-5 text-gray-900 focus:outline-none focus:border-indigo-700 transition duration-150 ease-in-out"
                            >
                                Dashboard
                            </Link>
                        </div>
                    </div>

                    <div class="hidden sm:flex sm:items-center sm:ml-6">
                        <template v-if="page.props.auth?.user">
                            <div class="flex items-center space-x-4">
                                <span class="text-sm text-gray-700">{{ page.props.auth.user.username }}</span>
                                <Link href="/logout" method="post" as="button" class="text-sm text-gray-500 hover:text-gray-700">
                                    Logout
                                </Link>
                            </div>
                        </template>
                        <template v-else>
                            <div class="flex items-center space-x-4">
                                <Link href="/login" class="text-sm text-gray-700 underline">Log in</Link>
                                <Link href="/register" class="text-sm text-gray-700 underline">Register</Link>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </nav>

        <header v-if="$slots.header" class="bg-white shadow">
            <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                <slot name="header" />
            </div>
        </header>

        <main>
            <slot />
        </main>
    </div>
</template>
