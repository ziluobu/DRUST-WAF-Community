import storage from '@/util/storage'

const state = {
    list: []
}

const getters = {}

const actions = {
    add({rootState, commit}, route) {
        return new Promise(resolve => {
            let names = []
            route.matched.map((v, i) => {
                if (i > 0) {
                    v.components.default.name && names.push(v.components.default.name)
                }
            })
            commit('add', {route: route, name: names})
            // 更新固定标签页的数据，数据会记录在 localstorage 里
            commit('updateStorage', rootState.user.account)
            // 增加标签页缓存
            commit('keepAlive/add', names, { root: true })
            resolve()
        })
    },
    remove({rootState, state, commit}, path) {
        let name
        state.list.map(v => {
            if (v.path == path) {
                name = v.name
            }
        })
        commit('remove', path)
        commit('updateStorage', rootState.user.account)
        name && commit('keepAlive/remove', name, { root: true })
    },
    removeOtherSide({rootState, state, commit}, path) {
        let names = []
        state.list.map(v => {
            if (v.path != path && !v.isPin) {
                names.push(v.name)
            }
        })
        commit('removeOtherSide', path)
        commit('updateStorage', rootState.user.account)
        names.map(v => {
            commit('keepAlive/remove', v, { root: true })
        })
    },
    removeLeftSide({rootState, state, commit}, path) {
        let index = ~~Object.keys(state.list).find(i => {
            return state.list[i].path == path
        })
        let names = []
        state.list.map((v, i) => {
            if (i < index && !v.isPin) {
                names.push(v.name)
            }
        })
        commit('removeLeftSide', path)
        commit('updateStorage', rootState.user.account)
        names.map(v => {
            commit('keepAlive/remove', v, { root: true })
        })
    },
    removeRightSide({rootState, commit}, path) {
        let index = ~~Object.keys(state.list).find(i => {
            return state.list[i].path == path
        })
        let names = []
        state.list.map((v, i) => {
            if (i > index && !v.isPin) {
                names.push(v.name)
            }
        })
        commit('removeRightSide', path)
        commit('updateStorage', rootState.user.account)
        names.map(v => {
            commit('keepAlive/remove', v, { root: true })
        })
    },
    pin({rootState, commit}, path) {
        commit('pin', path)
        commit('updateStorage', rootState.user.account)
    },
    unPin({rootState, commit}, path) {
        commit('unPin', path)
        commit('updateStorage', rootState.user.account)
    }
}

const mutations = {
    // 根据 localstorage 数据复原当前帐号的固定标签页
    recoveryStorage(state, account) {
        if (storage.local.get('tabbarPinData') != null) {
            state.list = JSON.parse(storage.local.get('tabbarPinData'))[account] || []
        }
    },
    // 更新 localstorage 数据
    updateStorage(state, account) {
        let data = JSON.parse(storage.local.get('tabbarPinData')) || {}
        data[account] = state.list.filter(item => {
            return item.isPin
        })
        storage.local.set('tabbarPinData', JSON.stringify(data))
    },
    // 清空所有标签页，登出的时候需要清空
    clean(state) {
        state.list = []
    },
    // 添加标签页
    add(state, data) {
        if (data.route.name != 'reload') {
            if (
                !state.list.some(item => {
                    return item.path == (data.route.meta.activeMenu || data.route.fullPath)
                })
            ) {
                state.list.push({
                    path: data.route.meta.activeMenu || data.route.fullPath,
                    title: data.route.meta.title,
                    i18n: data.route.meta.i18n,
                    name: data.name,
                    isPin: false
                })
            }
        }
    },
    // 删除指定标签页
    remove(state, path) {
        state.list = state.list.filter(item => {
            return item.path != path
        })
    },
    // 删除两侧非固定标签页
    removeOtherSide(state, path) {
        state.list = state.list.filter(item => {
            return item.path == path || item.isPin
        })
    },
    // 删除左侧非固定标签页
    removeLeftSide(state, path) {
        // 查找指定路由对应在标签页列表里的下标
        let index = ~~Object.keys(state.list).find(i => {
            return state.list[i].path == path
        })
        state.list = state.list.filter((item, i) => {
            return i >= index || item.isPin
        })
    },
    // 删除右侧非固定标签页
    removeRightSide(state, path) {
        // 查找指定路由对应在标签页列表里的下标
        let index = ~~Object.keys(state.list).find(i => {
            return state.list[i].path == path
        })
        state.list = state.list.filter((item, i) => {
            return i <= index || item.isPin
        })
    },
    // 固定标签页（移动到最后一个固定标签页后面，如果没有则移动至第一个）
    pin(state, path) {
        let index = ~~Object.keys(state.list).find(i => {
            return state.list[i].path == path
        })
        let toIndex = -1
        state.list.map((item, index) => {
            if (item.isPin) {
                toIndex = index
            }
        })
        state.list.splice(toIndex + 1, 0, state.list[index])
        state.list.splice(index + 1, 1)
        // 修改状态
        state.list.map(item => {
            if (item.path == path) {
                item.isPin = true
            }
        })
    },
    // 取消固定标签页（移动到最后一个固定标签页后面）
    unPin(state, path) {
        let index = Object.keys(state.list).find(i => {
            return state.list[i].path == path
        })
        index = ~~index
        let toIndex = -1
        state.list.map((item, index) => {
            if (item.isPin) {
                toIndex = index
            }
        })
        state.list.splice(toIndex + 1, 0, state.list[index])
        state.list.splice(index, 1)
        // 修改状态
        state.list.map(item => {
            if (item.path == path) {
                item.isPin = false
            }
        })
    },
    sort(state, data) {
        state.list = data
    }
}

export default {
    namespaced: true,
    state,
    actions,
    getters,
    mutations
}
