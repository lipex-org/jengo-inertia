<script>
    import { inertia, useForm } from "@inertiajs/svelte";
    import GuestLayout from "../../Layouts/GuestLayout.svelte";

    export let error = null;

    let form = useForm({
        email: "",
        password: "",
        remember: false,
    });

    function submit() {
        $form.post("/login");
    }
</script>

<svelte:head>
    <title>Log in</title>
</svelte:head>

<GuestLayout>
    {#if error}
        <div class="mb-4 font-medium text-sm text-red-600">{error}</div>
    {/if}

    <form on:submit|preventDefault={submit}>
        <div>
            <label for="email" class="block text-sm font-medium text-gray-700"
                >Email</label
            >
            <input
                id="email"
                type="email"
                bind:value={$form.email}
                class="mt-1 block w-full p-2 border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                required
                autocomplete="username"
            />
            {#if $form.errors.email}
                <div class="mt-2 text-sm text-red-600">
                    {$form.errors.email}
                </div>
            {/if}
        </div>

        <div class="mt-4">
            <label
                for="password"
                class="block text-sm font-medium text-gray-700">Password</label
            >
            <input
                id="password"
                type="password"
                bind:value={$form.password}
                class="mt-1 block w-full p-2 border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                required
                autocomplete="current-password"
            />
            {#if $form.errors.password}
                <div class="mt-2 text-sm text-red-600">
                    {$form.errors.password}
                </div>
            {/if}
        </div>

        <div class="block mt-4">
            <label class="flex items-center">
                <input
                    type="checkbox"
                    bind:checked={$form.remember}
                    class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
                />
                <span class="ml-2 text-sm text-gray-600">Remember me</span>
            </label>
        </div>

        <div class="mt-6">
            <button
                type="submit"
                disabled={$form.processing}
                class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 {$form.processing
                    ? 'opacity-50'
                    : ''}"
            >
                Log in
            </button>
        </div>

        <p class="mt-4 text-center text-sm text-gray-600">
            Don't have an account?
            <a
                href="/register"
                use:inertia
                class="font-medium text-indigo-600 hover:text-indigo-500"
            >
                Register
            </a>
        </p>
    </form>
</GuestLayout>
