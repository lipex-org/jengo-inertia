<script>
    import { Head, useForm, page } from '@inertiajs/svelte';
    import GuestLayout from '../../layouts/guest-layout.svelte';

    let form = useForm({
        token: ''
    });

    function submit() {
        $form.post('/auth/a/verify');
    }
</script>

<svelte:head>
    <title>Verify Code</title>
</svelte:head>

<GuestLayout>
    <div class="mb-8">
        <h1 class="text-2xl font-bold text-base-content">Verify Identity</h1>
        <p class="text-base-content/70 mt-1">Enter the 6-digit code sent to your email.</p>
    </div>

    {#if $page.props.flash?.error}
        <div class="mb-6 alert alert-error text-sm py-3 px-4 font-medium">
            {$page.props.flash.error}
        </div>
    {/if}

    <form on:submit|preventDefault={submit} class="space-y-5">
        <div>
            <label for="token" class="block text-sm font-semibold text-base-content/85 mb-1">Verification Code</label>
            <input
                id="token"
                type="text"
                bind:value={$form.token}
                class="input input-bordered w-full text-center text-2xl tracking-widest font-black"
                placeholder="000000"
                required
                autofocus
                autocomplete="one-time-code"
            />
            {#if $form.errors.token}
                <div class="mt-2 text-sm text-error font-medium">{$form.errors.token}</div>
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
                    Verify & Sign In
                {/if}
            </button>
        </div>
    </form>
</GuestLayout>
