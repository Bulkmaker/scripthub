<template>
    <Drawer
        v-model:visible="store.panelVisible"
        position="right"
        :header="store.activeService?.name || 'Настройки'"
        class="scripthub-drawer"
        appendTo="self"
        @hide="store.closePanel"
    >
        <template v-if="store.activeService">
            <!-- Description -->
            <p style="margin:0;padding:1rem 0;font-size:0.85rem;color:var(--p-text-muted-color);line-height:1.45">{{ store.activeService.description }}</p>

            <!-- Docs link -->
            <a
                v-if="store.activeService.docsUrl"
                :href="store.activeService.docsUrl"
                target="_blank"
                rel="noopener"
                style="display:inline-flex;align-items:center;gap:0.5rem;font-size:0.85rem;padding:0.5rem 1rem;border-radius:6px;background:var(--p-primary-50, #ecfdf5);color:var(--p-primary-color, #10b981);text-decoration:none;font-weight:500;transition:background 0.15s"
            >
                <span class="pi pi-book" style="font-size:1rem"></span>
                Документация
                <span class="pi pi-external-link" style="font-size:0.75rem;opacity:0.7"></span>
            </a>

            <Divider style="margin:1rem 0" />

            <!-- Fields -->
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

            <Divider style="margin:1rem 0" />

            <!-- Script Preview -->
            <ScriptPreview
                v-if="store.activeService.enabled && store.activeService.configured"
                :serviceKey="store.activeService.key"
            />

            <!-- Actions -->
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
