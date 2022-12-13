<template>
    <div>
        <!-- <page-main> -->
        <el-row>
            <el-col :md="24">
                <el-form ref="form" :inline="true" :rules="ruleForm" class="addform" :model="form" label-width="120px">
                    <el-form-item label="IP" prop="ip">
                        <el-input v-model="form.ip" readonly />
                    </el-form-item>
                    <el-form-item label="原因" prop="reason">
                        <el-input v-model="form.reason" readonly />
                    </el-form-item>
                    <el-form-item label="过期时间" prop="expire_time">
                        <el-date-picker v-model="form.expire_time" type="date" readonly />
                    </el-form-item>
                    <el-form-item label="管理员ID" prop="admin_id">
                        <el-input v-model="form.admin_id" readonly />
                    </el-form-item>
                    <el-form-item label="创建时间" prop="created_at">
                        <el-input v-model="form.created_at" readonly />
                    </el-form-item>
                    <el-form-item label="更新时间" prop="updated_at">
                        <el-input v-model="form.updated_at" readonly />
                    </el-form-item>
                </el-form>
            </el-col>
        </el-row>
        <div class="btn-box">
            <!-- <el-button type="primary" @click="submitForm('form')">确定</el-button> -->
            <el-button @click="resetForm('form')">取消</el-button>
        </div>
        <!-- </page-main> -->
    </div>
</template>
<script>
// import store from '@/store/index'

export default {
    props: {
        idInfo: {
            type: String,
            default: ''
        }
    },
    data() {
        return {
            id: '',
            form: {
                ip: '',
                reason: '',
                admin_id: '',
                expire_time: '',
                created_at: '',
                updated_at: ''
            },
            ruleForm: {
                ip: [
                    { required: true, message: '请输入IP地址', trigger: 'blur' }
                ],
                reason: [
                    { required: true, message: '请输入原因', trigger: 'blur' }
                ],
                expire_time: [
                    { required: true, message: '请选择过期时间', trigger: 'change' }
                ]
            }
        }
    },
    mounted() {
        this.id = this.$route.params.id
        this.initData()
    },
    methods: {
        initData() {
            this.$api.get(`api/ipallow/${this.id}`)
                .then(res => {
                    this.form = res.data
                })
        },
        resetForm(formName) {
            this.$refs[formName].resetFields()
            // this.openEditHadnle = false
            this.$emit('openEditShow', false)
        }
    }
}
</script>
