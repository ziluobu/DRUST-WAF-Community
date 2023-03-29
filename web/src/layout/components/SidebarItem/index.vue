<template>
    <div v-if="item.meta.sidebar !== false" class="sidebar-item">
        <!-- <router-link v-if="!hasChildren" v-slot="{ href, navigate, isActive, isExactActive }" custom :to="resolvePath(item.path)">
            <a :href="isExternal(resolvePath(item.path)) ? resolvePath(item.path) : href" :class="[isActive && 'router-link-active', isExactActive && 'router-link-exact-active']" :target="isExternal(resolvePath(item.path)) ? '_blank' : '_self'" @click="navigate">
                <el-menu-item :title="generateI18nTitle(item.meta.i18n, item.meta.title)" :index="resolvePath(item.path)">
                    <svg-icon v-if="iconName(isActive || isExactActive, item.meta.icon, item.meta.activeIcon)" :name="iconName(isActive || isExactActive, item.meta.icon, item.meta.activeIcon)" class="icon" />
                    <span>{{ generateI18nTitle(item.meta.i18n, item.meta.title) }}</span>
                    <span v-if="badge(item.meta.badge).visible" :class="{
                        'badge': true,
                        'badge-dot': badge(item.meta.badge).type == 'dot',
                        'badge-text': badge(item.meta.badge).type == 'text'
                    }"
                    >{{ badge(item.meta.badge).value }}</span>
                </el-menu-item>
            </a>
        </router-link>
        <router-link v-else v-slot="{ isActive, isExactActive }" custom :to="resolvePath(item.path)">
            <el-submenu :title="generateI18nTitle(item.meta.i18n, item.meta.title)" :index="resolvePath(item.path)">
                <template slot="title">
                    <svg-icon v-if="iconName(isActive || isExactActive, item.meta.icon, item.meta.activeIcon)" :name="iconName(isActive || isExactActive, item.meta.icon, item.meta.activeIcon)" class="icon" />
                    <span>{{ generateI18nTitle(item.meta.i18n, item.meta.title) }}</span>
                    <span v-if="badge(item.meta.badge).visible" :class="{
                        'badge': true,
                        'badge-text': badge(item.meta.badge).type == 'text',
                        'badge-dot': badge(item.meta.badge).type == 'dot'
                    }"
                    >{{ badge(item.meta.badge).value }}</span>
                </template>
                <SidebarItem v-for="route in item.children" :key="route.path" :item="route" :base-path="resolvePath(item.path)" />
            </el-submenu>
        </router-link> -->
        <router-link v-if="!hasChildren" v-slot="{ href, navigate, isActive, isExactActive }" custom :to="resolvePath(item.path)">
            <a :href="isExternal(resolvePath(item.path)) ? resolvePath(item.path) : href" :class="[isActive && 'router-link-active', isExactActive && 'router-link-exact-active']" :target="isExternal(resolvePath(item.component)) ? '_blank' : '_self'" @click="navigate">
                <el-menu-item :title="generateI18nTitle(item.meta.i18n, item.meta.title)" :index="resolvePath(item.path)">
                    <svg-icon v-if="iconName(isActive || isExactActive, item.meta.icon, item.meta.activeIcon)" :name="iconName(isActive || isExactActive, item.meta.icon, item.meta.activeIcon)" class="icon" />
                    <span>{{ generateI18nTitle(item.meta.i18n, item.meta.title) }}</span>
                    <span v-if="badge(item.meta.badge).visible" :class="{
                        'badge': true,
                        'badge-dot': badge(item.meta.badge).type == 'dot',
                        'badge-text': badge(item.meta.badge).type == 'text'
                    }"
                    >{{ badge(item.meta.badge).value }}</span>
                </el-menu-item>
            </a>
        </router-link>
        <router-link v-else v-slot="{ isActive, isExactActive }" custom :to="resolvePath(item.path)">
            <el-submenu :title="generateI18nTitle(item.meta.i18n, item.meta.title)" :index="resolvePath(item.component)">
                <template slot="title">
                    <svg-icon v-if="iconName(isActive || isExactActive, item.meta.icon, item.meta.activeIcon)" :name="iconName(isActive || isExactActive, item.meta.icon, item.meta.activeIcon)" class="icon" />
                    <span>{{ generateI18nTitle(item.meta.i18n, item.meta.title) }}</span>
                    <span v-if="badge(item.meta.badge).visible" :class="{
                        'badge': true,
                        'badge-text': badge(item.meta.badge).type == 'text',
                        'badge-dot': badge(item.meta.badge).type == 'dot'
                    }"
                    >{{ badge(item.meta.badge).value }}</span>
                </template>
                <SidebarItem v-for="route in item.children" :key="route.component" :item="route" :base-path="resolvePath(item.component)" />
            </el-submenu>
        </router-link>
    </div>
</template>

<script>
import path from 'path'

export default {
    name: 'SidebarItem',
    inject: ['generateI18nTitle'],
    props: {
        item: {
            type: Object,
            required: true
        },
        basePath: {
            type: String,
            default: ''
        }
    },
    data() {
        return {
            itemList: []
        }
    },
    computed: {
        hasChildren() {
            let flag = true
            if (this.item.children) {
                if (this.item.children.every(item => item.meta.sidebar === false)) {
                    flag = false
                }
            } else {
                flag = false
            }
            return flag

        }
    },
    created() {},
    mounted() {
        // console.log(this.item, 'dd')
        // let arry = this.item;
        // arry.map(item => {
        //     this.itemList.push(item)
        // })
    },
    methods: {
        isExternal(path) {
            return /^(https?:|mailto:|tel:)/.test(path)
        },
        resolvePath(routePath) {
            if (this.isExternal(routePath)) {
                return routePath
            }
            if (this.isExternal(this.basePath)) {
                return this.basePath
            }
            return path.resolve(this.basePath, routePath)
        },
        iconName(isActive, icon, activeIcon) {
            let name = ''
            if ((!isActive && icon) || (isActive && !activeIcon)) {
                name = icon
            } else if (isActive && activeIcon) {
                name = activeIcon
            }
            return name
        },
        badge(badge) {
            let res = {
                type: '', // text or dot
                value: '',
                visible: false
            }
            if (badge) {
                res.visible = true
                res.value = typeof badge == 'function' ? badge() : badge
                if (typeof res.value == 'boolean') {
                    res.type = 'dot'
                    if (!res.value) {
                        res.visible = false
                    }
                } else if (typeof res.value == 'number') {
                    res.type = 'text'
                    if (res.value <= 0) {
                        res.visible = false
                    }
                } else {
                    res.type = 'text'
                    if (!res.value) {
                        res.visible = false
                    }
                }
            }
            return res
        }
    }
}
</script>

<style lang="scss" scoped>
::v-deep .el-menu-item,
::v-deep .el-menu-item span,
::v-deep .el-submenu__title,
::v-deep .el-submenu__title span {
    vertical-align: inherit;
    @include text-overflow;
}
::v-deep .el-menu-item,
::v-deep .el-submenu__title {
    display: flex;
    align-items: center;
}
::v-deep .el-submenu,
::v-deep .el-menu-item {
    .icon {
        width: 20px;
        font-size: 20px;
        margin-right: 10px;
        vertical-align: -0.25em;
        transition: transform 0.3s;
        color: unset;
        &[class^=el-icon-] {
            vertical-align: middle;
        }
    }
    &:hover > .icon,
    .el-submenu__title:hover > .icon {
        transform: scale(1.2);
    }
}
a {
    cursor: pointer;
    color: inherit;
    text-decoration: none;
}
.badge {
    position: absolute;
    z-index: 1;
    @include themeify {
        background-color: themed('g-badge-bg');
        box-shadow: 0 0 0 1px themed('g-badge-border-color');
    }
    @include position-center(y);
    &-dot {
        right: 15px;
        text-indent: -9999px;
        border-radius: 50%;
        width: 6px;
        height: 6px;
    }
    &-text {
        right: 15px;
        border-radius: 10px;
        font-size: 12px;
        height: 18px;
        line-height: 18px;
        padding: 0 6px;
        text-align: center;
        white-space: nowrap;
        @include themeify {
            color: themed('g-badge-color');
        }
    }
}
.el-submenu__title {
    > .badge {
        &-dot {
            right: 40px;
        }
        &-text {
            right: 40px;
        }
    }
}
</style>

<style lang="scss">
.el-menu--inline {
    @include themeify {
        background-color: themed('g-sub-sidebar-menu-bg') !important;
    }
    .el-menu-item,
    .el-submenu > .el-submenu__title {
        @include themeify {
            color: themed('g-sub-sidebar-menu-color');
            background-color: themed('g-sub-sidebar-menu-bg') !important;
        }
        &:hover {
            @include themeify {
                color: themed('g-sub-sidebar-menu-hover-color') !important;
                background-color: themed('g-sub-sidebar-menu-hover-bg') !important;
            }
        }
    }
}
.el-menu-item,
.el-submenu__title {
    @include themeify {
        color: themed('g-sub-sidebar-menu-color');
    }
    &:hover {
        @include themeify {
            color: themed('g-sub-sidebar-menu-hover-color') !important;
            background-color: themed('g-sub-sidebar-menu-hover-bg') !important;
        }
    }
}
.el-menu--popup {
    @include themeify {
        background-color: themed('g-sub-sidebar-bg');
    }
}
.el-menu-item.is-active,
.el-submenu .el-menu--inline .el-menu-item.is-active {
    @include themeify {
        color: themed('g-sub-sidebar-menu-active-color') !important;
        background-color: themed('g-sub-sidebar-menu-active-bg') !important;
    }
}
.el-menu--collapse .el-submenu.is-active > .el-submenu__title {
    @include themeify {
        color: themed('g-sub-sidebar-menu-active-color') !important;
        background-color: themed('g-sub-sidebar-menu-active-bg') !important;
    }
}
</style>
