<script>
    import { Head, Link, useForm, page } from '@inertiajs/svelte';
    import GuestLayout from '../../layouts/guest-layout.svelte';

    export let canResetPassword = false;
    export let status = null;

    let form = useForm({
        email: '',
        password: '',
        remember: false,
    });

    function submit() {
        $form.post('/login', {
            onFinish: () => {
                // Done
            }
        });
    }
</script>

<svelte:head>
    <title>Welcome Back</title>
</svelte:head>

<GuestLayout>
    <div class="mb-8">
        <h1 class="text-2xl font-bold text-base-content">Welcome Back</h1>
        <p class="text-base-content/70 mt-1">Please enter your details to sign in.</p>
    </div>

    {#if status}
        <div class="mb-6 alert alert-success text-sm py-3 px-4 font-medium">
            {status}
        </div>
    {/if}

    {#if $page.props.flash?.error}
        <div class="mb-6 alert alert-error text-sm py-3 px-4 font-medium">
            {$page.props.flash.error}
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

        <div>
            <div class="flex items-center justify-between mb-1">
                <label for="password" class="block text-sm font-semibold text-base-content/85">Password</label>
                {#if canResetPassword}
                    <Link href="/login/magic-link" class="text-xs font-semibold text-primary hover:text-secondary transition-colors">
                        Forgot password?
                    </Link>
                {/if}
            </div>
            <input
                id="password"
                type="password"
                bind:value={$form.password}
                class="input input-bordered w-full"
                placeholder="••••••••"
                required
                autocomplete="current-password"
            />
            {#if $form.errors.password}
                <div class="mt-2 text-sm text-error font-medium">{$form.errors.password}</div>
            {/if}
        </div>

        <div class="flex items-center">
            <input
                id="remember"
                type="checkbox"
                bind:checked={$form.remember}
                class="checkbox checkbox-primary"
            />
            <label for="remember" class="ml-2 text-sm text-base-content/75 font-medium select-none">Keep me signed in</label>
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
                    Sign In
                {/if}
            </button>
        </div>

        <p class="text-center text-sm text-base-content/70">
            Don't have an account?{' '}
            <Link href="/register" class="font-bold text-primary hover:text-secondary transition-colors">
                Create account
            </Link>
        </p>
    </form>
</GuestLayout>
