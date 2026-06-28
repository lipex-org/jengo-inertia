<script>
    import { Head } from '@inertiajs/svelte';
    import AppLayout from '../layouts/app-layout.svelte';
</script>

<svelte:head>
    <title>Dashboard</title>
</svelte:head>

<AppLayout>
    <span slot="header">Dashboard</span>

    <div class="card bg-base-100 border border-base-300/50 p-8 shadow-md">
        <h3 class="text-xl font-bold mb-2">Welcome!</h3>
        <p class="text-base-content/70">You are successfully logged in to your Jengo application.</p>
    </div>
</AppLayout>
