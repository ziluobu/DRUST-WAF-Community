import Layout from '@/layout'

export default {
    path: '/protectReport',
    component: Layout,
    redirect: '/protectReport/list',
    name: 'protectReport',
    meta: {
        title: '防护报告',
        icon: 'slide-report'
    },
    children: [
        {
            path: '',
            component: () => import(/* webpackChunkName: 'protectReport' */ '@/views/protect_report/list'),
            meta: {
                title: '防护报告',
                sidebar: false,
                breadcrumb: false
            }
        }
        // {
        //     path: 'protectReportAdd',
        //     name: 'protectReportAdd',
        //     component: () => import(/* webpackChunkName: 'systemManage' */ '@/views/protect_report/form'),
        //     meta: {
        //         title: '新增页面',
        //         sidebar: false,
        //         activeMenu: '/protectReport'
        //     }
        // },
        // {
        //     path: 'protectReportEdit/:id',
        //     name: 'protectReportEdit',
        //     component: () => import(/* webpackChunkName: 'systemManage' */ '@/views/protect_report/form'),
        //     meta: {
        //         title: '编辑页面',
        //         sidebar: false,
        //         activeMenu: '/protectReport'
        //     }
        // }
    ]
}