<script>
    import { inertia } from '@inertiajs/svelte';
</script>

<div class="min-h-screen flex flex-col justify-center py-12 sm:px-6 lg:px-8 bg-gray-100 font-sans antialiased text-gray-900">
    <div class="sm:mx-auto sm:w-full sm:max-w-md">
        <a href="/" use:inertia class="flex justify-center text-4xl font-extrabold text-indigo-600">
            JENGO
        </a>
    </div>

    <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
        <div class="bg-white py-8 px-4 shadow sm:rounded-lg sm:px-10">
            <slot />
        </div>
    </div>
</div>
