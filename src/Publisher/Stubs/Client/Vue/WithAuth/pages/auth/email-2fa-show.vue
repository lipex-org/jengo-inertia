<script setup>
import { Head, useForm, usePage } from '@inertiajs/vue3'
import GuestLayout from '../../layouts/guest-layout.vue'
import { computed } from 'vue'

const page = usePage()
const flash = computed(() => page.props.flash)

defineProps({
    user: Object
})

const form = useForm({
    email: ''
})

const submit = () => {
    form.post('/auth/a/handle')
}
</script>

<template>
    <GuestLayout>

        <Head title="2FA Verification" />

        <div class="mb-8">
            <h1 class="text-2xl font-bold text-slate-900">2FA Verification</h1>
            <p class="text-slate-500 mt-1">Please confirm your email to receive a code.</p>
        </div>

        <div v-if="flash?.error"
            class="mb-6 p-4 rounded-xl bg-red-50 text-red-700 text-sm font-medium border border-red-100">
            {{ flash.error }}
        </div>

        <form @submit.prevent="submit" class="space-y-5">
            <div>
                <label for="email" class="block text-sm font-semibold text-slate-700 mb-1">Confirm Email</label>
                <input id="email" type="email" v-model="form.email" class="jengo-input" placeholder="name@example.com"
                    required autofocus />
                <div v-if="form.errors.email" class="mt-2 text-sm text-red-600 font-medium">{{ form.errors.email }}
                </div>
            </div>

            <div class="pt-2">
                <button type="submit" :class="{ 'opacity-50': form.processing }" :disabled="form.processing"
                    class="jengo-button w-full">
                    Send Code
                </button>
            </div>
        </form>
    </GuestLayout>
</template>
