/**
 * 存放全局公用状态
 */
import settings from '@/settings'

const state = {
    ...settings,
    // 侧边栏是否收起（用于记录 pc 模式下最后的状态）
    sidebarCollapseLastStatus: settings.sidebarCollapse,
    // 显示模式，支持：mobile、pc
    mode: 'pc',
    // 页面标题
    title: '',
    // 大屏打开
    enableLargeScreen: false
}

const getters = {}

const actions = {}

const mutations = {
    // 设置访问模式，页面宽度小于 992px 时切换为移动端展示
    setMode(state, width) {
        if (state.enableMobileAdaptation && width < 992) {
            state.mode = 'mobile'
        } else {
            state.mode = 'pc'
        }
    },
    // 设置网页标题
    setTitle(state, title) {
        state.title = title
    },
    // 设置大屏打开状态
    enableLargeScreen(state, enableLargeScreen) {
        state.enableLargeScreen = enableLargeScreen
    },
    // 切换侧边栏导航展开/收起
    toggleSidebarCollapse(state) {
        state.sidebarCollapse = !state.sidebarCollapse
        if (state.mode == 'pc') {
            state.sidebarCollapseLastStatus = !state.sidebarCollapseLastStatus
        }
    },
    setDefaultLang(state, lang) {
        state.defaultLang = lang
    },
    // 更新主题配置
    updateThemeSetting(state, data) {
        Object.assign(state, data)
    }
}

export default {
    namespaced: true,
    state,
    actions,
    getters,
    mutations
}
