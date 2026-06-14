<script setup>
import { Head, Link, usePage } from '@inertiajs/vue3'
import GuestLayout from '../../Layouts/GuestLayout.vue'
import { computed } from 'vue'

const page = usePage()
const flash = computed(() => page.props.flash)
</script>

<template>
    <GuestLayout>
        <Head title="Magic Link Sent" />

        <div v-if="flash.message" class="mb-4 font-medium text-sm text-green-600">{{ flash.message }}</div>

        <div class="mb-4 text-sm text-gray-600">
            We have emailed you a magic link. Please check your inbox and click the link to log in.
        </div>

        <div class="mt-6 text-center">
            <Link href="/login" class="font-medium text-indigo-600 hover:text-indigo-500">
                Back to Log in
            </Link>
        </div>
    </GuestLayout>
</template>
