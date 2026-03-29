<template>
    <div class="service-dashboard">
        <div v-for="cat in store.categories" :key="cat.key" class="category-section" style="margin-bottom:2rem">
            <template v-if="store.grouped[cat.key]?.length">
                <div class="category-header">
                    <i :class="cat.icon" style="font-size:1.25rem;color:var(--p-primary-color)"></i>
                    <h3 style="margin:0;font-size:1.1rem;font-weight:600">{{ cat.label }}</h3>
                    <Tag :value="enabledInCategory(cat.key)" severity="secondary" style="font-size:0.75rem" />
                </div>

                <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:1rem">
                    <ServiceCard
                        v-for="service in store.grouped[cat.key]"
                        :key="service.key"
                        :service="service"
                    />
                </div>
            </template>
        </div>

        <Message v-if="!store.loading && store.filteredServices.length === 0" severity="info">
            Сервисы не найдены
        </Message>
    </div>
</template>

<script setup>
import { Tag, Message } from 'primevue'
import { useServiceStore } from '../stores/serviceStore.js'
import ServiceCard from './ServiceCard.vue'

const store = useServiceStore()

function enabledInCategory(catKey) {
    const services = store.grouped[catKey] || []
    const enabled = services.filter(s => s.enabled).length
    return `${enabled}/${services.length}`
}
</script>
