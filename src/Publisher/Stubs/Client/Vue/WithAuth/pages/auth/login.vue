<script setup>
import { Head, Link, useForm, usePage } from '@inertiajs/vue3'
import GuestLayout from '../../layouts/guest-layout.vue'
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
            <h1 class="text-2xl font-bold text-base-content">Welcome Back</h1>
            <p class="text-base-content/70 mt-1">Please enter your details to sign in.</p>
        </div>

        <div v-if="status"
            class="mb-6 alert alert-success text-sm py-3 px-4 font-medium">
            {{ status }}
        </div>

        <div v-if="flash?.error"
            class="mb-6 alert alert-error text-sm py-3 px-4 font-medium">
            {{ flash.error }}
        </div>

        <form @submit.prevent="submit" class="space-y-5">
            <div>
                <label for="email" class="block text-sm font-semibold text-base-content/85 mb-1">Email Address</label>
                <input id="email" type="email" v-model="form.email" class="input input-bordered w-full" placeholder="name@example.com"
                    required autofocus autocomplete="username" />
                <div v-if="form.errors.email" class="mt-2 text-sm text-error font-medium">{{ form.errors.email }}
                </div>
            </div>

            <div>
                <div class="flex items-center justify-between mb-1">
                    <label for="password" class="block text-sm font-semibold text-base-content/85">Password</label>
                    <Link v-if="canResetPassword" href="/login/magic-link"
                        class="text-xs font-semibold text-primary hover:text-secondary transition-colors">
                        Forgot password?
                    </Link>
                </div>
                <input id="password" type="password" v-model="form.password" class="input input-bordered w-full" placeholder="••••••••"
                    required autocomplete="current-password" />
                <div v-if="form.errors.password" class="mt-2 text-sm text-error font-medium">{{ form.errors.password
                    }}</div>
            </div>

            <div class="flex items-center">
                <input id="remember" type="checkbox" v-model="form.remember"
                    class="checkbox checkbox-primary" />
                <label for="remember" class="ml-2 text-sm text-base-content/75 font-medium select-none">Keep me signed in</label>
            </div>

            <div class="pt-2">
                <button type="submit" :class="{ 'opacity-50': form.processing }" :disabled="form.processing"
                    class="btn btn-primary w-full shadow-md">
                    Sign In
                </button>
            </div>

            <p class="text-center text-sm text-base-content/70">
                Don't have an account?
                <Link href="/register"
                    class="font-bold text-primary hover:text-secondary transition-colors">
                    Create account
                </Link>
            </p>
        </form>
    </GuestLayout>
</template>
