<script setup>
import { Head, Link, useForm, usePage } from '@inertiajs/vue3'
import GuestLayout from '../../Layouts/GuestLayout.vue'
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
            <h1 class="text-2xl font-bold text-slate-900">Forgot Password?</h1>
            <p class="text-slate-500 mt-1">We'll send you a link to sign in instantly.</p>
        </div>

        <div v-if="flash?.error"
            class="mb-6 p-4 rounded-xl bg-red-50 text-red-700 text-sm font-medium border border-red-100">
            {{ flash.error }}
        </div>

        <div v-if="flash?.message"
            class="mb-6 p-4 rounded-xl bg-green-50 text-green-700 text-sm font-medium border border-green-100">
            {{ flash.message }}
        </div>

        <form @submit.prevent="submit" class="space-y-5">
            <div>
                <label for="email" class="block text-sm font-semibold text-slate-700 mb-1">Email Address</label>
                <input id="email" type="email" v-model="form.email" class="jengo-input" placeholder="name@example.com"
                    required autofocus autocomplete="username" />
                <div v-if="form.errors.email" class="mt-2 text-sm text-red-600 font-medium">{{ form.errors.email }}
                </div>
            </div>

            <div class="pt-2">
                <button type="submit" :class="{ 'opacity-50': form.processing }" :disabled="form.processing"
                    class="jengo-button w-full">
                    Send Magic Link
                </button>
            </div>

            <p class="text-center text-sm text-slate-600">
                Wait, I remember!
                <Link href="/login" class="font-bold text-brand-primary hover:text-brand-secondary transition-colors">
                    Back to sign in
                </Link>
            </p>
        </form>
    </GuestLayout>
</template>
