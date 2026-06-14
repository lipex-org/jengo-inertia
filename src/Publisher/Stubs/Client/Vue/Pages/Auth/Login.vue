<script setup>
import { Head, Link, useForm, usePage } from '@inertiajs/vue3'
import GuestLayout from '../../Layouts/GuestLayout.vue'
import { computed } from 'vue'

const page = usePage()
const flash = computed(() => page.props.flash)

defineProps({
    canResetPassword: {
        type: Boolean,
    },
    status: {
        type: String,
    },
})

const form = useForm({
    email: '',
    password: '',
    remember: false,
})

const submit = () => {
    form.post('/login', {
        onFinish: () => form.reset('password'),
    })
}
</script>

<template>
    <GuestLayout>

        <Head title="Welcome Back" />

        <div class="mb-8">
            <h1 class="text-2xl font-bold text-slate-900">Welcome Back</h1>
            <p class="text-slate-500 mt-1">Please enter your details to sign in.</p>
        </div>

        <div v-if="status"
            class="mb-6 p-4 rounded-xl bg-green-50 text-green-700 text-sm font-medium border border-green-100">
            {{ status }}
        </div>

        <div v-if="flash?.error"
            class="mb-6 p-4 rounded-xl bg-red-50 text-red-700 text-sm font-medium border border-red-100">
            {{ flash.error }}
        </div>

        <form @submit.prevent="submit" class="space-y-5">
            <div>
                <label for="email" class="block text-sm font-semibold text-slate-700 mb-1">Email Address</label>
                <input id="email" type="email" v-model="form.email" class="jengo-input" placeholder="name@example.com"
                    required autofocus autocomplete="username" />
                <div v-if="form.errors.email" class="mt-2 text-sm text-red-600 font-medium">{{ form.errors.email }}
                </div>
            </div>

            <div>
                <div class="flex items-center justify-between mb-1">
                    <label for="password" class="block text-sm font-semibold text-slate-700">Password</label>
                    <Link v-if="canResetPassword" href="/login/magic-link"
                        class="text-xs font-semibold text-brand-primary hover:text-brand-secondary transition-colors">
                        Forgot password?
                    </Link>
                </div>
                <input id="password" type="password" v-model="form.password" class="jengo-input" placeholder="••••••••"
                    required autocomplete="current-password" />
                <div v-if="form.errors.password" class="mt-2 text-sm text-red-600 font-medium">{{ form.errors.password
                    }}</div>
            </div>

            <div class="flex items-center">
                <input id="remember" type="checkbox" v-model="form.remember"
                    class="h-5 w-5 text-brand-primary border-slate-300 rounded-md focus:ring-brand-primary/20" />
                <label for="remember" class="ml-2 text-sm text-slate-600 font-medium">Keep me signed in</label>
            </div>

            <div class="pt-2">
                <button type="submit" :class="{ 'opacity-50': form.processing }" :disabled="form.processing"
                    class="jengo-button w-full">
                    Sign In
                </button>
            </div>

            <p class="text-center text-sm text-slate-600">
                Don't have an account?
                <Link href="/register"
                    class="font-bold text-brand-primary hover:text-brand-secondary transition-colors">
                    Create account
                </Link>
            </p>
        </form>
    </GuestLayout>
</template>
