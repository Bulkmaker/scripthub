import { createApp } from 'vue'
import { createPinia } from 'pinia'
import { PrimeVue, Aura } from 'primevue'
import AdminApp from './admin/AdminApp.vue'

const el = document.getElementById('scripthub-admin-app')
if (el) {
    const app = createApp(AdminApp)
    app.use(createPinia())
    app.use(PrimeVue, {
        theme: {
            preset: Aura,
            options: { darkModeSelector: 'none' },
        },
        ripple: true,
    })
    app.provide('scriptHubConfig', window.ScriptHub?.config || {})
    app.mount(el)
}
