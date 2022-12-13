import { deepClone } from '@/util'
import api from '@/api'
function hasPermission(permissions, route) {
    let isAuth = false
    if (route.meta && route.meta.auth) {
        isAuth = permissions.some(auth => {
            if (typeof route.meta.auth == 'string') {
                return route.meta.auth === auth
            } else {
                return route.meta.auth.some(routeAuth => {
                    return routeAuth === auth
                })
            }
        })
    } else {
        isAuth = true
    }
    return isAuth
}

function filterAsyncRoutes(routes, permissions) {
    const res = []
    routes.forEach(route => {
        const tmp = { ...route }
        if (hasPermission(permissions, tmp)) {
            if (tmp.children) {
                tmp.children = filterAsyncRoutes(tmp.children, permissions)
                tmp.children.length && res.push(tmp)
            } else {
                res.push(tmp)
            }
        }
    })
    return res
}

// 将多层嵌套路由处理成平级
function flatAsyncRoutes(routes, breadcrumb, baseUrl = '') {
    let res = []
    routes.forEach(route => {
        const tmp = { ...route }
        if (tmp.children) {
            let childrenBaseUrl = ''
            if (baseUrl == '') {
                childrenBaseUrl = tmp.path
            } else if (tmp.path != '') {
                childrenBaseUrl = `${baseUrl}/${tmp.path}`
            }
            let childrenBreadcrumb = deepClone(breadcrumb)
            if (route.meta.breadcrumb !== false) {
                childrenBreadcrumb.push({
                    path: childrenBaseUrl,
                    title: route.meta.title,
                    i18n: route.meta.i18n
                })
            }
            let tmpRoute = deepClone(route)
            tmpRoute.path = childrenBaseUrl
            tmpRoute.meta.breadcrumbNeste = childrenBreadcrumb
            delete tmpRoute.children
            res.push(tmpRoute)
            let childrenRoutes = flatAsyncRoutes(tmp.children, childrenBreadcrumb, childrenBaseUrl)
            childrenRoutes.map(item => {
                // 如果 path 一样则覆盖，因为子路由的 path 可能设置为空，导致和父路由一样，直接注册会提示路由重复
                if (res.some(v => v.path == item.path)) {
                    res.forEach((v, i) => {
                        if (v.path == item.path) {
                            res[i] = item
                        }
                    })
                } else {
                    res.push(item)
                }
            })
        } else {
            if (baseUrl != '') {
                if (tmp.path != '') {
                    tmp.path = `${baseUrl}/${tmp.path}`
                } else {
                    tmp.path = baseUrl
                }
            }
            // 处理面包屑导航
            let tmpBreadcrumb = deepClone(breadcrumb)
            if (tmp.meta.breadcrumb !== false) {
                tmpBreadcrumb.push({
                    path: tmp.path,
                    title: tmp.meta.title,
                    i18n: tmp.meta.i18n
                })
            }
            tmp.meta.breadcrumbNeste = tmpBreadcrumb
            res.push(tmp)
        }
    })
    return res
}

const state = {
    isGenerate: false,
    routes: [],
    headerActived: 0
}

const getters = {
    sidebarRoutes: state => {
        return state.routes.length > 0 ? state.routes[state.headerActived].children : []
    }
}

const actions = {
    // 根据权限动态生成路由
    generateRoutes({ rootState, dispatch, commit }, data) {
        // eslint-disable-next-line no-async-promise-executor
        return new Promise(async resolve => {
            let accessedRoutes
            // 判断权限功能是否开启
            if (rootState.settings.openPermission) {
                const permissions = await dispatch('user/getPermissions', null, { root: true })
                accessedRoutes = filterAsyncRoutes(data.asyncRoutes, permissions)
            } else {
                accessedRoutes = data.asyncRoutes
            }
            // console.log(accessedRoutes, data.currentPath, 'accessedRoutes')
            commit('setRoutes', accessedRoutes)
            commit('setHeaderActived', data.currentPath)
            let routes = []
            accessedRoutes.map(item => {
                routes.push(...item.children)
            })
            if (rootState.settings.enableFlatRoutes) {
                routes.map(item => {
                    if (item.children) {
                        item.children = flatAsyncRoutes(item.children, [{
                            path: item.path,
                            title: item.meta.title,
                            i18n: item.meta.i18n
                        }])
                    }
                })
            }
            resolve(routes)
        })
    },
    // 请求角色路由
    roleMenu({ commit }, data) {
        return new Promise(resolve => {

            api.post('api/getRouters', data).then(async res => {
                // const obj = JSON.parse(res.data.routes)
                if (res.data) {
                    commit('SET_PERMISSIONS', res.data.permissions)
                    let response = JSON.parse(JSON.stringify(res.data.routes))
                    response.forEach(item => {
                        // item.component = {
                        //     name: 'layout'
                        // }
                        item.children.forEach(items => {
                            items.component = 'layout'
                            items.children.forEach(itemed => {
                                if (itemed.children.length === 0) {
                                    delete itemed.children
                                } else {
                                    itemed.children.forEach(v => {
                                        if (v.children.length === 0) {
                                            delete v.children
                                        } else {
                                            v.children.forEach(e => {
                                                // e.redirect  = e.component
                                                if (e.children.length === 0) {
                                                    delete e.children
                                                }
                                            })
                                        }
                                    })
                                }

                            })
                        })
                    })
                    // console.log(response)
                    const data = eachMenu(response)
                    resolve(data, commit)
                }

            })
        })
    }
}

const eachMenu = async data => {
    data.forEach(item => {
        if (item.children) {
            eachMenu(item.children)
        }
        if (item.component) {
            if (item.component && item.component === 'layout') {
                item.component = () => import(/* layout */ '@/layout/index.vue')
            } else {
                const path = item.component
                const arr = ['/c', '/v']
                if (!arr.includes(path)) {
                    if (path) {
                        item.component = resolve => require([`@/views${path}`], resolve)
                    }
                }

            }

        }
        // if (item.children) {
        //     item.children.forEach(iteed => {
        //         if (iteed.children) {
        //             if (iteed.children) {
        //                 eachMenu(iteed.children)
        //             }
        //             if (iteed.component) {
        //                 if (iteed.component && iteed.component === 'layout') {
        //                     iteed.component = () => import(/* layout */ '@/layout/index.vue')
        //                 } else {
        //                     const path = iteed.component
        //                     console.log(path)
        //                     const arr = ['/c', '/v']
        //                     if (!arr.includes(path)) {
        //                         if (path) {
        //                             console.log(path)
        //                             iteed.component = resolve => require([`@/views${path}`], resolve)
        //                         }
        //                     }

        //                 }

        //             }
        //         }
        //     })
        // }

    })
    return data
}
const mutations = {
    invalidRoutes(state) {
        state.isGenerate = false
        state.headerActived = 0
    },
    setRoutes(state, routes) {
        state.isGenerate = true
        let newRoutes = deepClone(routes)
        state.routes = newRoutes.filter(item => {
            return item.children.length != 0
        })

    },
    // 根据路由判断属于哪个头部导航
    setHeaderActived(state, path) {

        state.routes.map(item => {
            item.children.map((itemed, inds) => {
                if (
                    itemed.children.some(r => {
                        return path.indexOf(r.path + '/') === 0 || path == r.path
                    })
                ) {
                    state.headerActived = inds
                }
                // if (itemed.children) {
                //     itemed.children.map((v,i) =>{

                //     })
                // }

            })

        })
    },
    // 切换头部导航
    switchHeaderActived(state, index) {
        state.headerActived = index
    },

    // 权限控制
    SET_PERMISSIONS: (state, permissions) => {
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
