<script setup>
import { Head, Link, useForm, usePage } from '@inertiajs/vue3'
import GuestLayout from '../../Layouts/GuestLayout.vue'
import { computed } from 'vue'

const page = usePage()
const flash = computed(() => page.props.flash)

const form = useForm({
    username: '',
    email: '',
    password: '',
    password_confirm: '',
})

const submit = () => {
    form.post('/register', {
        onFinish: () => form.reset('password', 'password_confirm'),
    })
}
</script>

<template>
    <GuestLayout>
        <Head title="Create Account" />

        <div class="mb-8">
            <h1 class="text-2xl font-bold text-slate-900">Create Account</h1>
            <p class="text-slate-500 mt-1">Join the Jengo community today.</p>
        </div>

        <div v-if="flash.error" class="mb-6 p-4 rounded-xl bg-red-50 text-red-700 text-sm font-medium border border-red-100">
            {{ flash.error }}
        </div>

        <form @submit.prevent="submit" class="space-y-5">
            <div>
                <label for="username" class="block text-sm font-semibold text-slate-700 mb-1">Username</label>
                <input
                    id="username"
                    type="text"
                    v-model="form.username"
                    class="jengo-input"
                    placeholder="johndoe"
                    required
                    autofocus
                    autocomplete="username"
                />
                <div v-if="form.errors.username" class="mt-2 text-sm text-red-600 font-medium">{{ form.errors.username }}</div>
            </div>

            <div class="mt-4">
                <label for="email" class="block text-sm font-semibold text-slate-700 mb-1">Email Address</label>
                <input
                    id="email"
                    type="email"
                    v-model="form.email"
                    class="jengo-input"
                    placeholder="name@example.com"
                    required
                    autocomplete="email"
                />
                <div v-if="form.errors.email" class="mt-2 text-sm text-red-600 font-medium">{{ form.errors.email }}</div>
            </div>

            <div class="mt-4">
                <label for="password" class="block text-sm font-semibold text-slate-700 mb-1">Password</label>
                <input
                    id="password"
                    type="password"
                    v-model="form.password"
                    class="jengo-input"
                    placeholder="••••••••"
                    required
                    autocomplete="new-password"
                />
                <div v-if="form.errors.password" class="mt-2 text-sm text-red-600 font-medium">{{ form.errors.password }}</div>
            </div>

            <div class="mt-4">
                <label for="password_confirm" class="block text-sm font-semibold text-slate-700 mb-1">Confirm Password</label>
                <input
                    id="password_confirm"
                    type="password"
                    v-model="form.password_confirm"
                    class="jengo-input"
                    placeholder="••••••••"
                    required
                    autocomplete="new-password"
                />
                <div v-if="form.errors.password_confirm" class="mt-2 text-sm text-red-600 font-medium">{{ form.errors.password_confirm }}</div>
            </div>

            <div class="pt-2">
                <button
                    type="submit"
                    :class="{ 'opacity-50': form.processing }"
                    :disabled="form.processing"
                    class="jengo-button w-full"
                >
                    Create Account
                </button>
            </div>

            <p class="text-center text-sm text-slate-600">
                Already have an account?
                <Link href="/login" class="font-bold text-brand-primary hover:text-brand-secondary transition-colors">
                    Sign in
                </Link>
            </p>
        </form>
    </GuestLayout>
</template>
