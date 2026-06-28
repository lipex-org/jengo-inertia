<script setup>
import { Head, Link, useForm, usePage } from '@inertiajs/vue3'
import GuestLayout from '../../layouts/guest-layout.vue'
import { computed } from 'vue'

const page = usePage()
const flash = computed(() => page.props.flash)

const form = useForm({
    email: '',
})

const submit = () => {
    form.post('/login/magic-link')
}
</script>

<template>
    <GuestLayout>

        <Head title="Magic Link" />

        <div class="mb-8">
            <h1 class="text-2xl font-bold text-base-content">Forgot Password?</h1>
            <p class="text-base-content/70 mt-1">We'll send you a link to sign in instantly.</p>
        </div>

        <div v-if="flash?.error"
            class="mb-6 alert alert-error text-sm py-3 px-4 font-medium">
            {{ flash.error }}
        </div>

        <div v-if="flash?.message"
            class="mb-6 alert alert-success text-sm py-3 px-4 font-medium">
            {{ flash.message }}
        </div>

        <form @submit.prevent="submit" class="space-y-5">
            <div>
                <label for="email" class="block text-sm font-semibold text-base-content/85 mb-1">Email Address</label>
                <input id="email" type="email" v-model="form.email" class="input input-bordered w-full" placeholder="name@example.com"
                    required autofocus autocomplete="username" />
                <div v-if="form.errors.email" class="mt-2 text-sm text-error font-medium">{{ form.errors.email }}
                </div>
            </div>

            <div class="pt-2">
                <button type="submit" :class="{ 'opacity-50': form.processing }" :disabled="form.processing"
                    class="btn btn-primary w-full shadow-md">
                    Send Magic Link
                </button>
            </div>

            <p class="text-center text-sm text-base-content/70">
                Wait, I remember!
                <Link href="/login" class="font-bold text-primary hover:text-secondary transition-colors">
                    Back to sign in
                </Link>
            </p>
        </form>
    </GuestLayout>
</template>
