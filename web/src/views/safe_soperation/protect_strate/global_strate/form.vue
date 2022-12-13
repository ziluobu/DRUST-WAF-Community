<template>
    <div>
        <!-- <page-main> -->
        <el-row>
            <el-col :md="24">
                <el-form ref="form" :inline="true" class="addform" :rules="ruleForm" :model="form" label-width="120px">
                    <!-- <el-form-item label="网站" prop="web_id">
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
                    </el-form-item> -->
                    <el-form-item label="请求url" prop="request_uri">
                        <el-input v-model="form.request_uri" />
                    </el-form-item>
                    <el-form-item label="请求方法" prop="request_method">
                        <el-select v-model="form.request_method"
                                   multiple
                                   filterable
                                   allow-create
                                   default-first-option placeholder="请选择" clearable
                        >
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
                    <el-form-item label="参数位置" prop="param_site">
                        <el-select v-model="form.param_site" placeholder="请选择" clearable>
                            <el-option label="无" value="0" />
                            <el-option label="请求行" value="1" />
                            <el-option label="请求体" value="2" />
                            <el-option label="header" value="3" />
                        </el-select>
                    </el-form-item>
                    <div v-if="form.param_site !=='0'">
                        <div style="margin-bottom: 10px;">
                            参数内容：
                            <el-button type="primary" icon="el-icon-plus" size="mini" @click="addItem">增加</el-button>
                        </div>
                        <div v-for="(item, index) in form.param_content" :key="index">
                            <el-form-item v-if="form.param_site === '1' || form.param_site === '2'" label="key" :prop="'param_content.' + index + '.key'">
                                <el-input v-model="item.key" />
                            </el-form-item>
                            <el-form-item v-if="form.param_site === '3'" label="key" :prop="'param_content.' + index + '.key'"
                                          :rules="[
                                              {required: true, message: 'key不能为空', trigger: 'blur'},

                                          ]"
                            >
                                <el-select v-model="item.key">
                                    <el-option label="Connection" value="Connection" />
                                    <el-option label="Content-Type" value="Content-Type" />
                                    <el-option label="User-Agent" value="User-Agent" />
                                    <el-option label="Referer" value="Referer" />
                                    <el-option label="Cookie" value="Cookie" />
                                    <el-option label="Origin" value="Origin" />
                                </el-select>
                            </el-form-item>
                            <el-form-item label="operator" :prop="'param_content.' + index + '.operator'"
                                          :rules="[
                                              {required: true, message: 'operator不能为空', trigger: 'blur'},

                                          ]"
                            >
                                <el-select v-model="item.operator">
                                    <el-option label="包含" value="@contains" />
                                    <el-option label="不包含" value="!@contains" />
                                    <el-option label="包含(词)" value="containsWord" />
                                    <el-option label="不包含(词)" value="!containsWord" />
                                    <el-option label="等于" value="@eq" />
                                    <el-option label="不等于" value="!@eq" />
                                    <el-option label="正则" value="@rx" />
                                </el-select>
                            </el-form-item>
                            <el-form-item label="value" :prop="'param_content.' + index + '.value'"
                                          :rules="[
                                              {required: true, message: 'value不能为空', trigger: 'blur'},

                                          ]"
                            >
                                <el-input v-model="item.value" />
                            </el-form-item>
                            <el-form-item>
                                <i class="el-icon-delete" @click="deleteItem(item, index)" />
                            </el-form-item>
                        </div>
                    </div>
                    <el-form-item label="描述" prop="describe">
                        <el-input v-model="form.describe" />
                    </el-form-item>

                    <el-form-item label="类型" prop="type_id">
                        <!-- <el-input v-model="form.type_id" /> -->
                        <el-select v-model="form.type_id" style="width: 100%;"
                                   filterable
                                   allow-create
                                   default-first-option
                                   placeholder="请选择类型"
                        >
                            <el-option v-for="item in roleList"
                                       :key="item.id"
                                       :value="item.id"
                                       :label="item.name"
                            />
                        </el-select>
                    </el-form-item>
                    <div>
                        <el-form-item label="是否加黑" prop="is_black">
                            <!-- <el-switch v-model="form.is_black" /> -->
                            <el-radio-group v-model="form.is_black">
                                <el-radio :label="&quot;0&quot;">不加黑</el-radio>
                                <el-radio :label="&quot;1&quot;">加黑</el-radio>
                            </el-radio-group>
                        </el-form-item>
                        <el-form-item v-if="form.is_black === '1'" label="加黑类型" prop="black_type">
                            <el-radio-group v-model="form.black_type">
                                <el-radio :label="&quot;0&quot;">永久</el-radio>
                                <el-radio :label="&quot;1&quot;">小时</el-radio>
                                <el-radio :label="&quot;2&quot;">天</el-radio>
                            </el-radio-group>
                        </el-form-item>
                        <el-form-item v-if="form.is_black === '1' && form.black_type === '1' || form.black_type === '2' " label="加黑时间" prop="black_num">
                            <el-input v-model="form.black_num" />
                        </el-form-item>
                    </div>
                    <el-form-item label="状态" prop="status">
                        <el-radio-group v-model="form.status">
                            <el-radio :label="&quot;0&quot;">禁用</el-radio>
                            <el-radio :label="&quot;1&quot;">阻断</el-radio>
                            <el-radio :label="&quot;2&quot;">告警</el-radio>
                        </el-radio-group>
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
                param_site: '0',
                describe: '',
                is_black: '0',
                black_type: '0',
                black_num: '',
                type_id: '',
                status: '0',
                param_content: [{
                    operator: '@contains',
                    key: '',
                    value: ''
                }]
                // param_content: {

                // }
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
                operator: [
                    { required: true, message: '请选择operator', trigger: 'change' }
                ],
                type_id: [
                    { required: true, message: '请输入类型', trigger: 'blur' }
                ],
                black_num: [
                    { required: true, message: '请输入加黑时间', trigger: 'blur' }
                ],
                status: [
                    { required: true, message: '请选择状态', trigger: 'change' }
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
        // if (this.$route.name == 'webProtectEdit') {
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
            this.$api.post('api/ruleType/searchList')
                .then(res => {
                    //
                    if (res.data) {
                        this.roleList  = res.data
                    }
                })

            this.$api.post('api/web/searchList')
                .then(res => {
                    //
                    if (res.data) {
                        this.webList  = res.data
                    }
                })

        },
        initData() {
            this.$api.get(`api/globalRules/${this.id}`)
                .then(res => {
                    this.form = res.data
                })
        },
        addItem() {
            this.form.param_content.push({
                key: '',

                operator: '@contains',
                value: ''

            })
        },
        deleteItem(item, index) {
            this.form.param_content.splice(index, 1)
        },
        submitForm(formName) {
            this.$refs[formName].validate(valid => {
                if (valid) {

                    if (this.form.param_site === '0') {
                        this.form.param_content = []
                    }
                    //
                    if (this.id) {
                        this.$api.put(`api/globalRules/${this.id}`, this.form)
                            .then(res => {
                                if (res.code === 2000) {
                                    this.$emit('formList')
                                    this.$emit('openEditShow', false)
                                    this.$message.success('新增成功！')
                                }

                            })
                    } else {
                        this.$api.post('api/globalRules/', this.form)
                            .then(res => {
                                if (res.code === 2000) {
                                    this.$emit('formList')
                                    this.$emit('openEditShow', false)
                                    this.$message.success('新增成功！')
                                }

                            })
                    }
                    // this.$router.push('/safeSoperation/protectStrate/webProtect')
                    // this.$emit('formList')
                    // this.$emit('openEditShow', false)
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
<style lang="less" scoped>
// /deep/.el-form-item__content {
/deep/ .el-icon-delete:nth-child(1) {
    // display: none;
}

</style>
