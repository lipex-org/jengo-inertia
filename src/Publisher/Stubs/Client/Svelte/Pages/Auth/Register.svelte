<script>
    import { inertia, useForm } from "@inertiajs/svelte";
    import GuestLayout from "../../Layouts/GuestLayout.svelte";

    let form = useForm({
        username: "",
        email: "",
        password: "",
        password_confirm: "",
    });

    function submit() {
        $form.post("/register");
    }
</script>

<svelte:head>
    <title>Register</title>
</svelte:head>

<GuestLayout>
    <form on:submit|preventDefault={submit}>
        <div>
            <label
                for="username"
                class="block text-sm font-medium text-gray-700">Username</label
            >
            <input
                id="username"
                type="text"
                bind:value={$form.username}
                class="mt-1 block w-full p-2 border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                required
                autocomplete="username"
            />
            {#if $form.errors.username}
                <div class="mt-2 text-sm text-red-600">
                    {$form.errors.username}
                </div>
            {/if}
        </div>

        <div class="mt-4">
            <label for="email" class="block text-sm font-medium text-gray-700"
                >Email</label
            >
            <input
                id="email"
                type="email"
                bind:value={$form.email}
                class="mt-1 block w-full p-2 border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                required
                autocomplete="email"
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
                autocomplete="new-password"
            />
            {#if $form.errors.password}
                <div class="mt-2 text-sm text-red-600">
                    {$form.errors.password}
                </div>
            {/if}
        </div>

        <div class="mt-4">
            <label
                for="password_confirm"
                class="block text-sm font-medium text-gray-700"
                >Confirm Password</label
            >
            <input
                id="password_confirm"
                type="password"
                bind:value={$form.password_confirm}
                class="mt-1 block w-full p-2 border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                required
                autocomplete="new-password"
            />
            {#if $form.errors.password_confirm}
                <div class="mt-2 text-sm text-red-600">
                    {$form.errors.password_confirm}
                </div>
            {/if}
        </div>

        <div class="mt-6">
            <button
                type="submit"
                disabled={$form.processing}
                class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 {$form.processing
                    ? 'opacity-50'
                    : ''}"
            >
                Register
            </button>
        </div>

        <p class="mt-4 text-center text-sm text-gray-600">
            Already have an account?
            <a
                href="/login"
                use:inertia
                class="font-medium text-indigo-600 hover:text-indigo-500"
            >
                Log in
            </a>
        </p>
    </form>
</GuestLayout>
