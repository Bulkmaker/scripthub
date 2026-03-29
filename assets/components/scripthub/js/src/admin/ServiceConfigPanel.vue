<template>
    <Drawer
        v-model:visible="store.panelVisible"
        position="right"
        :header="store.activeService?.name || 'Настройки'"
        style="width:480px"
        appendTo="self"
        @hide="store.closePanel"
    >
        <template v-if="store.activeService">
            <div style="margin-bottom:1rem;display:flex;align-items:center;gap:0.5rem">
                <i :class="store.activeService.icon" style="font-size:1.25rem;color:var(--p-primary-color)"></i>
                <span style="font-size:0.85rem;color:var(--p-text-muted-color)">{{ store.activeService.description }}</span>
            </div>

            <a
                v-if="store.activeService.docsUrl"
                :href="store.activeService.docsUrl"
                target="_blank"
                style="display:inline-flex;align-items:center;gap:0.375rem;font-size:0.8rem;margin-bottom:1.25rem;text-decoration:none"
            >
                <i class="pi pi-external-link" style="font-size:0.75rem"></i>
                Документация
            </a>

            <Divider />

            <ServiceFieldRenderer
                :fields="store.activeService.fields"
                :config="localConfig"
                :errors="errors"
            />

            <!-- Self-hosted refresh button for Yandex Metrika -->
            <div v-if="store.activeService.key === 'yandex-metrika' && localConfig.self_hosted" style="margin-bottom:1.25rem">
                <Button
                    label="Скачать tag.js на сервер"
                    icon="pi pi-download"
                    severity="secondary"
                    size="small"
                    :loading="refreshing"
                    @click="onRefreshAsset"
                />
            </div>

            <Divider />

            <!-- Script Preview -->
            <ScriptPreview
                v-if="store.activeService.enabled && store.activeService.configured"
                :serviceKey="store.activeService.key"
            />

            <div style="display:flex;gap:0.75rem;margin-top:1.5rem">
                <Button
                    label="Сохранить"
                    icon="pi pi-check"
                    :loading="store.saving"
                    @click="onSave"
                />
                <Button
                    label="Отмена"
                    icon="pi pi-times"
                    severity="secondary"
                    outlined
                    @click="store.closePanel"
                />
            </div>
        </template>
    </Drawer>
</template>

<script setup>
import { ref, watch } from 'vue'
import { Drawer, Button, Divider } from 'primevue'
import { useServiceStore } from '../stores/serviceStore.js'
import ServiceFieldRenderer from './ServiceFieldRenderer.vue'
import ScriptPreview from './ScriptPreview.vue'

const store = useServiceStore()
const localConfig = ref({})
const errors = ref({})
const refreshing = ref(false)

watch(() => store.activeService, (service) => {
    if (service) {
        // Initialize config with defaults from fields
        const cfg = { ...service.config }
        for (const field of service.fields) {
            if (cfg[field.key] === undefined && field.default !== undefined) {
                cfg[field.key] = field.default
            }
        }
        localConfig.value = cfg
        errors.value = {}
    }
}, { immediate: true })

async function onSave() {
    errors.value = {}
    const res = await store.saveConfig(store.activeService.key, localConfig.value)
    if (res?.success === false && res?.errors) {
        errors.value = res.errors
    } else {
        store.closePanel()
    }
}

async function onRefreshAsset() {
    refreshing.value = true
    try {
        await store.refreshAsset(store.activeService.key)
    } finally {
        refreshing.value = false
    }
}
</script>
