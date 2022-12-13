import settings from '@/settings'

const storage = {}

storage.local = {
    has: key => {
        return !!localStorage.getItem(`${settings.storagePrefix}${key}`)
    },
    get: key => {
        return localStorage.getItem(`${settings.storagePrefix}${key}`)
    },
    set: (key, value) => {
        localStorage.setItem(`${settings.storagePrefix}${key}`, value)
    },
    remove: key => {
        localStorage.removeItem(`${settings.storagePrefix}${key}`)
    },
    clear: () => {
        localStorage.clear()
    }
}

storage.session = {
    has: key => {
        return !!sessionStorage.getItem(`${settings.storagePrefix}${key}`)
    },
    get: key => {
        return sessionStorage.getItem(`${settings.storagePrefix}${key}`)
    },
    set: (key, value) => {
        sessionStorage.setItem(`${settings.storagePrefix}${key}`, value)
    },
    remove: key => {
        sessionStorage.removeItem(`${settings.storagePrefix}${key}`)
    },
    clear: () => {
        sessionStorage.clear()
    }
}

export default storage
