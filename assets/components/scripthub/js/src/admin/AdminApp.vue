<template>
    <div class="scripthub-admin">
        <div class="scripthub-header" style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.5rem">
            <div style="display:flex;align-items:center;gap:0.75rem">
                <h2 style="margin:0;font-size:1.5rem;font-weight:600">scriptHub</h2>
                <Tag :value="`${store.enabledCount} активно`" severity="success" v-if="store.enabledCount > 0" />
            </div>
            <IconField>
                <InputIcon class="pi pi-search" />
                <InputText v-model="store.searchQuery" placeholder="Поиск сервисов..." style="width:260px" />
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
