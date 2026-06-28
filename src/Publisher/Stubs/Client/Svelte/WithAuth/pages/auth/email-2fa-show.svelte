<script>
    import { Head, useForm, page } from '@inertiajs/svelte';
    import GuestLayout from '../../layouts/guest-layout.svelte';

    export let user;

    let form = useForm({
        email: ''
    });

    function submit() {
        $form.post('/auth/a/handle');
    }
</script>

<svelte:head>
    <title>2FA Verification</title>
</svelte:head>

<GuestLayout>
    <div class="mb-8">
        <h1 class="text-2xl font-bold text-base-content">2FA Verification</h1>
        <p class="text-base-content/70 mt-1">Please confirm your email to receive a code.</p>
    </div>

    {#if $page.props.flash?.error}
        <div class="mb-6 alert alert-error text-sm py-3 px-4 font-medium">
            {$page.props.flash.error}
        </div>
    {/if}

    <form on:submit|preventDefault={submit} class="space-y-5">
        <div>
            <label for="email" class="block text-sm font-semibold text-base-content/85 mb-1">Confirm Email</label>
            <input
                id="email"
                type="email"
                bind:value={$form.email}
                class="input input-bordered w-full"
                placeholder="name@example.com"
                required
                autofocus
            />
            {#if $form.errors.email}
                <div class="mt-2 text-sm text-error font-medium">{$form.errors.email}</div>
            {/if}
        </div>

        <div class="pt-2">
            <button
                type="submit"
                disabled={$form.processing}
                class="btn btn-primary w-full shadow-md"
            >
                {#if $form.processing}
                    <span class="loading loading-spinner"></span>
                {:else}
                    Send Code
                {/if}
            </button>
        </div>
    </form>
</GuestLayout>
