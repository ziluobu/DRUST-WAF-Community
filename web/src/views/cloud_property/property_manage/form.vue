<template>
    <div>
        <!-- <page-main> -->
        <el-row>
            <el-col :md="24">
                <el-form ref="form" class="addform" :inline="true" :rules="ruleForm" :model="form" label-width="120px">
                    <el-form-item label="单位名称" prop="group_id">
                        <!-- <el-input v-model="form.group_id" /> -->
                        <el-select v-model="form.group_id" style="width: 100%;"

                                   placeholder="请选择单位"
                        >
                            <el-option v-for="item in roleList"
                                       :key="item.id"
                                       :value="item.id"
                                       :label="item.group_name"
                            />
                        </el-select>
                    </el-form-item>
                    <el-form-item label="ip" prop="ip">
                        <el-input v-model="form.ip" />
                    </el-form-item>
                    <el-form-item label="联系人" prop="contact">
                        <el-input v-model="form.contact" />
                    </el-form-item>
                    <el-form-item label="联系方式" prop="phone">
                        <el-input v-model="form.phone" />
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
                group_id: '',
                ip: '',
                contact: '',
                phone: ''
            },
            ruleForm: {
                contact: [
                    { required: true, message: '请输入联系人', trigger: 'blur' },
                    { min: 3, message: '长度最少三个字符', trigger: 'blur' }
                ],
                group_id: [
                    { required: true, message: '请输入类型', trigger: 'change' }
                ],
                phone: [
                    { required: true, message: '请输入手机号', trigger: 'blur' },
                    { pattern: /^((0\d{2,3}-\d{7,8})|(1[3456789]\d{9}))$/, message: '请输入合法手机号/电话号', trigger: 'blur' }
                ],
                ip: [
                    { required: true, message: '请输入IP', trigger: 'blur' }
                ]
            },
            roleList: []
        }
    },
    mounted() {

        // this.$store.commit('settings/setTitle', '编辑页面')
        // //
        // this.$route.meta.title = '编辑页面'
        // this.initData()
        // if (this.$route.name == 'propertyManageEdit') {
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

            // web/searchList
            this.$api.post('api/group/searchList')
                .then(res => {
                    //
                    if (res.data) {
                        this.roleList  = res.data
                    }
                })
        },
        initData() {
            this.$api.get(`api/assets/${this.id}`)
                .then(res => {
                    //
                    this.form = res.data
                })
        },
        submitForm(formName) {
            this.$refs[formName].validate(valid => {
                if (valid) {
                    //
                    if (this.id) {
                        this.$api.put(`api/assets/${this.id}`, this.form)
                            .then(res => {

                                if (res.code === 2000) {
                                    //
                                    this.$emit('formList')
                                    this.$emit('openEditShow', false)
                                    this.$message.success('修改成功！')
                                }
                            })
                    } else {
                        this.$api.post('api/assets', this.form)
                            .then(res => {

                                if (res.code === 2000) {

                                    this.$emit('formList')
                                    this.$emit('openEditShow', false)
                                    this.$message.success('新增成功！')
                                } else {
                                    return false
                                }

                            })
                    }

                    // this.$router.push('/cloudProperty/propertyManageList')

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
