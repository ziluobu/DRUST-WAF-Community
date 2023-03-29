<template>
    <div>
        <div class="bg-banner">
            <div class="login-name">
                <img src="../assets/images/login_name1.png">
            </div>

            <div id="login-box">
                <!-- <div class="login-banner" /> -->
                <el-form v-show="formType == 'login'" ref="loginForm" :model="loginForm" :rules="loginRules" size="default"
                         class="login-form" autocomplete="on" label-position="left"
                >
                    <div class="title-container">
                        <h3 class="title">{{ title }}</h3>
                    </div>
                    <div>
                        <el-form-item prop="account" label="用户名">
                            <el-input ref="name" v-model="loginForm.account" :placeholder="$t('app.account')" type="text"
                                      tabindex="1" autocomplete="on"
                            >
                                <svg-icon slot="prefix" name="user" />
                            </el-input>
                        </el-form-item>
                        <el-form-item prop="password" label="密码">
                            <el-input ref="password" v-model="loginForm.password" :type="passwordType"
                                      :placeholder="$t('app.password')" tabindex="2" autocomplete="on"
                                      @keyup.enter.native="handleLogin"
                            >
                                <svg-icon slot="prefix" name="password" />
                                <svg-icon slot="suffix" :name="passwordType === 'password' ? 'eye' : 'eye-open'"
                                          @click="showPassword"
                                />
                            </el-input>
                        </el-form-item>
                    </div>
                    <div class="flex-bar">
                        <el-checkbox v-model="loginForm.remember">记住我</el-checkbox>
                        <!--<el-button type="text" @click="formType = 'reset'">忘记密码</el-button>-->
                    </div>
                    <el-button :loading="loading" type="primary" size="default" style="width: 100%;"
                               @click.native.prevent="handleLogin"
                    >
                        {{ $t('app.login') }}
                    </el-button>
                <!--                <div style="margin-top: 20px; margin-bottom: -10px; color: #666; font-size: 14px; text-align: center; font-weight: bold;">-->
                <!--                    <span style="margin-right: 5px;">演示帐号一键登录：</span>-->
                <!--                    <el-button type="danger" size="mini" @click="testAccount('admin')">admin</el-button>-->
                <!--                    <el-button type="danger" size="mini" plain @click="testAccount('test')">test</el-button>-->
                <!--                </div>-->
                </el-form>
                <el-form v-show="formType == 'reset'" ref="resetForm" :model="resetForm" :rules="resetRules" size="default"
                         class="login-form" auto-complete="on" label-position="left"
                >
                    <div class="title-container">
                        <h3 class="title">重置密码</h3>
                    </div>
                    <div>
                        <el-form-item prop="account">
                            <el-input ref="name" v-model="resetForm.account" :placeholder="$t('app.account')" type="text"
                                      tabindex="1" autocomplete="on"
                            >
                                <svg-icon slot="prefix" name="user" />
                            </el-input>
                        </el-form-item>
                        <el-form-item prop="captcha">
                            <el-input ref="captcha" v-model="resetForm.captcha" :placeholder="$t('app.captcha')" type="text"
                                      tabindex="2" autocomplete="on"
                            >
                                <svg-icon slot="prefix" name="user" />
                                <el-button slot="append">{{ $t('app.sendCaptcha') }}</el-button>
                            </el-input>
                        </el-form-item>
                        <el-form-item prop="newPassword">
                            <el-input ref="newPassword" v-model="resetForm.newPassword" :type="passwordType"
                                      :placeholder="$t('app.newPassword')" tabindex="3" autocomplete="on"
                            >
                                <svg-icon slot="prefix" name="password" />
                                <svg-icon slot="suffix" :name="passwordType === 'password' ? 'eye' : 'eye-open'"
                                          @click="showPassword"
                                />
                            </el-input>
                        </el-form-item>
                    </div>
                    <el-row :gutter="15" style="padding-top: 20px;">
                        <el-col :md="6">
                            <el-button size="default" class="btn-box" style="width: 100%;" @click="formType = 'login'">
                                {{
                                    $t('app.goLogin')
                                }}
                            </el-button>
                        </el-col>
                        <el-col :md="18">
                            <el-button :loading="loading" type="primary" class="btn-box" size="default" style="width: 100%;"
                                       @click.native.prevent="handleFind"
                            >
                                {{ $t('app.check') }}
                            </el-button>
                        </el-col>
                    </el-row>
                </el-form>
            </div>
        </div>
        <Copyright v-if="$store.state.settings.showCopyright" />
    </div>
</template>

<script>
import storage from '@/util/storage'
import Crypto from '@/util/encryp'
// import moment from 'moment'
var deteTime = Date.parse(new Date()) / 1000
export default {
    name: 'Login',
    data() {
        return {
            title: process.env.VUE_APP_TITLE,
            // 表单类型，login 登录，reset 重置密码
            formType: 'login',
            loginForm: {
                account: storage.local.get('login_account'),
                password: '',
                remember: storage.local.has('login_account')
            },
            loginRules: {
                account: [
                    {required: true, trigger: 'blur', message: '请输入用户名'}
                ],
                password: [
                    {required: true, trigger: 'blur', message: '请输入密码'}
                    // { min: 6, max: 18, trigger: 'blur', message: '密码长度为6到18位' }
                ]
            },
            resetForm: {
                account: storage.local.get('login_account'),
                captcha: '',
                newPassword: ''
            },
            resetRules: {
                account: [
                    {required: true, trigger: 'blur', message: '请输入用户名'}
                ],
                captcha: [
                    {required: true, trigger: 'blur', message: '请输入验证码'}
                ],
                newPassword: [
                    {required: true, trigger: 'blur', message: '请输入新密码'}
                    // { min: 6, max: 18, trigger: 'blur', message: '密码长度为6到18位' }
                ]
            },
            loading: false,
            passwordType: 'password',
            redirect: undefined,
            nowTime: '',
            catchData: ''
        }
    },
    watch: {
        $route: {
            handler: function(route) {
                this.redirect = route.query && route.query.redirect
            },
            immediate: true
        }
    },
    mounted() {
        this.getCode()
    },

    methods: {
        getCode() {
            // var time = new Date()

            //
            // getCodeImg().then(res => {
            //     this.captchaOnOff = res.captchaOnOff === undefined ? true : res.captchaOnOff
            //     if (this.captchaOnOff) {
            //         this.codeUrl = 'data:image/gif;base64,' + res.img
            //         this.loginForm.uuid = res.uuid
            //     }
            // })
            this.$api.post('api/captcha', {
                timestamp: deteTime
            }
            )
                .then(res => {
                    //
                    if (res.data) {
                        this.catchData = res.data
                    }

                })
        },
        showPassword() {
            this.passwordType = this.passwordType === 'password' ? '' : 'password'
            this.$nextTick(() => {
                this.$refs.password.focus()
            })
        },
        handleLogin() {
            // this.$refs.loginForm.validate(valid => {
            //     if (valid) {
            //         this.loading = true
            //         this.$store.dispatch('user/login', this.loginForm).then(() => {
            //             this.loading = false
            //             if (this.loginForm.remember) {
            //                 storage.local.set('login_account', this.loginForm.account)
            //             } else {
            //                 storage.local.remove('login_account')
            //             }
            //             this.$router.push({ path: this.redirect || '/largeScreen' })
            //             // this.$router.push({ path: this.redirect || '/' })
            //         }).catch(() => {
            //             this.loading = false
            //         })
            //     }
            // })
            var depassword = Crypto.encrypt(JSON.stringify(this.loginForm.password), 'VbcRf5era08qLir2vzjKeO6lQNHGdXPo', 'VbcRf5era08qLir2')
            this.$refs.loginForm.validate(valid => {
                if (valid) {
                    this.loading = true
                    this.$api.post('api/login', {
                        sign: 'werqew',
                        timestamp: deteTime,
                        username: this.loginForm.account,
                        password: depassword,
                        key: this.catchData.key,
                        captcha: '333'
                    })
                        .then(res => {
                            //
                            if (res.data) {
                                localStorage.setItem('fa_token', res.data.token)
                                this.$store.dispatch('user/login', this.loginForm).then(() => {
                                    this.loading = false
                                    if (this.loginForm.remember) {
                                        storage.local.set('login_account', this.loginForm.account)
                                    } else {
                                        storage.local.remove('login_account')
                                    }
                                    // this.$store.commit('setUserData', res.data)
                                    if (res.msg === '登录成功') {
                                        this.$message.success(res.msg)
                                        this.$router.push({ path: this.redirect || '/workbenchReport' })
                                    } else {
                                        this.$message.error(res.msg)
                                        this.$router.push({ path: this.redirect || '/personal/edit/password' })
                                    }

                                }).catch(() => {
                                    this.loading = false
                                })
                            }

                        })

                }
            })

        },
        handleFind() {
            this.$message({
                message: '重置密码仅提供界面演示，无实际功能，需开发者自行扩展',
                type: 'info'
            })
            this.$refs.resetForm.validate(valid => {
                if (valid) {
                    // 这里编写业务代码
                }
            })
        },
        testAccount(account) {
            this.loginForm.account = account
            this.loginForm.password = 'admin'
            this.handleLogin()
        }
    }
}
</script>

<style lang="scss" scoped>
[data-mode=mobile] {
    #login-box {
        max-width: 80%;
        .login-banner {
            display: none;
        }
    }
}
::v-deep input[type=password]::-ms-reveal {
    display: none;
}
.bg-banner {
    position: absolute;
    z-index: 0;
    width: 100%;
    height: 100%;
    background-image: url(../assets/images/login-bg1.png);
    background-size: cover;
    background-position: center center;
}
.login-name {
    width: 85%;
    margin: auto;
    height: 180px;
    display: flex;
    align-items: center;
    img {
        width: 280px;
    }
}
#login-box {
    display: flex;
    justify-content: space-between;
    position: absolute;
    top: 50%;
    left: 78%;
    transform: translateX(-50%) translateY(-50%);
    background: rgba(255, 255, 255, 0.8);
    border-radius: 10px;
    overflow: hidden;
    box-shadow: 0 0 5px #999;
    .login-banner {
        width: 300px;
        background-image: url(../assets/images/login-banner.jpg);
        background-size: cover;
        background-position: center center;
    }
    .login-form {
        width: 450px;
        padding: 50px 35px 30px;
        overflow: hidden;
        .svg-icon {
            vertical-align: -0.35em;
        }
        .title-container {
            position: relative;
            .title {
                font-size: 22px;
                color: #666;
                margin: 0 auto 30px;
                text-align: center;
                font-weight: bold;
                @include text-overflow;
            }
        }
    }
    ::v-deep .el-input {
        height: 48px;
        line-height: inherit;
        width: 100%;
        input {
            height: 48px;
        }
        .el-input__prefix {
            left: 10px;
        }
        .el-input__suffix {
            right: 10px;
        }
    }
    .flex-bar {
        display: flex;
        justify-content: space-between;
    }
    .el-checkbox {
        margin: 20px 0;
    }
}
.copyright {
    position: absolute;
    bottom: 30px;
    width: 100%;
    margin: 0;
    mix-blend-mode: difference;
}
.btn-box {
    background: #a02929;
}
</style>
<style lang="less" scoped>
/deep/.el-button--primary {
    background: #a02929;
    border-color: #a02929;
}
/deep/.el-checkbox__inner:hover {
    border-color: #a02929;
}
/deep/.el-checkbox__input.is-checked .el-checkbox__inner {
    background: #a02929;
    border-color: #a02929;
}
/deep/.el-checkbox__input.is-checked + .el-checkbox__label {
    color: #a02929;
}
/deep/.el-button--text {
    color: #a02929;
}
/deep/.el-button--text:focus,
.el-button--text:hover {
    color: #a02929;
}
/deep/.el-checkbox__input.is-focus .el-checkbox__inner {
    border-color: #a02929;
}
/deep/.el-form-item {
    margin-bottom: 0;
}
</style>
