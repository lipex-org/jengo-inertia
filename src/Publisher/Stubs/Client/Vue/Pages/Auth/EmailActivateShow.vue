<script setup>
import { Head, useForm, usePage } from '@inertiajs/vue3'
import GuestLayout from '../../Layouts/GuestLayout.vue'
import { computed } from 'vue'

const page = usePage()
const flash = computed(() => page.props.flash)

const form = useForm({
    token: ''
})

const submit = () => {
    form.post('/auth/a/verify')
}
</script>

<template>
    <GuestLayout>

        <Head title="Activate Account" />

        <div class="mb-8">
            <h1 class="text-2xl font-bold text-slate-900">Activate Account</h1>
            <p class="text-slate-500 mt-1">Please enter the code sent to your email.</p>
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
                <label for="token" class="block text-sm font-semibold text-slate-700 mb-1">Activation Code</label>
                <input id="token" type="text" v-model="form.token"
                    class="jengo-input text-center text-2xl letter-spacing-widest font-black" placeholder="000000"
                    required autofocus autocomplete="one-time-code" />
                <div v-if="form.errors.token" class="mt-2 text-sm text-red-600 font-medium">{{ form.errors.token }}
                </div>
            </div>

            <div class="pt-2">
                <button type="submit" :class="{ 'opacity-50': form.processing }" :disabled="form.processing"
                    class="jengo-button w-full">
                    Verify Code
                </button>
            </div>
        </form>
    </GuestLayout>
</template>
