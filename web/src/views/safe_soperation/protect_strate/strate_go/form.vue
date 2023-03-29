<template>
    <div>
        <!-- <page-main> -->
        <el-row>
            <el-col :md="24">
                <el-form ref="form" :inline="true" class="addform" :rules="ruleForm" :model="form" label-width="120px">
                    <el-form-item label="网站" prop="web_id">
                        <el-select v-model="form.web_id" style="width: 100%;"
                                   filterable
                                   allow-create
                                   default-first-option
                                   placeholder="请选择网站"
                        >
                            <el-option v-for="item in webList"
                                       :key="item.id"
                                       :value="item.id"
                                       :label="item.name"
                            />
                        </el-select>
                        <!-- <el-input v-model="form.web_id" /> -->
                    </el-form-item>
                    <el-form-item label="请求url" prop="request_uri">
                        <el-input v-model="form.request_uri" />
                    </el-form-item>
                    <el-form-item label="请求方法" prop="request_method">
                        <!-- collapse-tags -->
                        <el-select v-model="form.request_method" placeholder="请选择" multiple clearable>
                            <el-option label="GET" value="GET" />
                            <el-option label="POST" value="POST" />
                            <el-option label="OPTIONS" value="OPTIONS" />
                            <el-option label="HEAD" value="HEAD" />
                            <el-option label="PUT" value="PUT" />
                            <el-option label="DELETE" value="DELETE" />
                            <el-option label="TRACE" value="TRACE" />
                            <el-option label="CONNECT" value="CONNECT" />
                        </el-select>
                    </el-form-item>
                    <div>
                        <el-form-item label="移除系统规则" prop="remove_sysrule_id">
                            <!-- <el-input v-model="form.remove_sysrule_id" /> -->
                            <el-select v-model="form.remove_sysrule_id" style="width: 100%;"
                                       filterable
                                       allow-create
                                       default-first-option
                                       placeholder="请选择网站"
                            >
                                <el-option v-for="item in roleList"
                                           :key="item"
                                           :value="item"
                                           :label="item"
                                />
                            </el-select>
                        </el-form-item>
                        <el-form-item label="状态" prop="status">
                            <el-radio-group v-model="form.status">
                                <el-radio :label="&quot;0&quot;">禁用</el-radio>
                                <el-radio :label="&quot;1&quot;">开启</el-radio>
                            </el-radio-group>
                        <!-- <el-radio-group v-model="form.status">
                                <el-radio-button label="0">禁用</el-radio-button>
                                <el-radio-button label="1">阻断</el-radio-button>
                                <el-radio-button label="2">告警</el-radio-button>
                            </el-radio-group> -->
                        </el-form-item>
                    </div>
                    <el-form-item label="描述" prop="describe">
                        <el-input v-model="form.describe" type="textarea" />
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
            form: {
                web_id: '',
                request_uri: '',
                request_method: [],
                describe: '',
                remove_sysrule_id: '',
                status: '0'
            },
            ruleForm: {
                web_id: [
                    { required: true, message: '请输入网站', trigger: 'blur' }
                ],
                param_site: [
                    { required: true, message: '请输入参数', trigger: 'blur' }
                ],
                describe: [
                    { required: true, message: '请输入描述', trigger: 'blur' }
                ],
                remove_sysrule_id: [
                    { required: true, message: '请输入移除规则', trigger: 'blur' }
                ],
                type_id: [
                    { required: true, message: '请输入响应路径', trigger: 'blur' }
                ],
                request_uri: [
                    { required: true, message: '请输入请求url', trigger: 'blur' }
                ],
                status: [
                    { required: true, message: '请选择状态', trigger: 'blur' }
                ]
            },
            roleList: [],
            webList: []
        }
    },
    mounted() {

        // this.$store.commit('settings/setTitle', '编辑页面')
        // //
        // this.$route.meta.title = '编辑页面'
        // this.initData()
        // if (this.$route.name == 'strateGoEdit') {
        //     this.id = this.$route.params.id
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
            this.$api.post('api/web/searchList')
                .then(res => {
                    //
                    if (res.data) {
                        this.webList  = res.data
                    }
                })
            this.$api.post('api/sysRules/searchList')
                .then(res => {
                    //
                    if (res.data) {
                        this.roleList  = res.data
                    }
                })
        },
        initData() {
            this.$api.get(`api/whiteRules/${this.id}`)
                .then(res => {
                    this.form = res.data
                })
        },
        submitForm(formName) {
            this.$refs[formName].validate(valid => {
                if (valid) {

                    //
                    if (this.id) {
                        this.$api.put(`api/whiteRules/${this.id}`, this.form)
                            .then(res => {
                                if (res.code === 2000) {
                                    this.$emit('formList')
                                    this.$emit('openEditShow', false)
                                    this.$message.success('新增成功！')
                                }

                            })
                    } else {
                        this.$api.post('api/whiteRules/', this.form)
                            .then(res => {
                                if (res.code === 2000) {
                                    this.$emit('formList')
                                    this.$emit('openEditShow', false)
                                    this.$message.success('新增成功！')
                                }

                            })
                    }
                    // this.$router.push('/safeSoperation/protectStrate/strateGo')

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
