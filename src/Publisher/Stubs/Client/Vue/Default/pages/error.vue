<script setup>
import { computed } from 'vue'
import { Head } from '@inertiajs/vue3'

const props = defineProps({
    status: {
        type: Number,
        required: true,
    },
    message: {
        type: String,
        default: '',
    },
    exception: {
        type: Object,
        default: null,
    },
})

const titles = {
    401: 'Unauthorized',
    403: 'Forbidden',
    404: 'Page Not Found',
    419: 'Page Expired',
    500: 'Server Error',
    503: 'Service Unavailable',
}

const descriptions = {
    401: 'You are not authorized to access this resource.',
    403: 'You do not have permission to access this resource.',
    404: 'The page you are looking for could not be found.',
    419: 'The page has expired. Please refresh and try again.',
    500: 'An unexpected server error occurred.',
    503: 'Service is temporarily unavailable. Please try again shortly.',
}

const displayTitle = computed(() => titles[props.status] || `Error ${props.status}`)
const displayDescription = computed(() => descriptions[props.status] || props.message || 'An unexpected error occurred.')
</script>

<template>
    <Head :title="`${status}: ${displayTitle}`" />

    <div class="min-h-screen bg-base-200 text-base-content flex flex-col items-center justify-center px-6 py-12">
        <div class="max-w-md w-full text-center">
            <span class="text-7xl font-black text-primary tracking-tight">{{ status }}</span>
            <h1 class="mt-4 text-2xl font-bold tracking-tight">{{ displayTitle }}</h1>
            <p class="mt-2 text-base text-base-content/70">{{ displayDescription }}</p>

            <div class="mt-6 flex justify-center gap-4">
                <a href="/" class="btn btn-primary btn-sm">
                    Return Home
                </a>
            </div>
        </div>

        <div v-if="exception" class="mt-10 max-w-3xl w-full p-6 bg-base-100 border border-base-300 rounded-box shadow text-left overflow-auto">
            <h2 class="text-lg font-bold text-error">{{ exception.title || 'Exception' }}</h2>
            <p class="mt-1 text-sm font-mono text-base-content/80">{{ exception.message }}</p>
            <p v-if="exception.file" class="mt-2 text-xs font-mono text-base-content/60">
                {{ exception.file }}:{{ exception.line }}
            </p>
        </div>
    </div>
</template>
