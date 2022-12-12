<template>
    <div>
        <!-- <page-main> -->
        <el-row>
            <el-col :md="24" :lg="22">
                <el-form ref="form" :rules="ruleForm" :model="form" label-width="120px">
                    <el-form-item label="用户名" prop="username">
                        <el-input v-model="form.username" />
                    </el-form-item>
                    <el-form-item v-if="!dataInfo" label="密码" prop="password">
                        <el-input v-model="form.password" type="password" />
                    </el-form-item>
                    <el-form-item label="真实姓名" prop="realname">
                        <el-input v-model="form.realname" />
                    </el-form-item>
                    <el-form-item label="邮箱" prop="email">
                        <el-input v-model="form.email" />
                    </el-form-item>
                    <el-form-item label="所属单位">
                        <el-select v-model="form.group_id" style="width: 100%;"
                                   filterable
                                   allow-create
                                   default-first-option
                                   placeholder="请选择单位"
                        >
                            <el-option v-for="item in groupList"
                                       :key="item.id"
                                       :value="item.id"
                                       :label="item.group_name"
                            />
                        </el-select>
                    </el-form-item>
                    <el-form-item label="状态" prop="status">
                        <!-- <el-switch v-model="form.status" /> -->
                        <el-radio-group v-model="form.status">
                            <el-radio :label="1">启用</el-radio>
                            <el-radio :label="0">冻结</el-radio>
                        </el-radio-group>
                    </el-form-item>
                    <el-form-item label="角色">
                        <el-select v-model="form.roleIds" multiple style="width: 100%;"
                                   filterable
                                   allow-create
                                   default-first-option
                                   placeholder="请选择角色"
                        >
                            <el-option v-for="item in roleList"
                                       :key="item.id"
                                       :value="item.id"
                                       :label="item.name"
                            />
                        </el-select>
                    </el-form-item>
                    <el-form-item label="备注" prop="note">
                        <el-input v-model="form.note" type="textarea" />
                    </el-form-item>
                </el-form>
            </el-col>
        </el-row>
        <!-- </page-main> -->
        <!-- <fixed-action-bar>
            <el-button type="primary" @click="submitForm('form')">确定</el-button>
            <el-button type="info" @click="resetForm('form')">取消</el-button>
        </fixed-action-bar> -->
        <div class="btn-box">
            <el-button type="primary" @click="submitForm('form')">确定</el-button>
            <el-button type="info" @click="resetForm('form')">取消</el-button>
        </div>
    </div>
</template>
<script>
// import store from '@/store/index'

export default {
    props: {
        dataInfo: {
            type: String,
            default: ''
        }
    },
    data() {
        return {
            id: '',
            type: 'add',
            form: {
                username: '',
                password: '',
                realname: '',
                email: '',
                status: 1,
                note: '',
                roleIds: [],
                group_id: '0'
            },
            ruleForm: {
                username: [
                    { required: true, message: '请输入用户名', trigger: 'blur' }
                    // { min: 3, max: 5, message: '长度在 3 到 5 个字符', trigger: 'blur' }
                ],
                password: [
                    { required: true, message: '请输入类型', trigger: 'change' }
                ],
                realname: [
                    { required: true, message: '请输入关键字', trigger: 'change' }
                ],
                status: [
                    { required: true, message: '请选择状态', trigger: 'change' }
                ]
            },
            groupList: [],
            roleList: []
        }
    },
    mounted() {

        // this.$store.commit('settings/setTitle', '编辑页面')
        // //
        // this.$route.meta.title = '编辑页面'
        // this.initData()
        // if (this.$route.name == 'adminModuleEdit') {
        //     this.id = this.$route.params.id
        //     this.type = 'edit'
        //     //
        //     this.initData()
        // }
        // if (this.dataInfo) {
        //     this.type = 'edit'
        //     this.id = this.dataInfo.id
        //     this.initData()
        // }

        if (this.dataInfo) {
            this.id = this.dataInfo.id
            this.initData()
        }
        this.getRoleList()
    },
    methods: {
        getRoleList() {
            this.$api.post('api/role/searchList')
                .then(res => {
                    //
                    if (res.data) {
                        this.roleList  = res.data
                    }
                })
            this.$api.post('api/group/searchList')
                .then(res => {
                    //
                    if (res.data) {
                        this.groupList  = res.data
                    }
                })
        },
        initData() {
            this.$api.get(`api/manage/${this.id}`)
                .then(res => {
                    //

                    if (res.data) {
                        this.form = res.data
                        this.form.status = +res.data.status
                    }
                })
        },
        submitForm(formName) {
            //
            this.$refs[formName].validate(valid => {
                if (valid) {
                    if (this.id) {
                        this.$api.put(`api/manage/${this.id}`, this.form)
                            .then(res => {
                                //
                                if (res.code === 2000) {
                                    this.$emit('formList')
                                    this.$emit('openEditShow', false)
                                    this.$message.success('修改成功！')
                                }
                            })
                    } else {
                        this.$api.post('api/manage', this.form)
                            .then(res => {
                                if (res.code === 2000) {
                                    this.$emit('formList')
                                    this.$emit('openEditShow', false)
                                    this.$message.success('新增成功！')
                                }

                            })
                    }

                    // this.$router.push('/systemManage/configModuleList')

                } else {
                    //
                    return false
                }
            })
        },
        resetForm(formName) {
            this.$refs[formName].resetFields()
            this.$emit('openEditShow', false)
        }
    }
}
</script>

