<template>
    <div
        class="service-dashboard"
        style="display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:1rem"
        @dragover.prevent="onDragOver"
        @drop="onDrop"
    >
        <ServiceCard
            v-for="service in store.filteredAdded"
            :key="service.key"
            :service="service"
            :data-key="service.key"
            draggable="true"
            @dragstart="onDragStart($event, service.key)"
            @dragend="onDragEnd"
        />

        <Message v-if="!store.loading && store.filteredAdded.length === 0 && store.addedServices.length > 0" severity="info">
            Сервисы не найдены
        </Message>
    </div>
</template>

<script setup>
import { ref } from 'vue'
import { Message } from 'primevue'
import { useServiceStore } from '../stores/serviceStore.js'
import ServiceCard from './ServiceCard.vue'

const store = useServiceStore()
const dragKey = ref(null)

function onDragStart(e, key) {
    dragKey.value = key
    e.dataTransfer.effectAllowed = 'move'
    e.currentTarget.style.opacity = '0.4'
}

function onDragEnd(e) {
    e.currentTarget.style.opacity = ''
    dragKey.value = null
}

function onDragOver(e) {
    e.dataTransfer.dropEffect = 'move'
}

function onDrop(e) {
    const target = e.target.closest('[data-key]')
    if (!target || !dragKey.value) return

    const dropKey = target.dataset.key
    if (dropKey === dragKey.value) return

    // Reorder
    const items = [...store.filteredAdded]
    const fromIdx = items.findIndex(s => s.key === dragKey.value)
    const toIdx = items.findIndex(s => s.key === dropKey)
    if (fromIdx === -1 || toIdx === -1) return

    items.splice(fromIdx, 1)
    items.splice(toIdx, 0, store.filteredAdded[fromIdx])

    // Save new positions
    const order = items.map((s, i) => ({ key: s.key, position: i + 1 }))
    store.sortServices(order)

    dragKey.value = null
}
</script>
