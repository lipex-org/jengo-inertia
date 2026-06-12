<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3'
import GuestLayout from '../../Layouts/GuestLayout.vue'

const form = useForm({
    username: '',
    email: '',
    password: '',
    password_confirm: '',
})

const submit = () => {
    form.post('/register')
}
</script>

<template>
    <GuestLayout>
        <Head title="Register" />

        <form @submit.prevent="submit">
            <div>
                <label for="username" class="block text-sm font-medium text-gray-700">Username</label>
                <input
                    id="username"
                    type="text"
                    v-model="form.username"
                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                    required
                    autofocus
                    autocomplete="username"
                />
                <div v-if="form.errors.username" class="mt-2 text-sm text-red-600">{{ form.errors.username }}</div>
            </div>

            <div class="mt-4">
                <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                <input
                    id="email"
                    type="email"
                    v-model="form.email"
                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                    required
                    autocomplete="email"
                />
                <div v-if="form.errors.email" class="mt-2 text-sm text-red-600">{{ form.errors.email }}</div>
            </div>

            <div class="mt-4">
                <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                <input
                    id="password"
                    type="password"
                    v-model="form.password"
                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                    required
                    autocomplete="new-password"
                />
                <div v-if="form.errors.password" class="mt-2 text-sm text-red-600">{{ form.errors.password }}</div>
            </div>

            <div class="mt-4">
                <label for="password_confirm" class="block text-sm font-medium text-gray-700">Confirm Password</label>
                <input
                    id="password_confirm"
                    type="password"
                    v-model="form.password_confirm"
                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                    required
                    autocomplete="new-password"
                />
                <div v-if="form.errors.password_confirm" class="mt-2 text-sm text-red-600">{{ form.errors.password_confirm }}</div>
            </div>

            <div class="mt-6">
                <button
                    type="submit"
                    :class="{ 'opacity-50': form.processing }"
                    :disabled="form.processing"
                    class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                >
                    Register
                </button>
            </div>

            <p class="mt-4 text-center text-sm text-gray-600">
                Already have an account?
                <Link href="/login" class="font-medium text-indigo-600 hover:text-indigo-500">
                    Log in
                </Link>
            </p>
        </form>
    </GuestLayout>
</template>
