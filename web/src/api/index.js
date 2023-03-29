import axios from 'axios'
// import Qs from 'qs'
import router from '@/router/index'
import store from '@/store/index'
import { Message } from 'element-ui'
// import  moment from 'moment'
const toLogin = () => {
    store.dispatch('user/logout')
    router.push({
        path: '/login',
        query: {
            redirect: router.currentRoute.fullPath
        }
    })
}

const api = axios.create({
    baseURL: process.env.VUE_APP_API_ROOT,
    timeout: 10000,
    responseType: 'json'
    // withCredentials: true
})
// var time = new Date()
var deteTime = Date.parse(new Date()) / 1000
api.interceptors.request.use(
    request => {

        if (request.method == 'post') {
            // request.params  = {sign: 'werqew', timestamp: store.state.user.failure_time}
            request.headers.common['token'] = store.state.user.token

            if (request.data instanceof FormData) {
                if (store.getters['user/isLogin']) {
                    // 如果是 FormData 类型（上传图片）
                    // request.data.append('token', store.state.user.token)
                }
            } else {
                // 带上 token
                if (request.data == undefined) {
                    request.data = {}
                }
                if (store.getters['user/isLogin']) {
                    // request.data.token = store.state.user.token
                    request.data.timestamp = deteTime
                    // request.data.sign = 'werqew'
                }
                // request.data = Qs.stringify(request.data)

            }

        } else {

            request.headers.common['token'] = store.state.user.token

            // 带上 token
            // console.log(request.params, 'ee')

            if (request.params == undefined) {
                request.params = {}
            }
            if (store.getters['user/isLogin']) {
                // request.params.token = store.state.user.token
                request.params.timestamp = deteTime
                // request.params.sign = 'werqew'

            }

        }
        return request
    }
)

api.interceptors.response.use(
    response => {
        // if(response.config.url == 'mock/member/login') {

        // }
        // if (response.data.error != '') {
        // if (response.data.code != 2000 && response.config.url != 'mock/member/login') {    //  自己修改
        //     // 如果接口请求时发现 token 失效，则立马跳转到登录页
        //     // if (response.data.status == 0) {
        //     if (response.data.code == 4100 || response.data.code == 4102) {    //  自己修改
        //         toLogin()
        //     }
        //     Message.error(response.data.msg + '!')
        //     return Promise.reject(response.data)
        // }
        if (response.data.code != 2000 && response.data.error != '') {    //  自己修改
            // 如果接口请求时发现 token 失效，则立马跳转到登录页
            // if (response.data.status == 0) {
            if (response.data.code == 4100 || response.data.code == 4101 || response.data.code == 4102 || response.data.code == 4103 || response.data.status == 0) {    //  自己修改
                toLogin()
            }
            Message.error(response.data.msg + '!')
            return Promise.reject(response.data)
        }
        return Promise.resolve(response.data)
    },
    error => {
        return Promise.reject(error)
    }
)

// api.interceptors.response.use(
//     response => {
//         if (response.data.error != '') {
//             // 如果接口请求时发现 token 失效，则立马跳转到登录页
//             if (response.data.status == 0) {
//                 toLogin()
//             }
//             Message.error(response.data.error)
//             return Promise.reject(response.data)
//         }
//         return Promise.resolve(response.data)
//     },
//     error => {
//         return Promise.reject(error)
//     }
// )

export default api
