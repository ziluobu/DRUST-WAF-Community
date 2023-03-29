import Vue from 'vue'
import App from './App.vue'
import router from './router/index'
import store from './store/index'
import i18n from './lang'

import api from './api'
import common from './common'
import dataV, {borderBox1} from '@jiaminghi/data-view'
import dayjs from 'dayjs'
import auth from './util/auth'

import tabbar from './util/tabbar'
import cookies from 'vue-cookies'
import VueMeta from 'vue-meta'
import ElementUI from 'element-ui'
import 'element-ui/lib/theme-chalk/index.css'
import hotkeys from 'hotkeys-js'
import Contextmenu from 'vue-contextmenujs'
import 'echarts/map/js/world.js'
import 'echarts/map/js/china.js'
import 'echarts/extension-src/bmap/bmap.js'
import { parseTime, resetForm, addDateRange, selectDictLabel, selectDictLabels, handleTree } from '@/util/funcitons'
// 全局组件自动注册
import './components/autoRegister'
import 'remixicon/fonts/remixicon.css'

import './assets/styles/reset.scss'

// 错误日志
import './util/error.log'

import './mock'
import Utils from './util/index'
Vue.prototype.utils = Utils
Vue.prototype.$api = api
Vue.prototype.$common = common
Vue.prototype.parseTime = parseTime
Vue.prototype.resetForm = resetForm
Vue.prototype.addDateRange = addDateRange
Vue.prototype.selectDictLabel = selectDictLabel
Vue.prototype.selectDictLabels = selectDictLabels
Vue.prototype.handleTree = handleTree
Vue.use(dataV)

Vue.use(borderBox1)

Vue.prototype.$dayjs = dayjs

Vue.use(auth)

Vue.use(tabbar)

Vue.use(cookies)

Vue.use(VueMeta)

Vue.prototype.$ELEMENT = ElementUI
Vue.use(ElementUI, {
    size: store.state.settings.elementSize,
    i18n: (key, value) => i18n.t(key, value)
})

Vue.prototype.$hotkeys = hotkeys

Vue.use(Contextmenu)

// 引入echarts
import * as echarts from 'echarts'
Vue.prototype.$echarts = echarts

// 自动加载 svg 图标
const req = require.context('./assets/icons', false, /\.svg$/)
const requireAll = requireContext => requireContext.keys().map(requireContext)
requireAll(req)

Vue.config.productionTip = false

Vue.prototype.$eventBus = new Vue({
    router,
    store,
    i18n,
    render: h => h(App)
}).$mount('#app')
