<template>
    <div>
        <!-- <page-main> -->
        <el-row>
            <el-col :md="24">
                <el-form ref="form" :inline="true" :rules="ruleForm" :model="form" label-width="120px">
                    <el-form-item label="是否加黑" prop="is_black">
                        <!-- <el-switch v-model="form.is_black" /> -->
                        <el-radio-group v-model="form.is_black" :readonly="readonly">
                            <el-radio :label="&quot;0&quot;">否</el-radio>
                            <el-radio :label="&quot;1&quot;">是</el-radio>
                        </el-radio-group>
                    </el-form-item>
                    <el-form-item label="加黑类型" prop="black_type">
                        <el-radio-group v-model="form.black_type" :readonly="readonly">
                            <el-radio :label="&quot;0&quot;">永久</el-radio>
                            <el-radio :label="&quot;1&quot;">小时</el-radio>
                            <el-radio :label="&quot;2&quot;">天</el-radio>
                        </el-radio-group>
                    </el-form-item>
                    <el-row>
                        <el-form-item label="加黑个数" prop="black_num">
                            <el-input v-model="form.black_num" :readonly="readonly" />
                        </el-form-item>
                        <el-form-item label="类型" prop="type_id">
                            <el-select v-model="form.type_id" style="width: 100%;"
                                       filterable
                                       allow-create
                                       disabled
                                       default-first-option
                                       :readonly="readonly"
                                       placeholder="请选择类型"
                            >
                                <el-option v-for="item in roleList"
                                           :key="item.id"
                                           :value="item.id"
                                           :label="item.name"
                                />
                            </el-select>
                        </el-form-item>
                    </el-row>
                    <el-form-item label="创建时间" prop="created_at">
                        <el-input v-model="form.created_at" :readonly="readonly" />
                    </el-form-item>
                    <el-form-item label="更新时间" prop="updated_at">
                        <el-input v-model="form.updated_at" :readonly="readonly" />
                    </el-form-item>
                    <el-row>
                        <el-form-item label="规则内容" prop="rule_content">
                            <!-- <el-input type="textarea" v-text="form.rule_content" /> -->
                            <el-input type="textarea" class="tarea"
                                      v-html="keepTextStyle(form.rule_content)"
                            />
                        </el-form-item>
                    </el-row>
                    <el-row>
                        <el-form-item label="黑名单加入规则" prop="black_append_rule">
                            <el-input type="textarea" class="tarea"
                                      v-html="keepTextStyle(form.black_append_rule)"
                            />
                        </el-form-item>
                    </el-row>
                </el-form>
            </el-col>
        </el-row>
        <!-- </page-main> -->
    </div>
</template>
<script>
// import store from '@/store/index'

export default {
    props: {
        idInfo: {
            type: Object
        }
    },
    data() {
        return {
            id: '',
            form: {
                rule_content: '',
                is_black: false,
                black_type: '',
                black_num: '',
                type_id: '',
                created_at: '',
                updated_at: '',
                black_append_rule: ''
            },
            roleList: [],
            ruleForm: {
                // is_black: [
                //     { required: true, message: '请选择是否加黑', trigger: 'change' }
                // ],
                black_type: [
                    { required: true, message: '请输入类型', trigger: 'blur' }
                ],
                black_num: [
                    { required: true, message: '请输入类型', trigger: 'blur' }
                ],
                type_id: [
                    { required: true, message: '请输入类型', trigger: 'blur' }
                ]
                // status: [
                //     { required: true, message: '请选择状态', trigger: 'change' }
                // ]
            },
            readonly: true
        }
    },
    mounted() {
        if (this.idInfo) {
            this.id = this.idInfo
            this.form = this.idInfo
            this.initData()
        }
        this.getRoleList()
        // this.id = this.$route.params.id

    },
    methods: {
        initData() {
            // this.$api.get(`api/sysRules/${this.id}`)
            //     .then(res => {
            //         this.form = res.data
            //     })
        },
        getRoleList() {
            this.$api.post('api/ruleType/searchList')
                .then(res => {
                    //
                    if (res.data) {
                        this.roleList  = res.data
                    }
                })
        },
        submitForm(formName) {
            this.$refs[formName].validate(valid => {
                if (valid) {
                    this.$message.success('新增成功！')
                    //
                    this.$router.push('/safeSoperation/protectStrate/systemStrate')
                } else {
                    //
                    return false
                }
            })
        },
        resetForm(formName) {
            this.$refs[formName].resetFields()
        },
        keepTextStyle(val) {
            //
            //
            //
            return  (val + '').replace(/\n/g, '<br/>')
        }
    }
}
</script>
<style>
.tarea {
    max-width: 800px;

    /* width: 200px; */
    border: 1px solid #dcdfe6;
    padding: 10px;
}
</style>
