<template>
    <div>
        <el-drawer title="主题配置" :visible.sync="isShow" direction="rtl" :size="$store.state.settings.mode == 'pc' ? '500px' : '300px'">
            <el-alert title="主题配置可实时预览效果，更多设置请在 src/settings.js 中进行设置，建议在生产环境隐藏主题配置功能" type="error" :closable="false" />
            <el-form ref="form" :label-position="$store.state.settings.mode == 'pc' ? 'right' : 'top'" label-width="100px" size="small">
                <el-form-item v-if="$store.state.settings.mode == 'pc'" label="界面布局">
                    <el-select v-model="layout">
                        <el-option label="自适应" value="adaption" />
                        <el-option label="自适应（有最小宽度）" value="adaption-min-width" />
                        <el-option label="定宽居中" value="center" />
                        <el-option label="定宽居中（有最大宽度）" value="center-max-width" />
                    </el-select>
                </el-form-item>
                <el-form-item label="主题风格">
                    <el-select v-model="theme">
                        <el-option label="默认" value="default" />
                        <el-option label="Vue CLI 风格" value="vue-cli" />
                        <el-option label="码云风格" value="gitee" />
                        <el-option label="清新" value="freshness" />
                        <el-option label="素雅" value="elegant" />
                        <el-option label="纯白" value="pure-white" />
                    </el-select>
                </el-form-item>
                <el-form-item label="组件尺寸">
                    <el-radio-group v-model="elementSize">
                        <el-radio-button label="large">默认</el-radio-button>
                        <el-radio-button label="medium">中等</el-radio-button>
                        <el-radio-button label="small">小</el-radio-button>
                        <el-radio-button label="mini">极小</el-radio-button>
                    </el-radio-group>
                    <el-alert title="可全局设置 Element 组件的尺寸大小" type="info" :closable="false" />
                </el-form-item>
                <el-form-item v-if="$store.state.settings.mode == 'pc'" label="头部">
                    <el-radio-group v-model="showHeader">
                        <el-radio-button :label="true">显示</el-radio-button>
                        <el-radio-button :label="false">隐藏</el-radio-button>
                    </el-radio-group>
                </el-form-item>
                <el-form-item v-if="$store.state.settings.mode == 'pc'" label="侧边栏切换">
                    <el-radio-group v-model="enableSidebarCollapse">
                        <el-radio-button :label="true">启用</el-radio-button>
                        <el-radio-button :label="false">关闭</el-radio-button>
                    </el-radio-group>
                </el-form-item>
                <el-form-item label="侧边栏导航">
                    <el-radio-group v-model="sidebarCollapse">
                        <el-radio-button :label="true">收起</el-radio-button>
                        <el-radio-button :label="false">展开</el-radio-button>
                    </el-radio-group>
                </el-form-item>
                <el-form-item label="切换跳转">
                    <el-radio-group v-model="switchSidebarAndPageJump">
                        <el-radio-button :label="true">启用</el-radio-button>
                        <el-radio-button :label="false">关闭</el-radio-button>
                    </el-radio-group>
                    <el-alert title="开启该功能后，切换侧边栏时，页面自动跳转至该侧边栏导航下第一个路由地址" type="info" :closable="false" />
                </el-form-item>
                <el-form-item label="标签栏">
                    <el-radio-group v-model="enableTabbar">
                        <el-radio-button :label="true">启用</el-radio-button>
                        <el-radio-button :label="false">关闭</el-radio-button>
                    </el-radio-group>
                </el-form-item>
                <el-form-item label="顶栏">
                    <el-radio-group v-model="topbarFixed">
                        <el-radio-button :label="true">固定</el-radio-button>
                        <el-radio-button :label="false">不固定</el-radio-button>
                    </el-radio-group>
                    <el-alert title="包含顶部导航栏和标签栏" type="info" :closable="false" />
                </el-form-item>
                <el-form-item label="顶栏显示">
                    <el-radio-group v-model="switchTabbarAndTopbar">
                        <el-radio-button :label="true">顶部导航栏<br><br>标签栏</el-radio-button>
                        <el-radio-button :label="false">标签栏<br><br>顶部导航栏</el-radio-button>
                    </el-radio-group>
                </el-form-item>
                <el-form-item v-if="$store.state.settings.mode == 'pc'" label="面包屑导航">
                    <el-radio-group v-model="enableBreadcrumb">
                        <el-radio-button :label="true">启用</el-radio-button>
                        <el-radio-button :label="false">关闭</el-radio-button>
                    </el-radio-group>
                </el-form-item>
                <el-form-item label="底部版权">
                    <el-radio-group v-model="showCopyright">
                        <el-radio-button :label="true">显示</el-radio-button>
                        <el-radio-button :label="false">隐藏</el-radio-button>
                    </el-radio-group>
                </el-form-item>
                <el-form-item label="导航搜索">
                    <el-radio-group v-model="enableNavSearch">
                        <el-radio-button :label="true">开启</el-radio-button>
                        <el-radio-button :label="false">关闭</el-radio-button>
                    </el-radio-group>
                    <el-alert title="该功能为页面右上角的搜索按钮，可对侧边栏导航进行快捷搜索" type="info" :closable="false" />
                </el-form-item>
                <el-form-item label="通知中心">
                    <el-radio-group v-model="enableNotification">
                        <el-radio-button :label="true">开启</el-radio-button>
                        <el-radio-button :label="false">关闭</el-radio-button>
                    </el-radio-group>
                    <el-alert title="该功能为页面右上角的通知中心，具体业务功能需自行开发，框架仅提供展示模版" type="info" :closable="false" />
                </el-form-item>
                <el-form-item label="国际化">
                    <el-radio-group v-model="enableI18n">
                        <el-radio-button :label="true">开启</el-radio-button>
                        <el-radio-button :label="false">关闭</el-radio-button>
                    </el-radio-group>
                </el-form-item>
                <el-form-item v-if="$store.state.settings.mode == 'pc'" label="全屏">
                    <el-radio-group v-model="enableFullscreen">
                        <el-radio-button :label="true">开启</el-radio-button>
                        <el-radio-button :label="false">关闭</el-radio-button>
                    </el-radio-group>
                    <el-alert title="该功能为页面右上角的全屏按钮" type="info" :closable="false" />
                    <el-alert title="不建议开启，该功能使用场景极少，用户习惯于通过窗口“最大化”功能来扩大显示区域，以显示更多内容，并且使用 F11 键也可以进入全屏效果" type="warning" :closable="false" />
                </el-form-item>
                <el-form-item label="页面刷新">
                    <el-radio-group v-model="enablePageReload">
                        <el-radio-button :label="true">开启</el-radio-button>
                        <el-radio-button :label="false">关闭</el-radio-button>
                    </el-radio-group>
                    <el-alert title="该功能为页面右上角的刷新按钮，开启时会阻止 F5 键原刷新功能，并采用框架提供的刷新模式进行页面刷新" type="info" :closable="false" />
                </el-form-item>
                <el-form-item label="加载进度条">
                    <el-radio-group v-model="enableProgress">
                        <el-radio-button :label="true">开启</el-radio-button>
                        <el-radio-button :label="false">关闭</el-radio-button>
                    </el-radio-group>
                    <el-alert title="该功能开启时，跳转路由会看到页面顶部有条蓝色的进度条" type="info" :closable="false" />
                </el-form-item>
                <el-form-item label="动态标题">
                    <el-radio-group v-model="enableDynamicTitle">
                        <el-radio-button :label="true">开启</el-radio-button>
                        <el-radio-button :label="false">关闭</el-radio-button>
                    </el-radio-group>
                    <el-alert title="该功能开启时，页面标题会显示当前路由标题，格式为“页面标题 - 网站名称”；关闭时则显示网站名称，网站名称在项目根目录下 .env.* 文件里配置" type="info" :closable="false" />
                </el-form-item>
                <el-form-item label="控制台">
                    <el-radio-group v-model="enableDashboard">
                        <el-radio-button :label="true">开启</el-radio-button>
                        <el-radio-button :label="false">关闭</el-radio-button>
                    </el-radio-group>
                    <el-alert title="控制台即欢迎页，该功能开启时，登录成功默认进入控制台；关闭时则默认进入侧边栏导航第一个导航页面" type="info" :closable="false" />
                </el-form-item>
                <el-form-item label="页面水印">
                    <el-radio-group v-model="enableWatermark">
                        <el-radio-button :label="true">开启</el-radio-button>
                        <el-radio-button :label="false">关闭</el-radio-button>
                    </el-radio-group>
                </el-form-item>
            </el-form>
        </el-drawer>
    </div>
</template>

<script>
export default {
    name: 'ThemeSetting',
    inject: ['reload'],
    props: {},
    data() {
        return {
            isShow: false
        }
    },
    computed: {
        layout: {
            get: function() {
                return this.$store.state.settings.layout
            },
            set: function(newValue) {
                this.$store.commit('settings/updateThemeSetting', {
                    'layout': newValue
                })
            }
        },
        theme: {
            get: function() {
                return this.$store.state.settings.theme
            },
            set: function(newValue) {

                this.$store.commit('settings/updateThemeSetting', {
                    'theme': newValue
                })
            }
        },
        elementSize: {
            get: function() {
                return this.$store.state.settings.elementSize
            },
            set: function(newValue) {
                this.$ELEMENT.size = newValue
                this.$store.commit('settings/updateThemeSetting', {
                    'elementSize': newValue
                })
                this.reload()
            }
        },
        showHeader: {
            get: function() {
                return this.$store.state.settings.showHeader
            },
            set: function(newValue) {
                this.$store.commit('settings/updateThemeSetting', {
                    'showHeader': newValue
                })
            }
        },
        enableSidebarCollapse: {
            get: function() {
                return this.$store.state.settings.enableSidebarCollapse
            },
            set: function(newValue) {
                this.$store.commit('settings/updateThemeSetting', {
                    'enableSidebarCollapse': newValue
                })
            }
        },
        sidebarCollapse: {
            get: function() {
                return this.$store.state.settings.sidebarCollapse
            },
            set: function(newValue) {
                this.$store.commit('settings/updateThemeSetting', {
                    'sidebarCollapse': newValue
                })
            }
        },
        switchSidebarAndPageJump: {
            get: function() {
                return this.$store.state.settings.switchSidebarAndPageJump
            },
            set: function(newValue) {
                this.$store.commit('settings/updateThemeSetting', {
                    'switchSidebarAndPageJump': newValue
                })
            }
        },
        enableTabbar: {
            get: function() {
                return this.$store.state.settings.enableTabbar
            },
            set: function(newValue) {
                this.$store.commit('settings/updateThemeSetting', {
                    'enableTabbar': newValue
                })
            }
        },
        topbarFixed: {
            get: function() {
                return this.$store.state.settings.topbarFixed
            },
            set: function(newValue) {
                this.$store.commit('settings/updateThemeSetting', {
                    'topbarFixed': newValue
                })
            }
        },
        switchTabbarAndTopbar: {
            get: function() {
                return this.$store.state.settings.switchTabbarAndTopbar
            },
            set: function(newValue) {
                this.$store.commit('settings/updateThemeSetting', {
                    'switchTabbarAndTopbar': newValue
                })
            }
        },
        enableBreadcrumb: {
            get: function() {
                return this.$store.state.settings.enableBreadcrumb
            },
            set: function(newValue) {
                this.$store.commit('settings/updateThemeSetting', {
                    'enableBreadcrumb': newValue
                })
            }
        },
        showCopyright: {
            get: function() {
                return this.$store.state.settings.showCopyright
            },
            set: function(newValue) {
                this.$store.commit('settings/updateThemeSetting', {
                    'showCopyright': newValue
                })
            }
        },
        enableNavSearch: {
            get: function() {
                return this.$store.state.settings.enableNavSearch
            },
            set: function(newValue) {
                this.$store.commit('settings/updateThemeSetting', {
                    'enableNavSearch': newValue
                })
            }
        },
        enableNotification: {
            get: function() {
                return this.$store.state.settings.enableNotification
            },
            set: function(newValue) {
                this.$store.commit('settings/updateThemeSetting', {
                    'enableNotification': newValue
                })
            }
        },
        enableI18n: {
            get: function() {
                return this.$store.state.settings.enableI18n
            },
            set: function(newValue) {
                this.$store.commit('settings/updateThemeSetting', {
                    'enableI18n': newValue
                })
            }
        },
        enableFullscreen: {
            get: function() {
                return this.$store.state.settings.enableFullscreen
            },
            set: function(newValue) {
                this.$store.commit('settings/updateThemeSetting', {
                    'enableFullscreen': newValue
                })
            }
        },
        enablePageReload: {
            get: function() {
                return this.$store.state.settings.enablePageReload
            },
            set: function(newValue) {
                this.$store.commit('settings/updateThemeSetting', {
                    'enablePageReload': newValue
                })
            }
        },
        enableProgress: {
            get: function() {
                return this.$store.state.settings.enableProgress
            },
            set: function(newValue) {
                this.$store.commit('settings/updateThemeSetting', {
                    'enableProgress': newValue
                })
            }
        },
        enableDynamicTitle: {
            get: function() {
                return this.$store.state.settings.enableDynamicTitle
            },
            set: function(newValue) {
                this.$store.commit('settings/updateThemeSetting', {
                    'enableDynamicTitle': newValue
                })
            }
        },
        enableDashboard: {
            get: function() {
                return this.$store.state.settings.enableDashboard
            },
            set: function(newValue) {
                this.$store.commit('settings/updateThemeSetting', {
                    'enableDashboard': newValue
                })
            }
        },
        enableWatermark: {
            get: function() {
                return this.$store.state.settings.enableWatermark
            },
            set: function(newValue) {
                // console.log(newValue, 'newValue')
                this.$store.commit('settings/updateThemeSetting', {
                    'enableWatermark': newValue
                })
            }
        }
    },
    mounted() {
        this.$eventBus.$on('global-theme-toggle', () => {
            this.isShow = !this.isShow
        })
    },
    methods: {}
}
</script>

<style lang="scss" scoped>
::v-deep .el-drawer__wrapper,
::v-deep .el-drawer__wrapper * {
    outline: none !important;
}
::v-deep .el-drawer__body {
    padding: 0 20px 20px;
    overflow: auto;
}
.el-form {
    margin-top: 20px;
    .el-alert {
        margin-top: 10px;
        line-height: initial;
    }
}
</style>
