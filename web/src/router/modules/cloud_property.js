import Layout from '@/layout'
import EmptyLayout from '@/layout/empty'

export default {
    path: '/cloudProperty',
    component: Layout,
    redirect: '/cloudProperty/unitManageList',
    name: 'cloudProperty',
    meta: {
        title: '云资产',
        icon: 'slide-cloud'
    },
    children: [
        //  单位管理
        {
            path: 'unitManageList',
            name: 'unitManageList',
            component: EmptyLayout,
            redirect: '/cloudProperty/unitManageList',
            meta: {
                title: '单位管理'
            },
            children: [
                {
                    path: '',
                    component: () => import(/* webpackChunkName: 'cloudProperty' */ '@/views/cloud_property/unit_manage/list'),
                    meta: {
                        title: '单位管理',
                        sidebar: false,
                        breadcrumb: false
                    }
                },
                {
                    path: 'unitManageAdd',
                    name: 'unitManageAdd',
                    component: () => import(/* webpackChunkName: 'cloudProperty' */ '@/views/cloud_property/unit_manage/form'),
                    meta: {
                        title: '新增页面',
                        sidebar: false,
                        activeMenu: '/cloudProperty/unitManageList'
                    }
                },
                {
                    path: 'unitManageEdit/:id',
                    name: 'unitManageEdit',
                    component: () => import(/* webpackChunkName: 'cloudProperty' */ '@/views/cloud_property/unit_manage/form'),
                    meta: {
                        title: '编辑页面',
                        sidebar: false,
                        activeMenu: '/cloudProperty/unitManageList'
                    }
                }
            ]
        },
        //  资产管理
        {
            path: 'propertyManageList',
            name: 'propertyManageList',
            component: EmptyLayout,
            redirect: '/cloudProperty/propertyManageList',
            meta: {
                title: '资产管理'
            },
            children: [
                {
                    path: '',
                    component: () => import(/* webpackChunkName: 'cloudProperty' */ '@/views/cloud_property/property_manage/list'),
                    meta: {
                        title: '资产管理',
                        sidebar: false,
                        breadcrumb: false
                    }
                },
                {
                    path: 'propertyManageAdd',
                    name: 'propertyManageAdd',
                    component: () => import(/* webpackChunkName: 'cloudProperty' */ '@/views/cloud_property/property_manage/form'),
                    meta: {
                        title: '新增页面',
                        sidebar: false,
                        activeMenu: '/cloudProperty/propertyManageList'
                    }
                },
                {
                    path: 'propertyManageEdit/:id',
                    name: 'propertyManageEdit',
                    component: () => import(/* webpackChunkName: 'cloudProperty' */ '@/views/cloud_property/property_manage/form'),
                    meta: {
                        title: '编辑页面',
                        sidebar: false,
                        activeMenu: '/cloudProperty/propertyManageList'
                    }
                },
                {
                    path: 'propertyManageDetail/:id',
                    name: 'propertyManageDetail',
                    component: () => import(/* webpackChunkName: 'cloudProperty' */ '@/views/cloud_property/property_manage/detail'),
                    meta: {
                        title: '详情页面',
                        sidebar: false,
                        activeMenu: '/cloudProperty/propertyManageList'
                    }
                }
            ]
        }
    ]
}