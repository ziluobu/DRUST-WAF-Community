<template>
    <div :class="{
        'tabbar-container':true,
        'fixed': $store.state.settings.topbarFixed
    }" data-fixed-calc-width
    >
        <div ref="tabs" class="tabs" :class="{
            'tabs-ontop': $store.state.settings.switchTabbarAndTopbar
        }" @mousewheel.prevent
        >
            <draggable v-model="tabbarList" v-bind="dragOptions" @start="isDragging = true" @end="isDragging = false">
                <transition-group ref="tab-container" type="transition" name="tabbar" :duration="{ leave: 200 }" tag="div" class="tab-container">
                    <div v-for="item in $store.state.tabbar.list" :key="item.path" :ref="`tab-${item.path}`" :class="{
                        'tab': true,
                        'tab-ontop': $store.state.settings.switchTabbarAndTopbar,
                        'tab-dragging': isDragging,
                        'actived': item.path == activedTabPath
                    }" :title="generateI18nTitle(item.i18n, item.title)" @click="$router.push(item.path)" @contextmenu.prevent="onTabbarContextmenu($event, item)"
                    >
                        <div class="tab-dividers" />
                        <div class="tab-background">
                            <svg version="1.1" xmlns="http://www.w3.org/2000/svg"><defs><symbol id="tab-geometry-left" viewBox="0 0 214 36"><path d="M17 0h197v36H0v-2c4.5 0 9-3.5 9-8V8c0-4.5 3.5-8 8-8z" /></symbol><symbol id="tab-geometry-right" viewBox="0 0 214 36"><use xlink:href="#tab-geometry-left" /></symbol><clipPath id="crop"><rect class="mask" width="100%" height="100%" x="0" /></clipPath></defs><svg width="52%" height="100%"><use xlink:href="#tab-geometry-left" width="214" height="36" class="tab-geometry" /></svg><g transform="scale(-1, 1)"><svg width="52%" height="100%" x="-100%" y="0"><use xlink:href="#tab-geometry-right" width="214" height="36" class="tab-geometry" /></svg></g></svg>
                        </div>
                        <div class="tab-content">
                            <div class="title">{{ generateI18nTitle(item.i18n, item.title) }}</div>
                            <div v-if="!item.isPin" class="drag-handle" />
                            <i v-if="item.isPin" class="ri-pushpin-2-fill action-icon" @click.stop="$store.dispatch('tabbar/unPin', item.path)" />
                            <i v-else-if="$store.state.tabbar.list.length > 1" class="ri-close-fill action-icon" @click.stop="onTabClose(item.path)" />
                        </div>
                    </div>
                </transition-group>
            </draggable>
        </div>
        <div v-if="isShowMoreAction" class="more-action">
            <el-dropdown @command="actionCommand">
                <i class="ri-arrow-down-s-fill" />
                <el-dropdown-menu slot="dropdown">
                    <el-dropdown-item command="other-side" :disabled="!hasTabbarOtherSideCanClose">
                        <i class="el-icon-close" />
                        ?????????????????????
                    </el-dropdown-item>
                    <el-dropdown-item command="left-side" :disabled="!hasTabbarLeftSideCanClose">
                        <i class="el-icon-arrow-left" />
                        ?????????????????????
                    </el-dropdown-item>
                    <el-dropdown-item command="right-side" :disabled="!hasTabbarRightSideCanClose">
                        <i class="el-icon-arrow-right" />
                        ?????????????????????
                    </el-dropdown-item>
                </el-dropdown-menu>
            </el-dropdown>
        </div>
    </div>
</template>

<script>
import storage from '@/util/storage'
import draggable from 'vuedraggable'

export default {
    name: 'Tabbar',
    components: {
        draggable
    },
    inject: ['reload', 'generateI18nTitle'],
    data() {
        return {
            isDragging: false
        }
    },
    computed: {
        // ??????????????????????????????????????? activeMenu ????????????????????????????????????????????????????????????
        // ????????????????????? return this.$route.fullPath ????????????????????????????????????????????????
        activedTabPath() {
            return this.$route.meta.activeMenu || this.$route.fullPath
        },
        // ???????????????????????????????????????????????????
        hasTabbarOtherSideCanClose() {
            return this.checkOtherSideHasTabCanClose()
        },
        // ???????????????????????????????????????????????????
        hasTabbarLeftSideCanClose() {
            return this.checkLeftSideHasTabCanClose()
        },
        // ???????????????????????????????????????????????????
        hasTabbarRightSideCanClose() {
            return this.checkRightSideHasTabCanClose()
        },
        isShowMoreAction() {
            return this.$store.state.tabbar.list.length > 1 && (this.hasTabbarOtherSideCanClose || this.hasTabbarLeftSideCanClose || this.hasTabbarRightSideCanClose)
        },
        tabbarList: {
            get() {
                return this.$store.state.tabbar.list
            },
            set(value) {
                this.$store.commit('tabbar/sort', value)
            }
        },
        dragOptions() {
            return {
                animation: 200,
                ghostClass: 'tab-ghost',
                draggable: '.tab',
                handle: '.drag-handle',
                disabled: this.$store.state.settings.mode == 'mobile'
            }
        }
    },
    watch: {
        // ???????????????????????????????????????
        $route: {
            handler(val) {
                if (this.$store.state.settings.enableTabbar) {
                    this.$store.dispatch('tabbar/add', val).then(() => {
                        if (this.$refs[`tab-${val.meta.activeMenu || val.fullPath}`]) {
                            this.scrollTo(this.$refs[`tab-${val.meta.activeMenu || val.fullPath}`][0].offsetLeft)
                            this.tabbarScrollTip()
                        }
                    })
                }
            },
            immediate: true
        }
    },
    mounted() {
        this.$refs['tabs'].addEventListener('DOMMouseScroll', this.handlerMouserScroll, false)
        this.$refs['tabs'].addEventListener('mousewheel', this.handlerMouserScroll, false)
    },
    beforeDestroy() {
        this.$refs['tabs'].removeEventListener('DOMMouseScroll', this.handlerMouserScroll)
        this.$refs['tabs'].removeEventListener('mousewheel', this.handlerMouserScroll)
    },
    methods: {
        tabbarScrollTip() {
            if (this.$refs['tab-container'].$el.clientWidth > this.$refs['tabs'].clientWidth && !storage.local.has('tabbarScrollTip')) {
                this.$confirm('???????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????', '????????????', {
                    confirmButtonText: '?????????',
                    showCancelButton: false,
                    showClose: false,
                    closeOnClickModal: false,
                    closeOnPressEscape: false,
                    type: 'info',
                    center: true
                }).then(() => {
                    storage.local.set('tabbarScrollTip', true)
                })
            }
        },
        handlerMouserScroll(event) {
            let detail = event.wheelDelta || event.detail
            let moveForwardStep = -1
            let moveBackStep = 1
            let step = 0
            step = detail > 0 ? moveForwardStep * 50 : moveBackStep * 50
            this.$refs['tabs'].scrollBy({
                left: step
            })
        },
        scrollTo(offsetLeft) {
            this.$refs['tabs'].scrollTo({
                left: offsetLeft - 50,
                behavior: 'smooth'
            })
        },
        // ???????????????????????????????????????????????????
        checkOtherSideHasTabCanClose(path = this.activedTabPath) {
            return this.$store.state.tabbar.list.some(item => {
                return !item.isPin && item.path != path
            })
        },
        // ???????????????????????????????????????????????????
        checkLeftSideHasTabCanClose(path = this.activedTabPath) {
            let flag = true
            if (path == this.$store.state.tabbar.list[0].path) {
                flag = false
            } else {
                let index = ~~Object.keys(this.$store.state.tabbar.list).find(i => {
                    return this.$store.state.tabbar.list[i].path == path
                })
                flag = this.$store.state.tabbar.list.some((item, i) => {
                    return i < index && !item.isPin && item.path != path
                })
            }
            return flag
        },
        // ???????????????????????????????????????????????????
        checkRightSideHasTabCanClose(path = this.activedTabPath) {
            let flag = true
            if (path == this.$store.state.tabbar.list[this.$store.state.tabbar.list.length - 1].path) {
                flag = false
            } else {
                let index = ~~Object.keys(this.$store.state.tabbar.list).find(i => {
                    return this.$store.state.tabbar.list[i].path == path
                })
                flag = this.$store.state.tabbar.list.some((item, i) => {
                    return i >= index && !item.isPin && item.path != path
                })
            }
            return flag
        },
        onTabClose(path) {
            this.$tabbarClose(path)
        },
        onOtherSideTabClose(path) {
            // ?????????????????????????????????????????????????????????????????????????????????
            path != this.activedTabPath && this.$router.push(path)
            this.$store.dispatch('tabbar/removeOtherSide', path)
        },
        onLeftSideTabClose(path) {
            // ??????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????
            if (path != this.activedTabPath) {
                let pathIndex = ~~Object.keys(this.$store.state.tabbar.list).find(i => {
                    return this.$store.state.tabbar.list[i].path == path
                })
                let activedPathIndex = ~~Object.keys(this.$store.state.tabbar.list).find(i => {
                    return this.$store.state.tabbar.list[i].path == this.activedTabPath
                })
                if (activedPathIndex < pathIndex) {
                    this.$router.push(path)
                }
            }
            this.$store.dispatch('tabbar/removeLeftSide', path)
        },
        onRightSideTabClose(path) {
            // ??????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????
            if (path != this.activedTabPath) {
                let pathIndex = ~~Object.keys(this.$store.state.tabbar.list).find(i => {
                    return this.$store.state.tabbar.list[i].path == path
                })
                let activedPathIndex = ~~Object.keys(this.$store.state.tabbar.list).find(i => {
                    return this.$store.state.tabbar.list[i].path == this.activedTabPath
                })
                if (activedPathIndex > pathIndex) {
                    this.$router.push(path)
                }
            }
            this.$store.dispatch('tabbar/removeRightSide', path)
        },
        actionCommand(command) {
            switch (command) {
                case 'other-side':
                    this.onOtherSideTabClose(this.activedTabPath)
                    break
                case 'left-side':
                    this.onLeftSideTabClose(this.activedTabPath)
                    break
                case 'right-side':
                    this.onRightSideTabClose(this.activedTabPath)
                    break
            }
        },
        onTabbarContextmenu(event, routeItem) {
            this.$contextmenu({
                items: [
                    {
                        label: '????????????',
                        icon: 'el-icon-refresh',
                        disabled: routeItem.path != this.activedTabPath,
                        onClick: () => {
                            this.reload(1)
                        }
                    },
                    {
                        label: routeItem.isPin ? '????????????' : '??????',
                        divided: true,
                        // ???????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????
                        disabled: routeItem.path == '/workbenchReport',
                        onClick: () => {
                            if (routeItem.isPin) {
                                this.$store.dispatch('tabbar/unPin', routeItem.path)
                            } else {
                                this.$store.dispatch('tabbar/pin', routeItem.path)
                            }
                        }
                    },
                    {
                        label: '???????????????',
                        icon: 'el-icon-close',
                        disabled: this.$store.state.tabbar.list.length <= 1,
                        onClick: () => {
                            this.onTabClose(routeItem.path)
                        }
                    },
                    {
                        label: '?????????????????????',
                        disabled: !this.checkOtherSideHasTabCanClose(routeItem.path),
                        onClick: () => {
                            this.onOtherSideTabClose(routeItem.path)
                        }
                    },
                    {
                        label: '?????????????????????',
                        disabled: !this.checkLeftSideHasTabCanClose(routeItem.path),
                        onClick: () => {
                            this.onLeftSideTabClose(routeItem.path)
                        }
                    },
                    {
                        label: '?????????????????????',
                        disabled: !this.checkRightSideHasTabCanClose(routeItem.path),
                        onClick: () => {
                            this.onRightSideTabClose(routeItem.path)
                        }
                    }
                ],
                event,
                zIndex: 1000
            })
        }
    }
}
</script>

<style lang="scss" scoped>
.tabbar-container {
    position: absolute;
    z-index: 999;
    top: 0;
    height: $g-tabbar-height;
    transition: 0.3s;
    @include themeify {
        background-color: themed('g-tabbar-bg');
    }
    &.fixed {
        position: fixed;
    }
    .tabs {
        position: absolute;
        left: 0;
        right: 0;
        bottom: -1px;
        padding-right: 50px;
        overflow-y: hidden;
        white-space: nowrap;
        // firefox???????????????
        scrollbar-width: none;
        // chrome???????????????
        &::-webkit-scrollbar {
            display: none;
        }
        &.tabs-ontop {
            top: 0;
            bottom: inherit;
        }
    }
    .more-action {
        position: absolute;
        z-index: 10;
        top: 0;
        right: 0;
        display: flex;
        align-items: center;
        height: 100%;
        width: 50px;
        padding-left: 15px;
        @include themeify {
            background: linear-gradient(to right, transparent, themed('g-tabbar-bg'));
        }
        i {
            padding: 4px;
            border-radius: 5px;
            font-size: 16px;
            background-color: #fff;
            box-shadow: 0 0 5px #ccc;
            cursor: pointer;
        }
    }
}
.tab-container {
    display: inline-block;
}
.tab {
    position: relative;
    display: inline-block;
    width: 150px;
    height: 34px;
    line-height: 32px;
    margin-right: -20px;
    vertical-align: bottom;
    font-size: 14px;
    cursor: pointer;
    pointer-events: none;
    transition: transform 0.2s ease, opacity 0.2s ease;
    * {
        user-select: none;
    }
    &:last-child {
        margin-right: 30px;
    }
    &.tab-ontop .tab-background {
        transform: rotate(180deg);
    }
    &:not(.actived) .tab-background {
        opacity: 0;
        > svg .tab-geometry {
            transition: fill 0.2s ease;
        }
    }
    &:not(.tab-dragging):not(.actived):hover,
    &.actived {
        &::before,
        &::after {
            content: none;
        }
        & + .tab .tab-dividers::before {
            opacity: 0;
        }
        .tab-background {
            opacity: 1;
        }
    }
    &:not(.tab-dragging):not(.actived):hover {
        z-index: 3;
        .tab-content {
            .title,
            .action-icon {
                @include themeify {
                    color: themed('g-tabbar-tab-hover-color');
                }
            }
        }
    }
    &.actived {
        z-index: 5;
        .tab-background > svg .tab-geometry {
            @include themeify {
                fill: themed('g-tabbar-tab-active-bg');
            }
        }
        .tab-content {
            .title,
            .action-icon {
                @include themeify {
                    color: themed('g-tabbar-tab-active-color');
                }
            }
        }
    }
    &.tab-ghost {
        opacity: 0;
    }
    .tab-dividers {
        position: absolute;
        z-index: 0;
        top: 10px;
        bottom: 10px;
        left: 9px;
        right: 9px;
        &::before {
            content: "";
            display: block;
            position: absolute;
            top: 0;
            bottom: 0;
            width: 1px;
            opacity: 1;
            transition: opacity 0.2s ease;
            left: 1px;
            @include themeify {
                background-color: themed('g-tabbar-dividers-bg');
            }
        }
    }
    &:first-child .tab-dividers::before {
        opacity: 0;
    }
    .tab-background {
        position: absolute;
        z-index: 1;
        top: -1px;
        left: 0;
        width: 100%;
        height: 100%;
        overflow: hidden;
        pointer-events: none;
        transition: opacity 0.2s ease;
        svg {
            width: 100%;
            height: 100%;
            .tab-geometry {
                @include themeify {
                    fill: themed('g-tabbar-tab-hover-bg');
                }
            }
        }
    }
    .tab-content {
        position: absolute;
        z-index: 5;
        left: 0;
        right: 0;
        height: 100%;
        display: flex;
        pointer-events: all;
        .title {
            position: absolute;
            left: 20px;
            right: 35px;
            flex: 1;
            bottom: 3px;
            mask-image: linear-gradient(90deg, #000 0%, #000 calc(100% - 24px), transparent);
            overflow: hidden;
            white-space: nowrap;
            @include themeify {
                color: themed('g-tabbar-tab-color');
            }
        }
        .drag-handle {
            position: absolute;
            top: 0;
            bottom: 0;
            left: 9px;
            right: 9px;
        }
        .action-icon {
            position: absolute;
            top: 8px;
            right: 18px;
            vertical-align: middle;
            padding: 2px;
            border-radius: 50%;
            font-size: 12px;
            @include themeify {
                color: themed('g-tabbar-tab-color');
            }
            &:hover {
                background-color: #e8eaed;
            }
        }
    }
}
// ???????????????
.tabs {
    .tabbar-enter,
    .tabbar-leave-to {
        opacity: 0;
        transform: translateY(30px);
    }
    .tabbar-leave-active {
        position: absolute;
    }
    &.tabs-ontop {
        .tabbar-enter,
        .tabbar-leave-to {
            transform: translateY(-30px);
        }
    }
}
</style>
