import store from '@/store/index'
export function  hasPermission(permission) {
    console.log(permission, store.state, 'store.state111111')
}
export default {
    hasPermission
}