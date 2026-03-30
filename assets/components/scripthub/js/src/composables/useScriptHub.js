export function useScriptHub() {
    function getToken() {
        return window.ScriptHub?.config?.siteId || window.MODx?.siteId || ''
    }

    function buildUrl(action, params = {}) {
        const url = new URL(
            window.ScriptHub?.config?.connectorUrl || '',
            window.location.origin
        )
        url.searchParams.set('action', action)
        url.searchParams.set('HTTP_MODAUTH', getToken())
        for (const [k, v] of Object.entries(params)) {
            if (v != null) url.searchParams.set(k, typeof v === 'object' ? JSON.stringify(v) : String(v))
        }
        return url.toString()
    }

    async function request(action, params = {}, method = 'GET') {
        const opts = { method, headers: { Accept: 'application/json' }, credentials: 'same-origin' }

        let url
        if (method === 'GET') {
            url = buildUrl(action, params)
        } else {
            url = buildUrl(action)
            const fd = new FormData()
            for (const [k, v] of Object.entries(params)) {
                if (v != null) fd.append(k, typeof v === 'object' ? JSON.stringify(v) : String(v))
            }
            opts.body = fd
        }

        const res = await fetch(url, opts)
        return res.json()
    }

    const NS = 'RenderRoom\\ScriptHub\\Processors\\Service\\'

    return {
        getServices: () => request(NS + 'GetList'),
        getService: (key) => request(NS + 'Get', { service_key: key }),
        updateService: (key, config) => request(NS + 'Update', { service_key: key, config }, 'POST'),
        toggleService: (key, enabled) => request(NS + 'Toggle', { service_key: key, enabled: enabled ? 1 : 0 }, 'POST'),
        addService: (key) => request(NS + 'Add', { service_key: key }, 'POST'),
        removeService: (key, clearConfig) => request(NS + 'Remove', { service_key: key, clear_config: clearConfig ? 1 : 0 }, 'POST'),
        sortServices: (order) => request(NS + 'Sort', { order }, 'POST'),
        refreshAsset: (key) => request(NS + 'RefreshAsset', { service_key: key }, 'POST'),
    }
}
