import Vue from 'vue'
import store from '@/store/index'

if (process.env.NODE_ENV != 'development' && store.state.settings.enableErrorLog) {
    // 由于 errorHandler 拦截错误，如果不进行手动输出，在开发者控制台里将看不到任何错误提示
    // 所以默认在开发环境强制关闭该功能
    Vue.config.errorHandler = function(err, vm, info) {
        // 在此处编写错误上报代码
        // 以下代码为演示代码
        let log = {
            url: location.href,
            err: {
                message: err.message,
                stack: err.stack
            },
            info,
            datetime: vm.$dayjs().format('YYYY-MM-DD HH:mm:ss')
        }
        log = JSON.stringify(log)
        sessionStorage.setItem('errorLog', log)
    }
}
