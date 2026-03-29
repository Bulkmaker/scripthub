<template>
    <div style="margin-top:1rem">
        <div style="display:flex;align-items:center;gap:0.5rem;margin-bottom:0.5rem">
            <i class="pi pi-code" style="font-size:0.875rem"></i>
            <span style="font-size:0.85rem;font-weight:500">Превью кода</span>
        </div>
        <div class="script-preview">
            <code v-if="preview">{{ preview }}</code>
            <span v-else style="color:#888">Настройте сервис для просмотра кода</span>
        </div>
    </div>
</template>

<script setup>
import { ref, watch } from 'vue'
import { useScriptHub } from '../composables/useScriptHub.js'

const props = defineProps({
    serviceKey: { type: String, required: true },
})

const api = useScriptHub()
const preview = ref('')

watch(() => props.serviceKey, async (key) => {
    if (key) {
        try {
            const res = await api.getService(key)
            if (res?.object?.preview) {
                preview.value = res.object.preview
            }
        } catch {
            preview.value = ''
        }
    }
}, { immediate: true })
</script>
