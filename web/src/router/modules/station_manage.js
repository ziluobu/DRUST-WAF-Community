import Layout from '@/layout'

export default {
    path: '/stationManage',
    component: Layout,
    redirect: '/stationManage/list',
    name: 'stationManage',
    meta: {
        title: '站点管理',
        icon: 'slide-station'
    },
    // children: [
    //     {
    //         path: 'list',
    //         name: 'stationManageList',
    //         component: () => import(/* webpackChunkName: 'stationManage' */ '@/views/station_manage/list'),
    //         meta: {
    //             title: '站点管理',
    //             slidebar: false,
    //             breadcrumb: false,
    //             activeMenu: '/stationManage'
    //         }
    //     }
    // ],
    children: [
        {
            path: '',
            component: () => import(/* webpackChunkName: 'stationManage' */ '@/views/station_manage/list'),
            meta: {
                title: '站点管理',
                sidebar: false,
                breadcrumb: false
            }
        },
        {
            path: 'stationManageAdd',
            name: 'stationManageAdd',
            component: () => import(/* webpackChunkName: 'systemManage' */ '@/views/station_manage/form'),
            meta: {
                title: '新增页面',
                sidebar: false,
                activeMenu: '/stationManage'
            }
        },
        {
            path: 'stationManageEdit/:id',
            name: 'stationManageEdit',
            component: () => import(/* webpackChunkName: 'systemManage' */ '@/views/station_manage/form'),
            meta: {
                title: '编辑页面',
                sidebar: false,
                activeMenu: '/stationManage'
            }
        },
        {
            path: 'stationManageDetail/:id',
            name: 'stationManageDetail',
            component: () => import(/* webpackChunkName: 'systemManage' */ '@/views/station_manage/detail'),
            meta: {
                title: '详情页面',
                sidebar: false,
                activeMenu: '/stationManage'
            }
        }
    ]
}