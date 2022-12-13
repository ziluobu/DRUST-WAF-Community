import Layout from '@/layout'
import EmptyLayout from '@/layout/empty'

export default {
    path: '/safeSoperation',
    component: Layout,
    redirect: '/safeSoperation/whiteList',
    name: 'safeSoperation',
    meta: {
        title: '安全运营',
        icon: 'slide-strate'
    },
    children: [
        //  访问控制
        {
            path: 'accessControl',
            name: 'accessControl',
            component: EmptyLayout,
            redirect: '/safeSoperation/whiteList',
            meta: {
                title: '访问控制'
            },
            children: [
                {
                    path: 'whiteList',
                    name: 'whiteList',
                    component: EmptyLayout,
                    redirect: '/safeSoperation/whiteList',
                    meta: {
                        title: '白名单'
                    },
                    children: [
                        {
                            path: '',
                            component: () => import(/* webpackChunkName: 'safeSoperation' */ '@/views/safe_soperation/access_control/white_list/list'),
                            meta: {
                                title: '白名单',
                                sidebar: false,
                                breadcrumb: false
                            }
                        },
                        {
                            path: 'whiteListAdd',
                            name: 'whiteListAdd',
                            component: () => import(/* webpackChunkName: 'safeSoperation' */ '@/views/safe_soperation/access_control/white_list/form'),
                            meta: {
                                title: '新增页面',
                                sidebar: false,
                                activeMenu: '/safeSoperation/accessControl/whiteList'
                            }
                        },
                        {
                            path: 'whiteListEdit/:id',
                            name: 'whiteListEdit',
                            component: () => import(/* webpackChunkName: 'safeSoperation' */ '@/views/safe_soperation/access_control/white_list/form'),
                            meta: {
                                title: '编辑页面',
                                sidebar: false,
                                activeMenu: '/safeSoperation/accessControl/whiteList'
                            }
                        },
                        {
                            path: 'whiteListDetail/:id',
                            name: 'whiteListDetail',
                            component: () => import(/* webpackChunkName: 'safeSoperation' */ '@/views/safe_soperation/access_control/white_list/detail'),
                            meta: {
                                title: '详情页面',
                                sidebar: false,
                                activeMenu: '/safeSoperation/accessControl/whiteList'
                            }
                        }
                    ]
                },
                {
                    path: 'blackList',
                    name: 'blackList',
                    component: EmptyLayout,
                    redirect: '/safeSoperation/blackList',
                    meta: {
                        title: '黑名单'
                    },
                    children: [
                        {
                            path: '',
                            component: () => import(/* webpackChunkName: 'safeSoperation' */ '@/views/safe_soperation/access_control/black_list/list'),
                            meta: {
                                title: '黑名单',
                                sidebar: false,
                                breadcrumb: false
                            }
                        },
                        {
                            path: 'blackListAdd',
                            name: 'blackListAdd',
                            component: () => import(/* webpackChunkName: 'safeSoperation' */ '@/views/safe_soperation/access_control/black_list/form'),
                            meta: {
                                title: '新增页面',
                                sidebar: false,
                                activeMenu: '/safeSoperation/accessControl/blackList'
                            }
                        },
                        {
                            path: 'blackListEdit/:id',
                            name: 'blackListEdit',
                            component: () => import(/* webpackChunkName: 'safeSoperation' */ '@/views/safe_soperation/access_control/black_list/form'),
                            meta: {
                                title: '编辑页面',
                                sidebar: false,
                                activeMenu: '/safeSoperation/accessControl/blackList'
                            }
                        },
                        {
                            path: 'blackListDetail/:id',
                            name: 'blackListDetail',
                            component: () => import(/* webpackChunkName: 'safeSoperation' */ '@/views/safe_soperation/access_control/black_list/detail'),
                            meta: {
                                title: '详情页面',
                                sidebar: false,
                                activeMenu: '/safeSoperation/accessControl/blackList'
                            }
                        }
                    ]
                }
            ]
        },
        //  防护策略
        {
            path: 'protectStrate',
            name: 'protectStrate',
            component: EmptyLayout,
            redirect: '/safeSoperation/policyClassify',
            meta: {
                title: '防护策略'
            },
            children: [
                {
                    path: 'policyClassify',
                    name: 'policyClassify',
                    component: EmptyLayout,
                    redirect: '/safeSoperation/policyClassify',
                    meta: {
                        title: '策略分类'
                    },
                    children: [
                        {
                            path: '',
                            component: () => import(/* webpackChunkName: 'safeSoperation' */ '@/views/safe_soperation/protect_strate/policy_classify/list'),
                            meta: {
                                title: '策略分类',
                                sidebar: false,
                                breadcrumb: false
                            }
                        },
                        {
                            path: 'wpolicyClassifyAdd',
                            name: 'policyClassifyAdd',
                            component: () => import(/* webpackChunkName: 'safeSoperation' */ '@/views/safe_soperation/protect_strate/policy_classify/form'),
                            meta: {
                                title: '新增页面',
                                sidebar: false,
                                activeMenu: '/safeSoperation/protectStrate/policyClassify'
                            }
                        },
                        {
                            path: 'policyClassifyEdit/:id',
                            name: 'policyClassifyEdit',
                            component: () => import(/* webpackChunkName: 'safeSoperation' */ '@/views/safe_soperation/protect_strate/policy_classify/form'),
                            meta: {
                                title: '编辑页面',
                                sidebar: false,
                                activeMenu: '/safeSoperation/protectStrate/policyClassify'
                            }
                        }
                    ]
                },
                {
                    path: 'webProtect',
                    name: 'webProtect',
                    component: EmptyLayout,
                    redirect: '/safeSoperation/webProtect',
                    meta: {
                        title: '网站防护策略'
                    },
                    children: [
                        {
                            path: '',
                            component: () => import(/* webpackChunkName: 'safeSoperation' */ '@/views/safe_soperation/protect_strate/web_protect/list'),
                            meta: {
                                title: '网站防护策略',
                                sidebar: false,
                                breadcrumb: false
                            }
                        },
                        {
                            path: 'webProtectAdd',
                            name: 'webProtectAdd',
                            component: () => import(/* webpackChunkName: 'safeSoperation' */ '@/views/safe_soperation/protect_strate/web_protect/form'),
                            meta: {
                                title: '新增页面',
                                sidebar: false,
                                activeMenu: '/safeSoperation/protectStrate/webProtect'
                            }
                        },
                        {
                            path: 'webProtectEdit/:id',
                            name: 'webProtectEdit',
                            component: () => import(/* webpackChunkName: 'safeSoperation' */ '@/views/safe_soperation/protect_strate/web_protect/form'),
                            meta: {
                                title: '编辑页面',
                                sidebar: false,
                                activeMenu: '/safeSoperation/protectStrate/webProtect'
                            }
                        },
                        {
                            path: 'webProtectDetail/:id',
                            name: 'webProtectDetail',
                            component: () => import(/* webpackChunkName: 'safeSoperation' */ '@/views/safe_soperation/protect_strate/web_protect/detail'),
                            meta: {
                                title: '详情页面',
                                sidebar: false,
                                activeMenu: '/safeSoperation/protectStrate/webProtect'
                            }
                        }
                    ]
                },
                {
                    path: 'strateGo',
                    name: 'strateGo',
                    component: EmptyLayout,
                    redirect: '/safeSoperation/strateGo',
                    meta: {
                        title: '策略放过'
                    },
                    children: [
                        {
                            path: '',
                            component: () => import(/* webpackChunkName: 'safeSoperation' */ '@/views/safe_soperation/protect_strate/strate_go/list'),
                            meta: {
                                title: '策略放过',
                                sidebar: false,
                                breadcrumb: false
                            }
                        },
                        {
                            path: 'strateGoAdd',
                            name: 'strateGoAdd',
                            component: () => import(/* webpackChunkName: 'safeSoperation' */ '@/views/safe_soperation/protect_strate/strate_go/form'),
                            meta: {
                                title: '新增页面',
                                sidebar: false,
                                activeMenu: '/safeSoperation/protectStrate/strateGo'
                            }
                        },
                        {
                            path: 'strateGoEdit/:id',
                            name: 'strateGoEdit',
                            component: () => import(/* webpackChunkName: 'safeSoperation' */ '@/views/safe_soperation/protect_strate/strate_go/form'),
                            meta: {
                                title: '编辑页面',
                                sidebar: false,
                                activeMenu: '/safeSoperation/protectStrate/strateGo'
                            }
                        },
                        {
                            path: 'strateGoDetail/:id',
                            name: 'strateGoDetail',
                            component: () => import(/* webpackChunkName: 'safeSoperation' */ '@/views/safe_soperation/protect_strate/strate_go/detail'),
                            meta: {
                                title: '详情页面',
                                sidebar: false,
                                activeMenu: '/safeSoperation/protectStrate/strateGo'
                            }
                        }
                    ]
                },
                {
                    path: 'globalStrate',
                    name: 'globalStrate',
                    component: EmptyLayout,
                    redirect: '/safeSoperation/globalStrate',
                    meta: {
                        title: '全局防护策略'
                    },
                    children: [
                        {
                            path: '',
                            component: () => import(/* webpackChunkName: 'safeSoperation' */ '@/views/safe_soperation/protect_strate/global_strate/list'),
                            meta: {
                                title: '全局防护策略',
                                sidebar: false,
                                breadcrumb: false
                            }
                        },
                        {
                            path: 'globalStrateAdd',
                            name: 'globalStrateAdd',
                            component: () => import(/* webpackChunkName: 'safeSoperation' */ '@/views/safe_soperation/protect_strate/global_strate/form'),
                            meta: {
                                title: '新增页面',
                                sidebar: false,
                                activeMenu: '/safeSoperation/protectStrate/globalStrate'
                            }
                        },
                        {
                            path: 'globalStrateEdit/:id',
                            name: 'globalStrateEdit',
                            component: () => import(/* webpackChunkName: 'safeSoperation' */ '@/views/safe_soperation/protect_strate/global_strate/form'),
                            meta: {
                                title: '编辑页面',
                                sidebar: false,
                                activeMenu: '/safeSoperation/protectStrate/globalStrate'
                            }
                        },
                        {
                            path: 'globalStrateDetail/:id',
                            name: 'globalStrateDetail',
                            component: () => import(/* webpackChunkName: 'safeSoperation' */ '@/views/safe_soperation/protect_strate/global_strate/detail'),
                            meta: {
                                title: '详情页面',
                                sidebar: false,
                                activeMenu: '/safeSoperation/protectStrate/globalStrate'
                            }
                        }
                    ]
                },
                {
                    path: 'systemStrate',
                    name: 'systemStrate',
                    component: EmptyLayout,
                    redirect: '/safeSoperation/systemStrate',
                    meta: {
                        title: '系统策略'
                    },
                    children: [
                        {
                            path: '',
                            component: () => import(/* webpackChunkName: 'safeSoperation' */ '@/views/safe_soperation/protect_strate/system_strate/list'),
                            meta: {
                                title: '系统策略',
                                sidebar: false,
                                breadcrumb: false
                            }
                        },
                        {
                            path: 'systemStrateAdd',
                            name: 'systemStrateAdd',
                            component: () => import(/* webpackChunkName: 'safeSoperation' */ '@/views/safe_soperation/protect_strate/system_strate/form'),
                            meta: {
                                title: '新增页面',
                                sidebar: false,
                                activeMenu: '/safeSoperation/protectStrate/systemStrate'
                            }
                        },
                        {
                            path: 'systemStrateEdit/:id',
                            name: 'systemStrateEdit',
                            component: () => import(/* webpackChunkName: 'safeSoperation' */ '@/views/safe_soperation/protect_strate/system_strate/form'),
                            meta: {
                                title: '编辑页面',
                                sidebar: false,
                                activeMenu: '/safeSoperation/protectStrate/systemStrate'
                            }
                        },
                        {
                            path: 'systemStrateDetail/:id',
                            name: 'systemStrateDetail',
                            component: () => import(/* webpackChunkName: 'safeSoperation' */ '@/views/safe_soperation/protect_strate/system_strate/detail'),
                            meta: {
                                title: '详情页面',
                                sidebar: false,
                                activeMenu: '/safeSoperation/protectStrate/systemStrate'
                            }
                        }
                    ]
                }
            ]
        },
        
        //  日志查询
        {
            path: 'logQuery',
            name: 'logQuery',
            component: EmptyLayout,
            redirect: '/safeSoperation/logQuery',
            meta: {
                title: '日志查询'
            },
            children: [
                {
                    path: '',
                    component: () => import(/* webpackChunkName: 'safeSoperation' */ '@/views/safe_soperation/log_query/list'),
                    meta: {
                        title: '日志查询',
                        sidebar: false,
                        breadcrumb: false
                    }
                },
                {
                    path: 'logQueryAdd',
                    name: 'logQueryAdd',
                    component: () => import(/* webpackChunkName: 'safeSoperation' */ '@/views/safe_soperation/log_query/form'),
                    meta: {
                        title: '新增页面',
                        sidebar: false,
                        activeMenu: '/safeSoperation/logQuery'
                    }
                },
                {
                    path: 'logQueryEdit/:id',
                    name: 'logQueryEdit',
                    component: () => import(/* webpackChunkName: 'safeSoperation' */ '@/views/safe_soperation/log_query/form'),
                    meta: {
                        title: '编辑页面',
                        sidebar: false,
                        activeMenu: '/safeSoperation/logQuery'
                    }
                }
            ]
        }
    ]
}