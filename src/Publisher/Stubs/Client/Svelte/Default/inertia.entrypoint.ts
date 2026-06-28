import { createInertiaApp } from '@inertiajs/svelte'
import { mount } from 'svelte'

createInertiaApp({
    resolve: name => {
        const pages = import.meta.glob('./pages/**/*.svelte', { eager: true })
        return pages[`./pages/${name}.svelte`] as any;
    },
    setup({ el, App, props }) {
        if (el) {
            mount(App, { target: el, props })
        }
    },
})
