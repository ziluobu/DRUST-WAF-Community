<template>
    <div>
        <page-header title="修改密码" content="定期修改密码可以提高帐号安全性噢~" />
        <page-main>
            <el-row>
                <el-col :md="24" :lg="12">
                    <el-form ref="form" :model="form" :rules="rules" label-width="120px">
                        <el-form-item label="原密码" prop="old_password">
                            <el-input v-model="form.old_password" type="password" placeholder="请输入原密码" />
                        </el-form-item>
                        <el-form-item label="新密码" prop="password">
                            <el-input v-model="form.password" type="password" placeholder="请输入原密码" />
                        </el-form-item>
                        <el-form-item label="确认新密码" prop="password_confirmation">
                            <el-input v-model="form.password_confirmation" type="password" placeholder="请输入原密码" />
                        </el-form-item>
                    </el-form>
                </el-col>
            </el-row>
        </page-main>
        <fixed-action-bar>
            <el-button type="primary" @click="onSubmit">提交</el-button>
        </fixed-action-bar>
    </div>
</template>

<script>
export default {
    data() {
        const validatePassword = (rule, value, callback) => {
            if (value !== this.form.password) {
                callback(new Error('请确认新密码'))
            } else {
                callback()
            }
        }
        return {
            form: {
                old_password: '',
                password: '',
                password_confirmation: ''
            },
            rules: {
                old_password: [
                    { required: true, message: '请输入原密码', trigger: 'blur' }
                ],
                password: [
                    { required: true, message: '请输入新密码', trigger: 'blur' },
                    { min: 5, max: 18, trigger: 'blur', message: '密码长度为6到18位' }
                ],
                password_confirmation: [
                    { required: true, message: '请输入新密码', trigger: 'blur' },
                    { validator: validatePassword }
                ]
            }
        }
    },
    methods: {
        onSubmit() {
            // api/updatePwd
            this.$refs['form'].validate(valid => {
                if (valid) {
                    this.loading = true
                    this.$api.post('api/updatePwd', this.form)
                        .then(res => {
                            //
                            if (res.data) {
                                this.$message({
                                    type: 'success',
                                    message: '修改成功，请重新登录'
                                })
                                this.$store.dispatch('user/logout').then(() => {
                                    this.$router.push('/login')
                                })
                            }

                        })
                    // this.$store.dispatch('user/editPassword', this.form).then(() => {
                    //     this.$message({
                    //         type: 'success',
                    //         message: '修改成功，请重新登录'
                    //     })
                    //     this.$store.dispatch('user/logout').then(() => {
                    //         this.$router.push('/login')
                    //     })
                    // }).catch(() => {})
                }
            })
        }
    }
}
</script>
