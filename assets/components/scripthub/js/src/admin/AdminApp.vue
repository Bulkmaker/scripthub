<template>
    <div class="scripthub-admin">
        <div class="scripthub-header">
            <div style="display:flex;align-items:center;gap:0.75rem">
                <div style="display:flex;align-items:center;justify-content:center;width:40px;height:40px;border-radius:10px;background:var(--p-primary-color, #10b981);color:#fff;font-size:1.25rem;flex-shrink:0">
                    <i class="pi pi-code"></i>
                </div>
                <div>
                    <div style="display:flex;align-items:center;gap:0.5rem;flex-wrap:wrap">
                        <h2 style="margin:0;font-size:1.35rem;font-weight:700;letter-spacing:-0.01em">scriptHub</h2>
                        <Tag :value="`${store.enabledCount} активно`" severity="success" v-if="store.enabledCount > 0" />
                    </div>
                    <div style="font-size:0.8rem;color:var(--p-text-muted-color, #9ca3af);margin-top:2px">
                        Управление внешними скриптами
                    </div>
                </div>
            </div>
            <IconField class="scripthub-search">
                <InputIcon class="pi pi-search" />
                <InputText v-model="store.searchQuery" placeholder="Поиск сервисов..." style="width:100%" />
            </IconField>
        </div>

        <ProgressBar v-if="store.loading" mode="indeterminate" style="height:3px;margin-bottom:1rem" />

        <ServiceDashboard />

        <ServiceConfigPanel />
    </div>
</template>

<script setup>
import { onMounted } from 'vue'
import { Tag, InputText, InputIcon, IconField, ProgressBar } from 'primevue'
import { useServiceStore } from '../stores/serviceStore.js'
import ServiceDashboard from './ServiceDashboard.vue'
import ServiceConfigPanel from './ServiceConfigPanel.vue'

const store = useServiceStore()

onMounted(() => {
    store.fetchAll()
})
</script>
