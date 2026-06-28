<script>
    import { Head, Link, useForm, page } from '@inertiajs/svelte';
    import GuestLayout from '../../layouts/guest-layout.svelte';

    let form = useForm({
        email: '',
    });

    function submit() {
        $form.post('/login/magic-link');
    }
</script>

<svelte:head>
    <title>Magic Link</title>
</svelte:head>

<GuestLayout>
    <div class="mb-8">
        <h1 class="text-2xl font-bold text-base-content">Forgot Password?</h1>
        <p class="text-base-content/70 mt-1">We'll send you a link to sign in instantly.</p>
    </div>

    {#if $page.props.flash?.error}
        <div class="mb-6 alert alert-error text-sm py-3 px-4 font-medium">
            {$page.props.flash.error}
        </div>
    {/if}

    {#if $page.props.flash?.message}
        <div class="mb-6 alert alert-success text-sm py-3 px-4 font-medium">
            {$page.props.flash.message}
        </div>
    {/if}

    <form on:submit|preventDefault={submit} class="space-y-5">
        <div>
            <label for="email" class="block text-sm font-semibold text-base-content/85 mb-1">Email Address</label>
            <input
                id="email"
                type="email"
                bind:value={$form.email}
                class="input input-bordered w-full"
                placeholder="name@example.com"
                required
                autofocus
                autocomplete="username"
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
                    Send Magic Link
                {/if}
            </button>
        </div>

        <p class="text-center text-sm text-base-content/70">
            Wait, I remember!{' '}
            <Link href="/login" class="font-bold text-primary hover:text-secondary transition-colors">
                Back to sign in
            </Link>
        </p>
    </form>
</GuestLayout>
