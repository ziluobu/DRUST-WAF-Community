<template>
    <div class="user">
        <div class="tools">
            <span class="item" @click="fullscreen">
                <i class="el-icon-s-platform" />
            </span>
            <span v-if="$store.state.settings.enableNavSearch" class="item"
                  @click="$eventBus.$emit('global-search-toggle')"
            >
                <i class="ri-search-line" />
            </span>
            <el-popover v-if="$store.state.settings.enableNotification" v-model="notifyVisible" trigger="hover"
                        :open-delay="200" placement="bottom" width="350" @show="handlePopoverShow"
            >
                <Notification ref="tabs" />
                <template slot="reference">
                    <span class="item">
                        <el-badge :value="5">
                            <i class="ri-notification-3-line" />
                        </el-badge>
                    </span>
                </template>
            </el-popover>
            <el-dropdown v-if="$store.state.settings.enableI18n" class="language-container" size="medium"
                         @command="languageCommand"
            >
                <span class="item">
                    <i class="ri-translate" />
                </span>
                <el-dropdown-menu slot="dropdown">
                    <el-dropdown-item :disabled="$store.state.settings.defaultLang == 'zh-CN'" command="zh-CN">
                        中文(简体)
                    </el-dropdown-item>
                    <el-dropdown-item :disabled="$store.state.settings.defaultLang == 'zh-TW'" command="zh-TW">
                        中文(繁體)
                    </el-dropdown-item>
                    <el-dropdown-item :disabled="$store.state.settings.defaultLang == 'en'" command="en">
                        英文
                    </el-dropdown-item>
                </el-dropdown-menu>
            </el-dropdown>
            <span
                v-if="$store.state.settings.mode == 'pc' && isFullscreenEnable && $store.state.settings.enableFullscreen"
                class="item" @click="fullscreen"
            >
                <i :class="isFullscreen ? 'ri-fullscreen-exit-line' : 'ri-fullscreen-line'" />
            </span>
            <span v-if="$store.state.settings.enablePageReload" class="item"
                  @click="reload($store.state.settings.enableTabbar ? 1 : 2)"
            >
                <svg-icon name="toolbar-reload" />
            </span>
            <!-- <span v-if="$store.state.settings.enableThemeSetting" class="item"
                  @click="$eventBus.$emit('global-theme-toggle')"
            >
                <svg-icon name="toolbar-theme" />
            </span> -->
        </div>
        <el-dropdown size="default" class="user-container" @command="userCommand">
            <div class="user-wrapper">
                <el-avatar size="medium">
                    <i class="el-icon-user-solid" />
                </el-avatar>
                {{ $store.state.user.account }}
                <i class="el-icon-caret-bottom" />
            </div>
            <el-dropdown-menu slot="dropdown" class="user-dropdown">
                <!-- <el-dropdown-item v-if="$store.state.settings.enableDashboard" command="dashboard">
                    {{ $t('route.dashboard') }}
                </el-dropdown-item> -->
                <!-- <el-dropdown-item command="setting">{{ $t('app.profile') }}</el-dropdown-item> -->
                <!-- <el-dropdown-item divided command="logout">{{ $t('app.logout') }}</el-dropdown-item> -->
                <el-dropdown-item command="editPass">{{ $t('route.personal.editpassword') }}</el-dropdown-item>
                <el-dropdown-item>
                    <div @click="getRestule">刷新缓存</div>
                </el-dropdown-item>
                <el-dropdown-item divided command="logout">{{ $t('app.logout') }}</el-dropdown-item>
            </el-dropdown-menu>
        </el-dropdown>
    </div>
</template>

<script>
import screenfull from 'screenfull'
import Notification from '../Notification'

export default {
    name: 'UserMenu',
    components: {
        Notification
    },
    inject: ['reload', 'generateI18nTitle'],
    data() {
        return {
            isFullscreenEnable: screenfull.isEnabled,
            isFullscreen: false,
            notifyVisible: false,
            openScreen: false
        }
    },
    mounted() {
        if (screenfull.isEnabled) {
            screenfull.on('change', this.fullscreenChange)
        }
    },
    beforeDestroy() {
        if (screenfull.isEnabled) {
            screenfull.off('change', this.fullscreenChange)
        }
    },
    methods: {
        // 解决 tabs 组件嵌套在 popover 组件里使用时，高亮效果有 bug ，所以做以下特殊处理
        // https://github.com/ElemeFE/element/issues/19357
        handlePopoverShow() {
            this.$refs.tabs.$refs.tabs.calcPaneInstances(true)
        },
        fullscreen() {

            // this.$router.replace({name: '/largeScreen'})
            const { href } = this.$router.resolve({
                name: 'largeScreen'

            })
            window.open(href, '_blank')
            // screenfull.toggle()

            // const newPath = this.$router.resolve({name: '/largeScreen'})
            // window.open(newPath.href, '_blank')
            // this.$store.commit('settings/enableLargeScreen', true)
        },
        fullscreenChange() {
            this.isFullscreen = screenfull.isFullscreen
        },
        languageCommand(command) {
            this.$i18n.locale = command
            this.$store.commit('settings/setDefaultLang', command)
            this.$route.meta.title && this.$store.commit('settings/setTitle', this.generateI18nTitle(this.$route.meta.i18n, this.$route.meta.title))
        },
        userCommand(command) {
            switch (command) {
                case 'dashboard':
                    this.$router.push({
                        name: 'dashboard'
                    })
                    break
                case 'setting':
                    this.$router.push({
                        name: 'personalSetting'
                    })
                    break
                case 'editPass':
                    this.$router.push({
                        name: 'personalEditPassword'
                    })
                    break
                case 'logout':
                    this.$store.dispatch('user/logout').then(() => {
                        this.$router.push({
                            name: 'login'
                        })
                    })
                    break
            }
        },
        getRestule() {
            this.$api.post('api/refreshCache')
                .then(res => {
                    // console.log(res)
                    if (res.data) {
                        this.$message.success('操作成功！')
                    }
                })
        }
    }
}
</script>

<style lang="scss" scoped>
.user {
    display: flex;
    align-items: center;
    padding: 0 20px;
    white-space: nowrap;
}
.tools {
    margin-right: 20px;
    .item {
        margin-left: 5px;
        padding: 6px 8px;
        border-radius: 5px;
        outline: none;
        cursor: pointer;
        vertical-align: middle;
        transition: all 0.3s;
        [class^=ri-] {
            vertical-align: -0.15em;
        }
        .el-badge {
            vertical-align: initial;
        }
    }
}
.language-container {
    font-size: 16px;
}
.user-container {
    display: inline-block;
    height: 50px;
    line-height: 50px;
    cursor: pointer;
    .user-wrapper {
        .el-avatar {
            vertical-align: middle;
            margin-top: -2px;
            margin-right: 4px;
        }
    }
}
</style>
