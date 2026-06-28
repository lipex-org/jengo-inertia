<script>
    import { Head, Link, useForm, page } from '@inertiajs/svelte';
    import GuestLayout from '../../layouts/guest-layout.svelte';

    let form = useForm({
        username: '',
        email: '',
        password: '',
        password_confirm: '',
    });

    function submit() {
        $form.post('/register', {
            onFinish: () => {
                // Done
            }
        });
    }
</script>

<svelte:head>
    <title>Create Account</title>
</svelte:head>

<GuestLayout>
    <div class="mb-8">
        <h1 class="text-2xl font-bold text-base-content">Create Account</h1>
        <p class="text-base-content/70 mt-1">Join the Jengo community today.</p>
    </div>

    {#if $page.props.flash?.error}
        <div class="mb-6 alert alert-error text-sm py-3 px-4 font-medium">
            {$page.props.flash.error}
        </div>
    {/if}

    <form on:submit|preventDefault={submit} class="space-y-5">
        <div>
            <label for="username" class="block text-sm font-semibold text-base-content/85 mb-1">Username</label>
            <input
                id="username"
                type="text"
                bind:value={$form.username}
                class="input input-bordered w-full"
                placeholder="johndoe"
                required
                autofocus
                autocomplete="username"
            />
            {#if $form.errors.username}
                <div class="mt-2 text-sm text-error font-medium">{$form.errors.username}</div>
            {/if}
        </div>

        <div>
            <label for="email" class="block text-sm font-semibold text-base-content/85 mb-1">Email Address</label>
            <input
                id="email"
                type="email"
                bind:value={$form.email}
                class="input input-bordered w-full"
                placeholder="name@example.com"
                required
                autocomplete="email"
            />
            {#if $form.errors.email}
                <div class="mt-2 text-sm text-error font-medium">{$form.errors.email}</div>
            {/if}
        </div>

        <div>
            <label for="password" class="block text-sm font-semibold text-base-content/85 mb-1">Password</label>
            <input
                id="password"
                type="password"
                bind:value={$form.password}
                class="input input-bordered w-full"
                placeholder="••••••••"
                required
                autocomplete="new-password"
            />
            {#if $form.errors.password}
                <div class="mt-2 text-sm text-error font-medium">{$form.errors.password}</div>
            {/if}
        </div>

        <div>
            <label for="password_confirm" class="block text-sm font-semibold text-base-content/85 mb-1">Confirm Password</label>
            <input
                id="password_confirm"
                type="password"
                bind:value={$form.password_confirm}
                class="input input-bordered w-full"
                placeholder="••••••••"
                required
                autocomplete="new-password"
            />
            {#if $form.errors.password_confirm}
                <div class="mt-2 text-sm text-error font-medium">{$form.errors.password_confirm}</div>
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
                    Create Account
                {/if}
            </button>
        </div>

        <p class="text-center text-sm text-base-content/70">
            Already have an account?{' '}
            <Link href="/login" class="font-bold text-primary hover:text-secondary transition-colors">
                Sign in
            </Link>
        </p>
    </form>
</GuestLayout>
