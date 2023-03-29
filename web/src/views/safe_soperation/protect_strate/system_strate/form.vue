<template>
    <div>
        <!-- <page-main> -->
        <el-row>
            <el-col :md="24" :lg="22">
                <el-form ref="form" :rules="ruleForm" :model="form" label-width="120px">
                    <el-form-item label="是否加黑" prop="is_black">
                        <!-- <el-switch v-model="form.is_black" /> -->
                        <el-radio-group v-model="form.is_black">
                            <el-radio :label="&quot;0&quot;">否</el-radio>
                            <el-radio :label="&quot;1&quot;">是</el-radio>
                        </el-radio-group>
                    </el-form-item>
                    <el-row v-if="form.is_black === '1'">
                        <el-form-item label="加黑类型" prop="black_type">
                            <el-radio-group v-model="form.black_type">
                                <el-radio :label="&quot;0&quot;">永久</el-radio>
                                <el-radio :label="&quot;1&quot;">小时</el-radio>
                                <el-radio :label="&quot;2&quot;">天</el-radio>
                            </el-radio-group>
                        </el-form-item>
                        <el-form-item label="加黑时间" prop="black_num">
                            <el-input v-model="form.black_num" />
                        </el-form-item>
                        <!-- <el-form-item label="加黑个数" prop="black_num">
                            <el-input v-model="form.black_num" />
                        </el-form-item> -->
                    </el-row>
                    <el-form-item label="类型" prop="type_id">
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
                        <!-- <el-input v-model="form.type_id" /> -->
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
            type: Object
        }
    },
    data() {
        return {
            id: '',
            form: {
                is_black: '0',
                black_type: '',
                black_num: '',
                type_id: ''
            },
            roleList: [],
            typeBlack: '',
            ruleForm: {
                is_black: [
                    { required: true, message: '请选择是否加黑', trigger: 'change' }
                ],
                black_type: [
                    { required: true, message: '请输入加黑类型', trigger: 'blur' }
                ],
                black_num: [
                    { required: true, message: '请输入加黑个数', trigger: 'blur' }
                ],
                type_id: [
                    { required: true, message: '请输入类型', trigger: 'blur' }
                ]
                // status: [
                //     { required: true, message: '请选择状态', trigger: 'change' }
                // ]
            }
        }
    },
    mounted() {
        if (this.dataInfo) {
            // this.type = 'edit'
            this.id = this.dataInfo.id

            this.initData()
        }
        this.getRoleList()
    },
    methods: {
        initData() {
            this.$api.get(`api/sysRules/${this.id}`)
                .then(res => {
                    this.form = res.data
                })
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

                    this.$api.put(`api/sysRules/${this.id}`, this.form)
                        .then(res => {
                            if (res.code === 2000) {
                                this.$emit('formList')
                                this.$emit('openEditShow', false)
                                this.$message.success('新增成功！')
                            }

                        })
                    //
                    // this.$router.push('/safeSoperation/protectStrate/systemStrate')

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
