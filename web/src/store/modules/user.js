import storage from '@/util/storage'
import api from '@/api'

const state = {
    account: storage.local.get('account') || '',
    token: storage.local.get('token') || '',
    failure_time: storage.local.get('failure_time') || '',
    permissions: []
}

const getters = {
    isLogin: state => {
        let retn = false
        if (state.token) {
            let unix = Date.parse(new Date())
            if (unix < state.failure_time * 1000) {
                retn = true
            }
        }
        return retn
    }
}

const actions = {
    login({commit}, data) {
        return new Promise((resolve, reject) => {
            // 通过 mock 进行登录
            api.post('mock/member/login', data).then(res => {
                // console.log(res, 'ddd')
                commit('setUserData', res.data)
                resolve()
            }).catch(error => {
                reject(error)
            })
        })
    },
    logout({commit}) {
        commit('removeUserData')
        commit('menu/invalidRoutes', null, {root: true})
        commit('tabbar/clean', null, {root: true})
    },
    // 获取我的权限
    getPermissions({state, commit}) {
        return new Promise(resolve => {
            // 通过 mock 获取权限
            api.get('mock/member/permission', {
                params: {
                    account: state.account
                }
            }).then(res => {
                commit('setPermissions', res.data.permissions)
                resolve(res.data.permissions)
            })
        })
    }
}

const mutations = {
    setUserData(state, data) {
        // console.log(data)
        storage.local.set('account', data.account)
        // storage.local.set('token', data.token)
        storage.local.set('failure_time', data.failure_time)
        state.account = data.account
        state.token = storage.local.get('token')
        // state.token = data.token
        state.failure_time = data.failure_time
    },
    removeUserData(state) {
        storage.local.remove('account')
        storage.local.remove('token')
        storage.local.remove('failure_time')
        state.account = ''
        state.token = ''
        state.failure_time = ''
    },
    setPermissions(state, permissions) {
        state.permissions = permissions
    }
}

export default {
    namespaced: true,
    state,
    actions,
    getters,
    mutations
}
