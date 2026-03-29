import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import { useScriptHub } from '../composables/useScriptHub.js'

export const useServiceStore = defineStore('serviceStore', () => {
    const api = useScriptHub()

    const services = ref([])
    const loading = ref(false)
    const saving = ref(false)
    const activeService = ref(null)
    const panelVisible = ref(false)
    const searchQuery = ref('')

    const categories = [
        { key: 'analytics', label: 'Аналитика', icon: 'pi pi-chart-bar' },
        { key: 'pixels', label: 'Рекламные пиксели', icon: 'pi pi-bullseye' },
        { key: 'chats', label: 'Чаты и коммуникации', icon: 'pi pi-comments' },
        { key: 'leadgen', label: 'Лидогенерация', icon: 'pi pi-megaphone' },
    ]

    const filteredServices = computed(() => {
        if (!searchQuery.value) return services.value
        const q = searchQuery.value.toLowerCase()
        return services.value.filter(s =>
            s.name.toLowerCase().includes(q) ||
            s.description.toLowerCase().includes(q)
        )
    })

    const grouped = computed(() => {
        const groups = {}
        for (const cat of categories) {
            groups[cat.key] = filteredServices.value.filter(s => s.category === cat.key)
        }
        return groups
    })

    const enabledCount = computed(() => {
        return services.value.filter(s => s.enabled).length
    })

    async function fetchAll() {
        loading.value = true
        try {
            const res = await api.getServices()
            if (res.success !== false) {
                services.value = res.results || res.data || []
            }
        } catch (e) {
            console.error('[scriptHub] Failed to load services:', e)
        } finally {
            loading.value = false
        }
    }

    async function saveConfig(serviceKey, config) {
        saving.value = true
        try {
            const res = await api.updateService(serviceKey, config)
            if (res.success !== false) {
                const idx = services.value.findIndex(s => s.key === serviceKey)
                if (idx !== -1) {
                    services.value[idx] = { ...services.value[idx], ...res.object, config }
                }
            }
            return res
        } finally {
            saving.value = false
        }
    }

    async function toggle(serviceKey, enabled) {
        try {
            const res = await api.toggleService(serviceKey, enabled)
            if (res.success !== false) {
                const idx = services.value.findIndex(s => s.key === serviceKey)
                if (idx !== -1) {
                    services.value[idx].enabled = enabled
                }
            }
            return res
        } catch (e) {
            console.error('[scriptHub] Toggle failed:', e)
        }
    }

    async function refreshAsset(serviceKey) {
        try {
            return await api.refreshAsset(serviceKey)
        } catch (e) {
            console.error('[scriptHub] Refresh asset failed:', e)
        }
    }

    function openPanel(service) {
        activeService.value = { ...service, config: { ...service.config } }
        panelVisible.value = true
    }

    function closePanel() {
        panelVisible.value = false
        activeService.value = null
    }

    return {
        services,
        loading,
        saving,
        activeService,
        panelVisible,
        searchQuery,
        categories,
        grouped,
        enabledCount,
        filteredServices,
        fetchAll,
        saveConfig,
        toggle,
        refreshAsset,
        openPanel,
        closePanel,
    }
})
