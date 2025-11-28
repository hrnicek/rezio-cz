import '../css/app.css';

import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import type { DefineComponent } from 'vue';
import { createApp, h } from 'vue';
import { createPinia } from 'pinia';
import VCalendar from 'v-calendar';
import 'v-calendar/style.css';
import { ZiggyVue } from 'ziggy-js';
import { DarkModeManager } from './utils/darkMode';
import 'vue-sonner/style.css'

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

// Dark mode temporarily disabled - using light mode only
// DarkModeManager.initialize();

createInertiaApp({
    title: (title) => (title ? `${title} - ${appName}` : appName),
    resolve: (name) => resolvePageComponent(`./Pages/${name}.vue`, import.meta.glob<DefineComponent>('./Pages/**/*.vue')),
    setup({ el, App, props, plugin }) {
        createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(ZiggyVue)
            .use(createPinia())
            .use(VCalendar, {})
            .mount(el);
    },
    progress: {
        color: '#4B5563',
    },
});
