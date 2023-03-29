import Layout from '@/layout'

export default {
    path: '/workbenchReport',
    component: Layout,
    redirect: '/workbenchReport/index',
    name: 'workbenchReport',
    meta: {
        title: '工作台',
        icon: 'ri-mac-line'
    },
   
    children: [
        {
            path: '',
            component: () => import(/* webpackChunkName: 'workbenchReport' */ '@/views/workbench_report/index'),
            meta: {
                title: '工作台',
                sidebar: false,
                breadcrumb: false
            }
        }
        // {
        //     path: 'largeScreenAdd',
        //     name: 'largeScreenAdd',
        //     component: () => import(/* webpackChunkName: 'systemManage' */ '@/views/large_screen/form'),
        //     meta: {
        //         title: '新增页面',
        //         sidebar: false,
        //         activeMenu: '/largeScreen'
        //     }
        // },
        // {
        //     path: 'largeScreenEdit/:id',
        //     name: 'largeScreenEdit',
        //     component: () => import(/* webpackChunkName: 'systemManage' */ '@/views/large_screen/form'),
        //     meta: {
        //         title: '编辑页面',
        //         sidebar: false,
        //         activeMenu: '/largeScreen'
        //     }
        // }
    ]
}