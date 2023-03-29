<template>
    <div>
        <!-- <page-main> -->
        <el-row>
            <el-col :md="24" :lg="22">
                <el-form ref="form" :rules="ruleForm" :model="form" label-width="120px">
                    <el-form-item label="单位名称" prop="group_name">
                        <el-input v-model="form.group_name" />
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
                group_name: ''
            },
            ruleForm: {
                group_name: [
                    { required: true, message: '请输入单位名称', trigger: 'blur' }
                    // { min: 3, max: 5, message: '长度在 3 到 5 个字符', trigger: 'blur' }
                ]
            }
        }
    },
    mounted() {

        // this.$store.commit('settings/setTitle', '编辑页面')
        // //
        // this.$route.meta.title = '编辑页面'
        // this.initData()
        // if (this.$route.name == 'unitManageEdit') {
        //     this.id = this.$route.params.id
        //     this.initData()
        // }
        if (this.dataInfo) {
            this.id = this.dataInfo.id
            this.form.group_name = this.dataInfo.group_name
            this.initData()
        }
        //
    },
    methods: {
        initData() {
            // this.$api.get(`api/group/${this.id}`)
            //     .then(res => {
            //         //
            //         this.form = res.data
            //     })
        },
        submitForm(formName) {
            this.$refs[formName].validate(valid => {
                //
                if (valid) {
                    if (this.id) {
                        this.$api.put(`api/group/${this.id}`, this.form)
                            .then(res => {
                                //
                                if (res.code === 2000) {
                                    this.$emit('formList')
                                    this.$emit('openEditShow', false)
                                    this.$message.success('修改成功！')
                                }
                            })
                    } else {
                        this.$api.post('api/group', this.form)
                            .then(res => {
                                if (res.code === 2000) {
                                    this.$emit('formList')
                                    this.$emit('openEditShow', false)
                                    this.$message.success('新增成功！')
                                }

                            })
                    }

                    // this.$router.push('/cloudProperty/unitManageList')

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
