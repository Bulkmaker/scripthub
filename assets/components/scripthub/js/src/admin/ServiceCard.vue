<template>
    <div
        class="service-card"
        style="border:1px solid var(--p-content-border-color);border-radius:12px;padding:1.25rem;cursor:pointer;background:var(--p-content-background)"
        @click="store.openPanel(service)"
    >
        <div style="display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:0.75rem">
            <div style="display:flex;align-items:center;gap:0.625rem">
                <span
                    v-if="service.iconSvg"
                    v-html="service.iconSvg"
                    style="width:28px;height:28px;flex-shrink:0;display:flex;align-items:center;justify-content:center"
                ></span>
                <i v-else :class="service.icon" style="font-size:1.5rem;color:var(--p-primary-color)"></i>
                <div>
                    <div style="font-weight:600;font-size:0.95rem">{{ service.name }}</div>
                </div>
            </div>
            <ToggleSwitch
                :modelValue="service.enabled"
                @update:modelValue="onToggle"
                @click.stop
            />
        </div>

        <p style="margin:0 0 0.75rem;font-size:0.8rem;color:var(--p-text-muted-color);line-height:1.4">
            {{ service.description }}
        </p>

        <div style="display:flex;align-items:center;gap:0.5rem">
            <Tag
                :value="service.configured ? 'Настроен' : 'Не настроен'"
                :severity="service.configured ? 'success' : 'warn'"
                style="font-size:0.7rem"
            />
            <Tag
                v-if="service.enabled && service.configured"
                value="Активен"
                severity="info"
                style="font-size:0.7rem"
            />
        </div>
    </div>
</template>

<script setup>
import { ToggleSwitch, Tag } from 'primevue'
import { useServiceStore } from '../stores/serviceStore.js'

const props = defineProps({
    service: { type: Object, required: true },
})

const store = useServiceStore()

async function onToggle(value) {
    await store.toggle(props.service.key, value)
}
</script>
