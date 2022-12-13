import Layout from '@/layout'
import EmptyLayout from '@/layout/empty'

export default {
    path: '/systemManage',
    component: Layout,
    redirect: '/systemManage/configModuleList',
    name: 'systemManage',
    meta: {
        title: '系统管理',
        icon: 'sidebar-breadcrumb'
    },
    children: [
        {
            path: 'configModuleList',
            name: 'configModuleList',
            component: () => import(/* webpackChunkName: 'systemManage' */ '@/views/system_manage/config_module/list'),
            meta: {
                title: '配置模块'
            }
        },
        // {
        //     path: 'configModuleAdd',
        //     name: 'configModuleAdd',
        //     component: () => import(/* webpackChunkName: 'systemManage' */ '@/views/system_manage/config_module/form'),
        //     meta: {
        //         title: '新增页面',
        //         sidebar: false,
        //         activeMenu: '/systemManage/configModuleList'
        //     }
        // },
        // {
        //     path: 'configModuleEdit/:id',
        //     name: 'configModuleEdit',
        //     component: () => import(/* webpackChunkName: 'systemManage' */ '@/views/system_manage/config_module/form'),
        //     meta: {
        //         title: '编辑页面',
        //         sidebar: false,
        //         activeMenu: '/systemManage/configModuleList'
        //     }
        // },
        //  管理员模块
        {
            path: 'adminModuleList',
            name: 'adminModuleList',
            component: EmptyLayout,
            redirect: '/systemManage/adminModuleList',
            meta: {
                title: '管理员模块'
            },
            children: [
                {
                    path: '',
                    component: () => import(/* webpackChunkName: 'systemManage' */ '@/views/system_manage/admin_module/list'),
                    meta: {
                        title: '管理员模块',
                        sidebar: false,
                        breadcrumb: false
                    }
                }
                // {
                //     path: 'adminModuleAdd',
                //     name: 'adminModuleAdd',
                //     component: () => import(/* webpackChunkName: 'systemManage' */ '@/views/system_manage/admin_module/form'),
                //     meta: {
                //         title: '新增页面',
                //         sidebar: false,
                //         activeMenu: '/systemManage/adminModuleList'
                //     }
                // },
                // {
                //     path: 'adminModuleEdit/:id',
                //     name: 'adminModuleEdit',
                //     component: () => import(/* webpackChunkName: 'systemManage' */ '@/views/system_manage/admin_module/form'),
                //     meta: {
                //         title: '编辑页面',
                //         sidebar: false,
                //         activeMenu: '/systemManage/adminModuleList'
                //     }
                // }
            ]
        }
    ]
}