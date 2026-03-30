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
            <div style="display:flex;align-items:center;gap:0.75rem" class="scripthub-header-actions">
                <Button
                    label="Добавить сервис"
                    icon="pi pi-plus"
                    size="small"
                    @click="store.addDialogVisible = true"
                />
                <IconField class="scripthub-search" v-if="store.addedServices.length > 0">
                    <InputIcon class="pi pi-search" />
                    <InputText v-model="store.searchQuery" placeholder="Поиск..." style="width:100%" />
                </IconField>
            </div>
        </div>

        <ProgressBar v-if="store.loading" mode="indeterminate" style="height:3px;margin-bottom:1rem" />

        <!-- Empty state -->
        <div v-if="!store.loading && store.addedServices.length === 0" style="text-align:center;padding:4rem 1rem">
            <div style="font-size:3rem;margin-bottom:1rem;opacity:0.3">
                <i class="pi pi-box"></i>
            </div>
            <h3 style="margin:0 0 0.5rem;font-size:1.15rem;font-weight:600;color:var(--p-text-color)">Нет добавленных сервисов</h3>
            <p style="margin:0 0 1.5rem;font-size:0.9rem;color:var(--p-text-muted-color)">
                Добавьте первый сервис для управления скриптами
            </p>
            <Button
                label="Добавить сервис"
                icon="pi pi-plus"
                @click="store.addDialogVisible = true"
            />
        </div>

        <!-- Dashboard -->
        <ServiceDashboard v-else />

        <ServiceConfigPanel />
        <AddServiceDialog />
    </div>
</template>

<script setup>
import { onMounted } from 'vue'
import { Tag, InputText, InputIcon, IconField, ProgressBar, Button } from 'primevue'
import { useServiceStore } from '../stores/serviceStore.js'
import ServiceDashboard from './ServiceDashboard.vue'
import ServiceConfigPanel from './ServiceConfigPanel.vue'
import AddServiceDialog from './AddServiceDialog.vue'

const store = useServiceStore()

onMounted(() => {
    store.fetchAll()
})
</script>
