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
    const addDialogVisible = ref(false)
    const searchQuery = ref('')

    const categories = [
        { key: 'analytics', label: 'Аналитика', icon: 'pi pi-chart-bar' },
        { key: 'pixels', label: 'Рекламные пиксели', icon: 'pi pi-bullseye' },
        { key: 'chats', label: 'Чаты и коммуникации', icon: 'pi pi-comments' },
        { key: 'leadgen', label: 'Лидогенерация', icon: 'pi pi-megaphone' },
    ]

    // Services added to dashboard, sorted by position
    const addedServices = computed(() => {
        return services.value
            .filter(s => s.added)
            .sort((a, b) => a.position - b.position)
    })

    // Services available to add (not yet on dashboard)
    const availableServices = computed(() => {
        return services.value.filter(s => !s.added)
    })

    // Available services grouped by category (for add dialog)
    const availableGrouped = computed(() => {
        const groups = {}
        for (const cat of categories) {
            const items = availableServices.value.filter(s => s.category === cat.key)
            if (items.length) {
                groups[cat.key] = items
            }
        }
        return groups
    })

    const enabledCount = computed(() => {
        return services.value.filter(s => s.enabled).length
    })

    // Filtered added services for search on dashboard
    const filteredAdded = computed(() => {
        if (!searchQuery.value) return addedServices.value
        const q = searchQuery.value.toLowerCase()
        return addedServices.value.filter(s =>
            s.name.toLowerCase().includes(q) ||
            s.description.toLowerCase().includes(q)
        )
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

    async function addService(serviceKey) {
        try {
            const res = await api.addService(serviceKey)
            if (res.success !== false) {
                const idx = services.value.findIndex(s => s.key === serviceKey)
                if (idx !== -1) {
                    services.value[idx].added = true
                    services.value[idx].position = Math.max(
                        ...services.value.filter(s => s.added).map(s => s.position), 0
                    ) + 1
                }
                // Open config panel for new service
                addDialogVisible.value = false
                const service = services.value.find(s => s.key === serviceKey)
                if (service) {
                    openPanel(service)
                }
            }
            return res
        } catch (e) {
            console.error('[scriptHub] Add service failed:', e)
        }
    }

    async function removeService(serviceKey, clearConfig = false) {
        try {
            const res = await api.removeService(serviceKey, clearConfig)
            if (res.success !== false) {
                const idx = services.value.findIndex(s => s.key === serviceKey)
                if (idx !== -1) {
                    services.value[idx].added = false
                    services.value[idx].enabled = false
                    if (clearConfig) {
                        services.value[idx].config = {}
                        services.value[idx].configured = false
                    }
                }
                closePanel()
            }
            return res
        } catch (e) {
            console.error('[scriptHub] Remove service failed:', e)
        }
    }

    async function sortServices(order) {
        try {
            // Update local positions immediately
            for (const item of order) {
                const idx = services.value.findIndex(s => s.key === item.key)
                if (idx !== -1) {
                    services.value[idx].position = item.position
                }
            }
            await api.sortServices(order)
        } catch (e) {
            console.error('[scriptHub] Sort failed:', e)
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
        addDialogVisible,
        searchQuery,
        categories,
        addedServices,
        availableServices,
        availableGrouped,
        enabledCount,
        filteredAdded,
        fetchAll,
        saveConfig,
        toggle,
        addService,
        removeService,
        sortServices,
        refreshAsset,
        openPanel,
        closePanel,
    }
})
