<template>
    <div>
        <!-- <page-main> -->
        <el-row>
            <el-col :md="24" :lg="20">
                <el-form ref="form" :rules="ruleForm" :model="form" label-width="120px">
                    <el-form-item label="配置名称" prop="name">
                        <el-input v-model="form.name" />
                    </el-form-item>
                    <el-form-item label="配置类型" prop="type">
                        <el-radio-group v-model="form.type">
                            <el-radio-button label="0">默认</el-radio-button>
                            <el-radio-button label="1">文件</el-radio-button>
                            <el-radio-button label="2">图片</el-radio-button>
                        </el-radio-group>
                    </el-form-item>
                    <el-form-item label="key" prop="key">
                        <el-input v-model="form.key" />
                    </el-form-item>
                    <el-form-item label="value" prop="value">
                        <el-input v-model="form.value" />
                    </el-form-item>
                    <el-form-item v-if="form.type=='2'" label="上传图片">
                        <image-upload :url.sync="image"
                                      :action="`${actionUrl}api/config/upload`"
                                      accept=".jpg,.jpeg,.png,gif"
                                      :headers="{token:$store.state.user.token}" name="file" :width="250" :height="150" :data="dataObj"
                                      :before-remove="beforeRemove1"
                                      @on-success="handleSuccess1"
                        />
                        <!-- <image-upload :url.sync="image" action="http://scrm.1daas.com/api/upload/upload" name="image" :width="250" :height="150" :data="{'token':'TKD628431923530324'}" @on-success="handleSuccess1" /> -->
                    </el-form-item>
                    <el-form-item v-if="form.type=='1'" label="文件上传">
                        <el-upload ref="upload"
                                   class="upload-demo"
                                   :action="`${actionUrl}api/config/upload`"
                                   :headers="{token:$store.state.user.token}"
                                   name="file"
                                   :limit="1"
                                   accept=".doc,.docx,.xls,xlsx,json"
                                   :data="dataObj1"
                                   :on-success="handleSuccess"
                                   :before-remove="beforeRemove"
                        >
                            <el-button slot="trigger" size="small" type="primary">上传文件</el-button>
                        </el-upload>
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
            image: '',
            fileList: [],
            form: {
                name: '',
                type: 0,
                key: '',
                value: ''
            },
            ruleForm: {
                name: [
                    { required: true, message: '请输入配置名称', trigger: 'blur' }
                    // { min: 3, max: 5, message: '长度在 3 到 5 个字符', trigger: 'blur' }
                ],
                type: [
                    { required: true, message: '请输入配置类型', trigger: 'change' }
                ],
                key: [
                    { required: true, message: '请输入关键字', trigger: 'change' }
                ],
                value: [
                    { required: true, message: '请输入值', trigger: 'change' }
                ]
            },
            actionUrl: process.env.VUE_APP_API_ROOT,
            dataObj: {
                type: 2,
                sign: 'sdsdsd',
                timestamp: this.$store.state.user.failure_time
            },
            dataObj1: {
                type: 1,
                sign: 'sdsdsd',
                timestamp: this.$store.state.user.failure_time
            }
        }
    },
    mounted() {

        // this.$store.commit('settings/setTitle', '编辑页面')
        // //
        // this.$route.meta.title = '编辑页面'
        // this.initData()
        // if (this.$route.name == 'configModuleEdit') {
        //     this.id = this.$route.params.id
        //     this.initData()
        // }
        if (this.dataInfo) {
            this.id = this.dataInfo.id
            this.initData()
        }
    },
    methods: {
        handleSuccess(response) {
            //
            this.form.value = response.data.path
            // this.image = data.data.path
        },
        handleSuccess1(data) {
            this.image = data.data.path
            this.form.value = data.data.path
        },
        beforeRemove(file) {

            // return this.$confirm(`确定移除 ${ file.name }？`)
            this.$confirm(`确定移除 ${ file.name }？`, '提示', {
                confirmButtonText: '确定',
                cancelButtonText: '取消',
                type: 'warning'
            }).then(() => {
                this.form.value = ''
                this.$message({
                    type: 'success',
                    message: '删除成功!'
                })
            }).catch(() => {
                this.$message({
                    type: 'info',
                    message: '已取消删除'
                })
            })

        },
        beforeRemove1(file) {

            // return this.$confirm(`确定移除 ${ file.name }？`)
            this.$confirm(`确定移除 ${ file.name }？`, '提示', {
                confirmButtonText: '确定',
                cancelButtonText: '取消',
                type: 'warning'
            }).then(() => {
                this.form.value = ''
                this.$message({
                    type: 'success',
                    message: '删除成功!'
                })
            }).catch(() => {
                this.$message({
                    type: 'info',
                    message: '已取消删除'
                })
            })

        },
        handleRemove() {},
        beforeUpload() {},
        changeUpload() {},
        initData() {
            this.$api.get(`api/config/${this.id}`)
                .then(res => {
                    //
                    this.form = res.data
                    this.form.value = this.$common.escape2Html(res.data.value)
                })

            // this.$api.get('api/config', {
            //     id: this.id

            // }).then(res => {
            //     //
            //     // this.$message.success({
            //     //     message: `模拟${val ? '启用' : '停用'}成功`,
            //     //     center: true
            //     // })
            // })
        },
        submitForm(formName) {
            this.$refs[formName].validate(valid => {
                if (valid) {
                    //
                    if (this.id) {

                        this.$api.put(`api/config/${this.id}`, this.form)
                            .then(res => {
                                if (res.code === 2000) {
                                    this.$emit('formList')
                                    this.$emit('openEditShow', false)
                                    this.$message.success('新增成功！')
                                }

                            })
                    } else {
                        this.$api.post('api/config', this.form)
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
