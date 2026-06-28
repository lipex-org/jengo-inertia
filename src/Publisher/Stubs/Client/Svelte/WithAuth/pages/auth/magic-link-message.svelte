<script>
    import { Head, Link, page } from '@inertiajs/svelte';
    import GuestLayout from '../../layouts/guest-layout.svelte';
</script>

<svelte:head>
    <title>Magic Link Sent</title>
</svelte:head>

<GuestLayout>
    {#if $page.props.flash?.message}
        <div class="mb-6 alert alert-success text-sm py-3 px-4 font-medium">
            {$page.props.flash.message}
        </div>
    {/if}

    <div class="mb-6 text-sm text-base-content/75 leading-relaxed">
        We have emailed you a magic link. Please check your inbox and click the link to log in.
    </div>

    <div class="mt-6 text-center">
        <Link href="/login" class="font-bold text-primary hover:text-secondary transition-colors">
            Back to Log in
        </Link>
    </div>
</GuestLayout>
