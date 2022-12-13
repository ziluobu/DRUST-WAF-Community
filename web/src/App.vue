<template>
    <div id="app">
        <RouterView />
    </div>
</template>

<script>
export default {
    provide() {
        return {
            generateI18nTitle: this.generateI18nTitle
        }
    },
    data() {
        return {}
    },
    watch: {
        $route: {
            handler: 'routeChange',
            immediate: true
        },
        '$store.state.keepAlive.list'(val) {
            process.env.NODE_ENV === 'development' && console.log(`[ keepAliveList ] ${val}`)
        },
        '$store.state.settings.mode': {
            handler() {
                if (this.$store.state.settings.mode === 'pc') {
                    this.$store.commit('settings/updateThemeSetting', {
                        'sidebarCollapse': this.$store.state.settings.sidebarCollapseLastStatus
                    })
                } else if (this.$store.state.settings.mode == 'mobile') {
                    this.$store.commit('settings/updateThemeSetting', {
                        'sidebarCollapse': true
                    })
                }
                document.body.setAttribute('data-mode', this.$store.state.settings.mode)
            },
            immediate: true
        },
        '$store.state.settings.layout': {
            handler() {
                document.body.setAttribute('data-layout', this.$store.state.settings.layout)
            },
            immediate: true
        },
        '$store.state.settings.theme': {
            handler() {
                document.body.setAttribute('data-theme', this.$store.state.settings.theme)
            },
            immediate: true
        },
        '$store.state.settings.showHeader': {
            handler() {
                document.body.removeAttribute('data-no-main-sidebar')
                if (this.$store.state.settings.showHeader || (this.$store.state.menu.routes.length <= 1 && !this.$store.state.settings.alwaysShowMainSidebar)) {
                    document.body.setAttribute('data-no-main-sidebar', '')
                }
            },
            immediate: true
        },
        '$store.state.menu.routes': {
            handler() {
                document.body.removeAttribute('data-no-main-sidebar')
                if (this.$store.state.settings.showHeader || (this.$store.state.menu.routes.length <= 1 && !this.$store.state.settings.alwaysShowMainSidebar)) {
                    document.body.setAttribute('data-no-main-sidebar', '')
                }
            },
            immediate: true,
            deep: true
        },
        '$store.state.settings.sidebarCollapse': {
            handler() {
                document.body.removeAttribute('data-sidebar-no-collapse')
                document.body.removeAttribute('data-sidebar-collapse')
                if (this.$store.state.settings.sidebarCollapse) {
                    document.body.setAttribute('data-sidebar-collapse', '')
                } else {
                    document.body.setAttribute('data-sidebar-no-collapse', '')
                }
            },
            immediate: true
        }
    },
    mounted() {
        window.onresize = () => {
            this.$store.commit('settings/setMode', document.body.clientWidth)
        }
        window.onresize()
    },
    methods: {
        // 路由 title 转国际化，如果没有配置则默认显示 title
        generateI18nTitle(key, defaultTitle) {
            let title
            if (this.$te(key)) {
                title = this.$t(key)
            } else {
                title = defaultTitle
            }
            return title
        },
        // 监听路由变化，更新页面 title
        routeChange() {
            this.$route.meta.title && this.$store.commit('settings/setTitle', this.generateI18nTitle(this.$route.meta.i18n, this.$route.meta.title))
        }
    },
    metaInfo() {
        return {
            title: this.$store.state.settings.enableDynamicTitle && this.$store.state.settings.title,
            titleTemplate: title => {
                return title ? `${title} - ${process.env.VUE_APP_TITLE}` : process.env.VUE_APP_TITLE
            }
        }
    }
}
</script>

<style scoped>
#app {
    height: 100%;
}
</style>
