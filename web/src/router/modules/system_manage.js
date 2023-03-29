import Layout from '@/layout'
import EmptyLayout from '@/layout/empty'

export default {
    path: '/systemManage',
    component: Layout,
    redirect: '/systemManage/configModuleList',
    name: 'systemManage',
    meta: {
        title: '系统管理',
        icon: 'el-icon-setting'
    },
    children: [
        //  配置模块
        {
            path: 'configModuleList',
            name: 'configModuleList',
            component: EmptyLayout,
            redirect: '/systemManage/configModuleList',
            meta: {
                title: '配置模块'
            },
            children: [
                {
                    path: '',
                    component: () => import(/* webpackChunkName: 'systemManage' */ '@/views/system_manage/config_module/list'),
                    meta: {
                        title: '配置模块',
                        sidebar: false,
                        breadcrumb: false
                    }
                }
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
                // {
                //     path: 'configModuleDetail/:id',
                //     name: 'configModuleDetail',
                //     component: () => import(/* webpackChunkName: 'systemManage' */ '@/views/system_manage/config_module/detail'),
                //     meta: {
                //         title: '详情页面',
                //         sidebar: false,
                //         activeMenu: '/systemManage/configModuleList'
                //     }
                // }
            ]
        },
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
                // {
                //     path: '',
                //     component: () => import(/* webpackChunkName: 'systemManage' */ '@/views/system_manage/admin_module/list'),
                //     meta: {
                //         title: '管理员模块',
                //         sidebar: false,
                //         breadcrumb: false
                //     }
                // },
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
                // },
                // {
                //     path: 'adminModuleDetail/:id',
                //     name: 'adminModuleDetail',
                //     component: () => import(/* webpackChunkName: 'systemManage' */ '@/views/system_manage/admin_module/detail'),
                //     meta: {
                //         title: '详情页面',
                //         sidebar: false,
                //         activeMenu: '/systemManage/adminModuleList'
                //     }
                // }
            ]
        },
        // 角色模块
        {
            path: 'roleModuleList',
            name: 'roleModuleList',
            component: EmptyLayout,
            redirect: '/systemManage/roleModuleList',
            meta: {
                title: '角色模块'
            },
            children: [
                {
                    path: '',
                    component: () => import(/* webpackChunkName: 'systemManage' */ '@/views/system_manage/role_module/list'),
                    meta: {
                        title: '角色模块',
                        sidebar: false,
                        breadcrumb: false
                    }
                }
                // {
                //     path: 'roleModuleAdd',
                //     name: 'roleModuleAdd',
                //     component: () => import(/* webpackChunkName: 'systemManage' */ '@/views/system_manage/role_module/form'),
                //     meta: {
                //         title: '新增页面',
                //         sidebar: false,
                //         activeMenu: '/systemManage/roleModuleList'
                //     }
                // },
                // {
                //     path: 'roleModuleEdit/:id',
                //     name: 'roleModuleEdit',
                //     component: () => import(/* webpackChunkName: 'systemManage' */ '@/views/system_manage/role_module/form'),
                //     meta: {
                //         title: '编辑页面',
                //         sidebar: false,
                //         activeMenu: '/systemManage/roleModuleList'
                //     }
                // },
                // {
                //     path: 'roleModuleDetail/:id',
                //     name: 'roleModuleDetail',
                //     component: () => import(/* webpackChunkName: 'systemManage' */ '@/views/system_manage/role_module/detail'),
                //     meta: {
                //         title: '详情页面',
                //         sidebar: false,
                //         activeMenu: '/systemManage/roleModuleList'
                //     }
                // }
            ]
        },
        //  菜单模块
        {
            path: 'menuModuleList',
            name: 'menuModuleList',
            component: EmptyLayout,
            redirect: '/systemManage/menuModuleList',
            meta: {
                title: '菜单模块'
            },
            children: [
                {
                    path: '',
                    component: () => import(/* webpackChunkName: 'systemManage' */ '@/views/system_manage/menu_module/list'),
                    meta: {
                        title: '菜单模块',
                        sidebar: false,
                        breadcrumb: false
                    }
                }
                // {
                //     path: 'menuModuleAdd',
                //     name: 'menuModuleAdd',
                //     component: () => import(/* webpackChunkName: 'systemManage' */ '@/views/system_manage/menu_module/form'),
                //     meta: {
                //         title: '新增页面',
                //         sidebar: false,
                //         activeMenu: '/systemManage/menuModuleList'
                //     }
                // },
                // {
                //     path: 'menuModuleEdit/:id',
                //     name: 'menuModuleEdit',
                //     component: () => import(/* webpackChunkName: 'systemManage' */ '@/views/system_manage/menu_module/form'),
                //     meta: {
                //         title: '编辑页面',
                //         sidebar: false,
                //         activeMenu: '/systemManage/menuModuleList'
                //     }
                // },
                // {
                //     path: 'menuModuleDetail/:id',
                //     name: 'menuModuleDetail',
                //     component: () => import(/* webpackChunkName: 'systemManage' */ '@/views/system_manage/menu_module/detail'),
                //     meta: {
                //         title: '详情页面',
                //         sidebar: false,
                //         activeMenu: '/systemManage/menuModuleList'
                //     }
                // }
            ]
        },
        //  日志模块
        {
            path: 'logModule',
            name: 'logModule',
            component: EmptyLayout,
            redirect: '/systemManage/loginLog',
            meta: {
                title: '日志模块'
            },
            children: [
                {
                    path: 'loginLog',
                    name: 'loginLog',
                    component: () => import(/* webpackChunkName: 'systemManage' */ '@/views/system_manage/log_module/login_log'),
                    meta: {
                        title: '登录日志'
                    }
                },
                {
                    path: 'operaLog',
                    name: 'operaLog',
                    component: () => import(/* webpackChunkName: 'systemManage' */ '@/views/system_manage/log_module/opera_log'),
                    meta: {
                        title: '操作日志'
                    }
                }
            ]
        }
    ]
}