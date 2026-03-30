<template>
    <Dialog
        v-model:visible="store.addDialogVisible"
        header="Добавить сервис"
        modal
        :style="{ width: 'min(640px, 95vw)' }"
        :contentStyle="{ padding: '0 1.5rem 1.5rem' }"
    >
        <!-- Search -->
        <div style="padding:0.75rem 0 1rem">
            <IconField>
                <InputIcon class="pi pi-search" />
                <InputText v-model="dialogSearch" placeholder="Поиск..." style="width:100%" />
            </IconField>
        </div>

        <!-- Grouped service cards -->
        <div v-for="cat in filteredCategories" :key="cat.key" style="margin-bottom:1.25rem">
            <div style="font-size:0.8rem;font-weight:600;color:var(--p-text-muted-color);text-transform:uppercase;letter-spacing:0.05em;margin-bottom:0.5rem">
                {{ cat.label }}
            </div>
            <div style="display:grid;grid-template-columns:repeat(auto-fill, minmax(160px, 1fr));gap:0.5rem">
                <div
                    v-for="service in cat.services"
                    :key="service.key"
                    @click="onAdd(service.key)"
                    style="display:flex;align-items:center;gap:0.5rem;padding:0.625rem 0.75rem;border:1px solid var(--p-content-border-color, #e5e7eb);border-radius:8px;cursor:pointer;transition:all 0.15s;background:#fff"
                    @mouseenter="$event.currentTarget.style.borderColor='var(--p-primary-color)';$event.currentTarget.style.boxShadow='0 2px 8px rgba(0,0,0,0.08)'"
                    @mouseleave="$event.currentTarget.style.borderColor='';$event.currentTarget.style.boxShadow=''"
                >
                    <span
                        v-if="service.iconSvg"
                        v-html="service.iconSvg"
                        style="width:24px;height:24px;flex-shrink:0;display:flex;align-items:center;justify-content:center"
                    ></span>
                    <span v-else :class="service.icon" style="font-size:1.25rem;color:var(--p-primary-color);flex-shrink:0"></span>
                    <span style="font-size:0.85rem;font-weight:500;line-height:1.2">{{ service.name }}</span>
                </div>
            </div>
        </div>

        <!-- Empty state -->
        <div v-if="filteredCategories.length === 0" style="text-align:center;padding:2rem 0;color:var(--p-text-muted-color)">
            <span class="pi pi-check-circle" style="font-size:2rem;display:block;margin-bottom:0.5rem"></span>
            <div style="font-size:0.9rem">Все сервисы уже добавлены</div>
        </div>
    </Dialog>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Dialog, InputText, InputIcon, IconField } from 'primevue'
import { useServiceStore } from '../stores/serviceStore.js'

const store = useServiceStore()
const dialogSearch = ref('')

const filteredCategories = computed(() => {
    const q = dialogSearch.value.toLowerCase()
    const result = []
    for (const cat of store.categories) {
        let items = store.availableServices.filter(s => s.category === cat.key)
        if (q) {
            items = items.filter(s => s.name.toLowerCase().includes(q))
        }
        if (items.length) {
            result.push({ ...cat, services: items })
        }
    }
    return result
})

function onAdd(key) {
    store.addService(key)
}
</script>
