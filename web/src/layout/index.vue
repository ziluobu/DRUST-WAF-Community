<template>
    <div v-if="!$store.state.settings.enableLargeScreen" class="layout">
        <div id="app-main">
            <transition name="header">
                <header v-if="$store.state.settings.mode == 'pc' && $store.state.settings.showHeader">
                    <div class="header-container">
                        <div class="main">
                            <Logo />
                            <!-- 当头部导航大于 1 个的时候才会显示 -->
                            <div v-if="menuList.length > 1" class="nav">
                                <template v-for="(item, index) in menuList">
                                    <div v-if="item.children && item.children.length !== 0" :key="index" :class="{
                                        'item': true,
                                        'active': index == $store.state.menu.headerActived
                                    }" :title="item.meta.title" @click="switchMenu(item,index)"
                                    >
                                        <svg-icon v-if="item.meta.icon" :name="item.meta.icon" class="icon" />
                                        <span>{{ item.meta.title }}</span>
                                    </div>
                                </template>
                            </div>
                            <!-- <div v-if="$store.state.menu.routes.length > 1" class="nav">
                                <template v-for="(item, index) in $store.state.menu.routes">
                                    <div v-if="item.children && item.children.length !== 0" :key="index" :class="{
                                        'item': true,
                                        'active': index == $store.state.menu.headerActived
                                    }" @click="switchMenu(item,index)"
                                    >
                                        <svg-icon v-if="item.meta.icon" :name="item.meta.icon" class="icon" />
                                        <span v-if="item.meta.title">{{ item.meta.title }}</span>
                                    </div>
                                </template>
                            </div> -->
                        </div>
                        <UserMenu />
                    </div>
                </header>
            </transition>
            <div class="wrapper">
                <div :class="{
                    'sidebar-container': true,
                    'show': $store.state.settings.mode == 'mobile' && !$store.state.settings.sidebarCollapse
                }"
                >
                    <transition name="main-sidebar">
                        <div
                            v-if="(!$store.state.settings.showHeader || $store.state.settings.mode == 'mobile') && ($store.state.menu.routes.length > 1 || $store.state.settings.alwaysShowMainSidebar)"
                            class="main-sidebar-container"
                        >
                            <Logo :show-title="false" class="sidebar-logo" />
                            <div class="nav">
                                <!-- <template v-for="(item, index) in $store.state.menu.routes">
                                    <div v-if="item.children && item.children.length !== 0" :key="index" :class="{
                                        'item': true,
                                        'active': index == $store.state.menu.headerActived
                                    }" :title="item.meta.title" @click="switchMenu(index)"
                                    >
                                        <svg-icon v-if="item.meta.icon" :name="item.meta.icon" class="icon" />
                                        <span>{{ item.meta.title }}</span>
                                    </div>
                                </template> -->
                                <template v-for="(item, index) in menuList">
                                    <div v-if="item.children && item.children.length !== 0" :key="index" :class="{
                                        'item': true,
                                        'active': index == $store.state.menu.headerActived
                                    }" :title="item.meta.title" @click="switchMenu(item,index)"
                                    >
                                        <svg-icon v-if="item.meta.icon" :name="item.meta.icon" class="icon" />
                                        <span>{{ item.meta.title }}</span>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </transition>
                    <div :class="{
                        'sub-sidebar-container': true,
                        'is-collapse': $store.state.settings.mode == 'pc' && $store.state.settings.sidebarCollapse
                    }" @scroll="onSidebarScroll"
                    >
                        <Logo
                            :show-logo="$store.state.menu.routes.length <= 1 && !$store.state.settings.alwaysShowMainSidebar"
                            :class="{
                                'sidebar-logo': true,
                                'sidebar-logo-bg': $store.state.menu.routes.length <= 1 && !$store.state.settings.alwaysShowMainSidebar,
                                'shadow': sidebarScrollTop
                            }"
                        />
                        <el-menu unique-opened :default-active="$route.meta.activeMenu || $route.path"
                                 :collapse="$store.state.settings.mode == 'pc' && $store.state.settings.sidebarCollapse"
                                 :collapse-transition="false" :class="{
                                     'is-collapse-without-logo': $store.state.menu.routes.length > 1 && $store.state.settings.mode == 'pc' && $store.state.settings.sidebarCollapse
                                 }"
                        >
                            <transition-group name="sub-sidebar">
                                <!-- <template v-for="route in $store.getters['menu/sidebarRoutes']">
                                    <SidebarItem v-if="route.meta.sidebar !== false" :key="route.path" :item="route"
                                                 :base-path="route.path"
                                    />
                                </template> -->

                                <template v-for="item in itemMenuList">
                                    <SidebarItem :key="item.path" :item="item"
                                                 :base-path="item.path"
                                    />
                                </template>
                            </transition-group>
                        </el-menu>
                    </div>
                </div>
                <div :class="{
                    'sidebar-mask': true,
                    'show': $store.state.settings.mode == 'mobile' && !$store.state.settings.sidebarCollapse
                }" @click="$store.commit('settings/toggleSidebarCollapse')"
                />
                <div class="main-container" :style="{'padding-bottom': $route.meta.paddingBottom}">
                    <Tabbar v-if="$store.state.settings.enableTabbar && !$store.state.settings.switchTabbarAndTopbar" />
                    <Topbar :class="{'shadow': scrollTop}" />
                    <Tabbar v-if="$store.state.settings.enableTabbar && $store.state.settings.switchTabbarAndTopbar" />
                    <div class="main">
                        <transition name="main" mode="out-in">
                            <keep-alive v-if="isRouterAlive" :include="$store.state.keepAlive.list">
                                <RouterView :key="$route.path" />
                            </keep-alive>
                        </transition>
                    </div>
                    <Copyright v-if="showCopyright" />
                </div>
            </div>
            <el-backtop :right="20" :bottom="20" title="回到顶部" />
        </div>
        <Search />
        <ThemeSetting />
        <Watermark />
    </div>
    <largeScreen v-else />
</template>

<script>
import Logo from './components/Logo'
import UserMenu from './components/UserMenu'
import SidebarItem from './components/SidebarItem'
import Tabbar from './components/Tabbar'
import Topbar from './components/Topbar'
import Search from './components/Search'
import ThemeSetting from './components/ThemeSetting'
import Watermark from './components/Watermark'
import largeScreen from '@/views/large_screen'

export default {
    name: 'Layout',
    components: {
        Logo,
        UserMenu,
        SidebarItem,
        Tabbar,
        Topbar,
        Search,
        ThemeSetting,
        Watermark,
        largeScreen
    },
    provide() {
        return {
            reload: this.reload
        }
    },
    data() {
        return {
            isRouterAlive: true,
            routePath: '',
            sidebarScrollTop: 0,
            scrollTop: 0,
            largeScreen: false,
            menuList: [],
            itemMenuList: [],
            itemList: ''
        }
    },
    computed: {
        showCopyright() {
            return typeof this.$route.meta.copyright !== 'undefined' ? this.$route.meta.copyright : this.$store.state.settings.showCopyright
        }
    },
    watch: {
        $route: 'routeChange',
        '$store.state.settings.sidebarCollapse'(val) {
            if (this.$store.state.settings.mode == 'mobile') {
                if (!val) {
                    document.querySelector('body').classList.add('hidden')
                } else {
                    document.querySelector('body').classList.remove('hidden')
                }
            }
        }
    },
    mounted() {
        // console.log(this.$store.state.menu.routes, '$store.state.menu.routes.length')
        this.initHotkey()
        window.addEventListener('scroll', this.onScroll)
        this.getMenu()
        // console.log(this.$store.state.menu.routes)
    },
    destroyed() {
        window.removeEventListener('scroll', this.onScroll)
    },
    methods: {
        getMenu() {

            this.$api.post('api/getRouters')
                .then(res => {

                    this.menuList = res.data.routes[0].children

                    this.itemMenuList = this.menuList[0].children

                })
        },
        reload(type = 1) {
            if (this.$store.state.settings.enableTabbar) {
                let path = this.$route.meta.activeMenu || this.$route.fullPath
                let name
                this.$store.state.tabbar.list.map(v => {
                    if (v.path == path) {
                        name = v.name
                    }
                })
                if (name) {
                    this.$store.commit('keepAlive/remove', name)
                    this.$router.push({
                        name: 'reload'
                    })
                    this.$nextTick(() => {
                        this.$store.commit('keepAlive/add', name)
                    })
                }
            } else {
                if (type == 1) {
                    this.isRouterAlive = false
                    this.$nextTick(() => (this.isRouterAlive = true))
                } else {
                    this.$router.push({
                        name: 'reload'
                    })
                }
            }
        },
        routeChange(newVal, oldVal) {
            if (newVal.name == oldVal.name) {
                this.reload()
            }
        },
        initHotkey() {
            this.$hotkeys('alt+s', e => {
                if (this.$store.state.settings.enableNavSearch) {
                    e.preventDefault()
                    this.$eventBus.$emit('global-search-toggle')
                }
            })
            this.$hotkeys('f5', e => {
                if (this.$store.state.settings.enablePageReload) {
                    e.preventDefault()
                    this.reload(this.$store.state.settings.enableTabbar ? 1 : 2)
                }
            })
        },
        onSidebarScroll(e) {
            this.sidebarScrollTop = e.target.scrollTop
        },
        onScroll() {
            this.scrollTop = document.documentElement.scrollTop || document.body.scrollTop
        },
        switchMenu(val, index) {
            // console.log(val)
            this.$store.commit('menu/switchHeaderActived', index)
            // if (this.$store.state.settings.switchSidebarAndPageJump) {
            //     this.$router.push(this.$store.getters['menu/sidebarRoutes'][0].path)
            // }
            if (this.menuList[index].children.length === 0) {

                // this.gotoRouteHandle(val)
            } else {
                this.itemMenuList = this.menuList[index].children
                // console.log(this.itemMenuList)
            }
        }
    }
}
</script>

<style lang="scss" scoped>
[data-layout=adaption] {
    #app-main {
        width: 100%;
    }
}
[data-layout=adaption-min-width] {
    #app-main {
        min-width: $g-app-width;
    }
}
[data-layout=center] {
    #app-main {
        width: $g-app-width;
    }
}
[data-layout=center-max-width] {
    #app-main {
        width: $g-app-width;
        max-width: 100%;
    }
}

// 侧边栏未折叠
[data-sidebar-no-collapse] {
    .sidebar-container {
        width: calc(#{$g-main-sidebar-width} + #{$g-sub-sidebar-width});
    }
    .main-container {
        margin-left: calc(#{$g-main-sidebar-width} + #{$g-sub-sidebar-width});
    }

    // 没有主侧边栏
    &[data-no-main-sidebar] {
        .sidebar-container {
            width: $g-sub-sidebar-width;
        }
        .main-container {
            margin-left: $g-sub-sidebar-width;
        }
    }
}

// 侧边栏折叠
[data-sidebar-collapse] {
    .sidebar-container {
        width: calc(#{$g-main-sidebar-width} + 64px);
    }
    .main-container {
        margin-left: calc(#{$g-main-sidebar-width} + 64px);
    }

    // 没有主侧边栏
    &[data-no-main-sidebar] {
        .sidebar-container {
            width: 64px;
        }
        .main-container {
            margin-left: 64px;
        }
    }
}
[data-mode=mobile] {
    #app-main {
        width: 100%;
        min-width: unset;
        max-width: unset;
    }
    .sidebar-container {
        width: calc(#{$g-main-sidebar-width} + #{$g-sub-sidebar-width});
        transform: translateX(-#{$g-main-sidebar-width}) translateX(-#{$g-sub-sidebar-width});
        &.show {
            transform: translateX(0);
        }
    }
    .main-container {
        margin-left: 0;
    }
    &[data-no-main-sidebar] {
        .sidebar-container {
            width: calc(#{$g-main-sidebar-width} + #{$g-sub-sidebar-width});
            transform: translateX(-#{$g-main-sidebar-width}) translateX(-#{$g-sub-sidebar-width});
            &.show {
                transform: translateX(0);
            }
        }
        .main-container {
            margin-left: 0;
        }
    }
}
.layout {
    height: 100%;
}
#app-main {
    height: 100%;
    margin: 0 auto;
    transition: all 0.2s;
}
header {
    position: fixed;
    z-index: 1000;
    top: 0;
    left: 0;
    right: 0;
    display: flex;
    align-items: center;
    padding: 0 20px;
    height: $g-header-height;
    @include themeify {
        color: themed('g-header-color');
        background-color: themed('g-header-bg');
    }
    .header-container {
        width: $g-header-width;
        margin: 0 auto;
        display: flex;
        align-items: center;
        justify-content: space-between;
        .main {
            display: flex;
            align-items: center;
        }
    }
    @media screen and (max-width: $g-header-width) {
        .header-container {
            width: 100%;
        }
    }
    ::v-deep .title {
        position: relative;
        width: inherit;
        height: inherit;
        padding: inherit;
        background-color: inherit;
        .logo {
            width: 50px;
            height: 50px;
        }
        span {
            font-size: 24px;
            letter-spacing: 1px;
            @include themeify {
                color: themed('g-header-color');
            }
        }
    }
    .nav {
        display: flex;
        margin-left: 50px;
        .item {
            margin: 0 10px;
            padding: 10px;
            border-radius: 4px;
            cursor: pointer;
            transition: all 0.3s;
            @include themeify {
                background-color: themed('g-header-bg');
                color: themed('g-header-menu-color');
            }
            &:hover {
                @include themeify {
                    color: themed('g-header-menu-hover-color');
                    background-color: themed('g-header-menu-hover-bg');
                }
            }
            &.active {
                @include themeify {
                    color: themed('g-header-menu-active-color');
                    background-color: themed('g-header-menu-active-bg');
                }
            }
            .icon {
                font-size: 20px;
                vertical-align: middle;
                & + span {
                    margin-left: 5px;
                    vertical-align: middle;
                }
            }
        }
    }
    ::v-deep .user {
        padding: 0;
        .tools [class^=ri-] {
            @include themeify {
                color: themed('g-header-color');
            }
        }
        .user-container {
            font-size: 16px;
            @include themeify {
                color: themed('g-header-color');
            }
        }
    }
}
.wrapper {
    position: relative;
    width: 100%;
    height: 100%;
    .sidebar-container {
        position: fixed;
        z-index: 1010;
        top: 0;
        bottom: 0;
        display: flex;
        transition: transform 0.3s;
        transform: transition3d(0, 0, 0);
        @include themeify {
            box-shadow: -1px 0 0 0 darken(themed('g-main-bg'), 10);
        }
    }
    .sidebar-mask {
        position: fixed;
        z-index: 1000;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba($color: #000, $alpha: 0.5);
        backdrop-filter: blur(2px);
        transition: all 0.2s;
        transform: translateZ(0);
        opacity: 0;
        visibility: hidden;
        &.show {
            opacity: 1;
            visibility: visible;
        }
    }
    .main-sidebar-container,
    .sub-sidebar-container {
        overflow-x: hidden;
        overflow-y: auto;
        overscroll-behavior: contain;
        // firefox隐藏滚动条
        scrollbar-width: none;
        // chrome隐藏滚动条
        &::-webkit-scrollbar {
            display: none;
        }
    }
    .main-sidebar-container {
        position: relative;
        z-index: 1;
        width: $g-main-sidebar-width;
        @include themeify {
            color: themed('g-main-sidebar-menu-color');
            background-color: themed('g-main-sidebar-bg');
        }
        .sidebar-logo {
            transition: 0.3s;
            @include themeify {
                background-color: themed('g-main-sidebar-bg');
            }
        }
        .nav {
            width: inherit;
            padding-top: $g-sidebar-logo-height;
            .item {
                display: flex;
                flex-direction: column;
                justify-content: center;
                text-align: center;
                height: 60px;
                padding: 0 5px;
                cursor: pointer;
                transition: color 0.3s, background-color 0.3s;
                &:hover {
                    @include themeify {
                        color: themed('g-main-sidebar-menu-hover-color');
                        background-color: themed('g-main-sidebar-menu-hover-bg');
                    }
                }
                &.active {
                    @include themeify {
                        color: themed('g-main-sidebar-menu-active-color');
                        background-color: themed('g-main-sidebar-menu-active-bg');
                    }
                }
                .icon {
                    margin: 0 auto;
                    font-size: 20px;
                }
                span {
                    text-align: center;
                    font-size: 14px;
                    @include text-overflow;
                }
            }
        }
    }
    .sub-sidebar-container {
        width: $g-sub-sidebar-width;
        position: absolute;
        left: 0;
        top: 0;
        bottom: 0;
        transition: 0.3s;
        @include themeify {
            background-color: themed('g-sub-sidebar-bg');
            box-shadow: 10px 0 10px -10px darken(themed('g-sub-sidebar-bg'), 20);
        }
        &.is-collapse {
            width: 64px;
            .sidebar-logo {
                &:not(.sidebar-logo-bg) {
                    display: none;
                }
                ::v-deep span {
                    display: none;
                }
            }
        }
        .sidebar-logo {
            transition: box-shadow 0.2s, background-color 0.3s, color 0.3s;
            @include themeify {
                background-color: themed('g-sub-sidebar-bg');
            }
            &:not(.sidebar-logo-bg) {
                ::v-deep span {
                    @include themeify {
                        color: themed('g-sub-sidebar-menu-color');
                    }
                }
            }
            &.sidebar-logo-bg {
                @include themeify {
                    background-color: themed('g-main-sidebar-bg');
                }
            }
            &.shadow {
                @include themeify {
                    box-shadow: 0 10px 10px -10px darken(themed('g-sub-sidebar-bg'), 20);
                }
            }
        }
        .el-menu {
            border-right: 0;
            padding-top: $g-sidebar-logo-height;
            transition: border-color 0.3s, background-color 0.3s, color 0.3s;
            @include themeify {
                background-color: themed('g-sub-sidebar-bg');
            }
            &:not(.el-menu--collapse) {
                width: inherit;
            }
            &.is-collapse-without-logo {
                padding-top: 0;
            }
            &.el-menu--collapse {
                ::v-deep .icon {
                    margin-right: 0;
                }
                ::v-deep .el-menu-item,
                ::v-deep .el-submenu__title {
                    span {
                        display: none;
                    }
                    i {
                        right: 7px;
                        margin-top: -5px;
                    }
                }
            }
        }
    }
    .main-sidebar-container + .sub-sidebar-container {
        left: $g-main-sidebar-width;
    }
    .main-container {
        display: flex;
        flex-direction: column;
        min-height: 100%;
        transition: margin-left 0.3s;
        @include themeify {
            background-color: themed('g-main-bg');
            box-shadow: 1px 0 0 0 darken(themed('g-main-bg'), 10);
        }
        .tabbar-container + .topbar-container {
            top: $g-tabbar-height;
            z-index: 998;
        }
        .topbar-container + .tabbar-container {
            top: $g-topbar-height;
        }
        .main {
            height: 100%;
            flex: auto;
            position: relative;
            padding: $g-topbar-height 0 0;
            overflow: hidden;
            transition: 0.3s;
        }
        .topbar-container + .main {
            padding: calc(#{$g-topbar-height}) 0 0;
        }
        .tabbar-container + .topbar-container + .main,
        .topbar-container + .tabbar-container + .main {
            padding: calc(#{$g-tabbar-height} + #{$g-topbar-height}) 0 0;
        }
    }
}
header + .wrapper {
    padding-top: $g-header-height;
    .sidebar-container {
        top: $g-header-height;
        .sidebar-logo {
            display: none;
        }
        .el-menu {
            padding-top: 0;
        }
    }
    .main-container {
        .tabbar-container {
            top: $g-header-height;
        }
        .topbar-container {
            top: $g-header-height;
            ::v-deep .user {
                display: none;
            }
        }
        .tabbar-container + .topbar-container {
            top: calc(#{$g-header-height} + #{$g-tabbar-height});
        }
        .topbar-container + .tabbar-container {
            top: calc(#{$g-header-height} + #{$g-topbar-height});
        }
    }
}

// 头部动画
.header-enter-active {
    transition: 0.2s;
}
.header-enter {
    transform: translateY(-#{$g-header-height});
}

// 主侧边栏动画
.main-sidebar-enter-active {
    transition: 0.3s;
}
.main-sidebar-enter {
    transform: translateX(-#{$g-main-sidebar-width});
}

// 次侧边栏动画
.sub-sidebar-enter-active {
    transition: 0.3s;
}
.sub-sidebar-enter,
.sub-sidebar-leave-active {
    opacity: 0;
    transform: translateY(30px) skewY(10deg);
}
.sub-sidebar-leave-active {
    position: absolute;
}

// 主内容区动画
.main-enter-active {
    transition: 0.2s;
}
.main-leave-active {
    transition: 0.15s;
}
.main-enter {
    opacity: 0;
    margin-left: -20px;
}
.main-leave-to {
    opacity: 0;
    margin-left: 20px;
}
</style>
