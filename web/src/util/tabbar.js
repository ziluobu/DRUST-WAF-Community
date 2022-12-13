import router from '@/router/index'
import store from '@/store/index'

export default {
    install(Vue) {
        Vue.prototype.$tabbarClose = function(path = router.currentRoute.meta.activeMenu || router.currentRoute.fullPath) {
            let activedTabPath = router.currentRoute.meta.activeMenu || router.currentRoute.fullPath
            // 如果关闭的标签正好是当前路由，并且标签栏数目大于 1
            if (path == activedTabPath && store.state.tabbar.list.length > 1) {
                let index = ~~Object.keys(store.state.tabbar.list).find(i => {
                    return store.state.tabbar.list[i].path == path
                })
                if (index < store.state.tabbar.list.length - 1) {
                    this.$router.push(store.state.tabbar.list[index + 1].path)
                } else {
                    this.$router.push(store.state.tabbar.list[index - 1].path)
                }
            }
            store.dispatch('tabbar/remove', path)
        }
    }
}
