<template>
    <div>
        <!-- <page-main> -->
        <el-row>
            <!-- :lg="12" -->
            <el-col :md="24">
                <el-form ref="form" :inline="true" :rules="ruleForm" :model="form" label-width="120px" class="formBox">
                    <el-form-item label="系统名称" prop="web_sysname">
                        <el-input v-model="form.web_sysname" />
                    </el-form-item>
                    <el-form-item label="防护域名" prop="web_name">
                        <el-input v-model="form.web_name" />
                    </el-form-item>
                    <el-form-item label="源端口" prop="web_port">
                        <el-input v-model="form.web_port" />
                    </el-form-item>
                    <el-form-item label="源站地址" prop="source_ip">
                        <el-input v-model="form.source_ip" />
                    </el-form-item>
                    <!-- <el-form-item label="源端口" prop="web_port">
                        <el-input v-model="form.web_port" />
                    </el-form-item> -->
                    <el-form-item label="目的端口" prop="dst_port">
                        <el-input v-model="form.dst_port" />
                    </el-form-item>
                    <div>
                        <el-form-item label="是否开启https" prop="is_https" style="width: 32.5%;">
                            <!--<div style="width: 190px;">-->
                            <!-- <el-switch v-model="form.is_https"  /> -->
                            <el-radio-group v-model="form.is_https">
                                <el-radio :label="0">关闭</el-radio>
                                <el-radio :label="1">开启</el-radio>
                            </el-radio-group>
                            <!--</div>-->
                        </el-form-item>
                        <el-form-item label="所属单位" prop="group_id">
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
                    </div>

                    <div v-if="form.is_https == '1'">
                        <el-form-item label="证书文件" prop="proxy_catefile">
                            <el-upload :action="`${actionUrl}api/web/upload`"
                                       :headers="{token:$store.state.user.token}"
                                       :data="dataObj"

                                       accept=".crt" :limit="1" :on-success="UploadSuccess"
                                       :file-list="fileList"
                                       :on-remove="handleRemove"
                            >
                                <el-button size="small" type="primary">点击上传</el-button>
                            </el-upload>
                        </el-form-item>
                    </div>
                    <div v-if="form.is_https == '1'">
                        <el-form-item label="密钥文件" prop="proxy_catekeyfile">
                            <el-upload :action="`${actionUrl}api/web/upload`"
                                       :headers="{token:$store.state.user.token}"
                                       :data="dataObj1"
                                       :on-remove="handleRemove1"
                                       accept=".key" :limit="1" :on-success="UploadSuccess1" :file-list="fileList1"
                            >
                                <el-button size="small" type="primary">点击上传</el-button>
                            </el-upload>
                        </el-form-item>
                    </div>
                    <el-form-item v-if="form.is_https == '1'" label="证书链文件" prop="proxy_catechainfile">
                        <el-upload :action="`${actionUrl}api/web/upload`"
                                   :headers="{token:$store.state.user.token}"
                                   :data="dataObj2"
                                   :on-remove="handleRemove2"
                                   accept=".crt" :limit="1" :on-success="UploadSuccess2" :file-list="fileList2"
                        >
                            <el-button size="small" type="primary">点击上传</el-button>
                        </el-upload>
                    </el-form-item>
                    <div>
                        <el-form-item label="模式" prop="protect_status">
                            <el-radio-group v-model="form.protect_status">
                                <el-radio-button label="1">防护模式</el-radio-button>
                                <el-radio-button label="0">转发模式</el-radio-button>
                            </el-radio-group>
                        </el-form-item>
                    </div>
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
            actionUrl: process.env.VUE_APP_API_ROOT,
            id: '',
            form: {
                web_sysname: '',
                web_name: '',
                web_port: '',
                source_ip: '',
                dst_port: '',
                is_https: 0,
                proxy_catefile: '',
                proxy_catekeyfile: '',
                proxy_catechainfile: '',
                protect_status: 1,
                group_id: ''
            },
            ruleForm: {
                web_name: [
                    { required: true, message: '请输入防护域名', trigger: 'change' }
                ],
                web_port: [
                    { required: true, message: '请输入关键字', trigger: 'change' }
                ],
                source_ip: [
                    { required: true, message: '请输入源站地址', trigger: 'blur' }
                ],
                dst_port: [
                    { required: true, message: '请输入目的端口', trigger: 'blur' }
                ],
                is_https: [
                    { required: true, message: '请选择是否开启https', trigger: 'change' }
                ],
                protect_status: [
                    { required: true, message: '请选择模式', trigger: 'change' }
                ],
                group_id: [
                    { required: true, message: '请选择事业单位', trigger: 'blur' }
                ]
            },
            dataObj: {
                type: '1',
                sign: 'sdsdsd',
                timestamp: this.$store.state.user.failure_time
            },
            dataObj1: {
                type: '2',
                sign: 'sdsdsd',
                timestamp: this.$store.state.user.failure_time
            },
            dataObj2: {
                type: '3',
                sign: 'sdsdsd',
                timestamp: this.$store.state.user.failure_time
            },
            roleList: [],
            fileList: [],
            fileList1: [],
            fileList2: []
        }
    },
    mounted() {

        // this.$store.commit('settings/setTitle', '编辑页面')
        // //
        // this.$route.meta.title = '编辑页面'
        // this.initData()
        // if (this.$route.name == 'stationManageEdit') {
        //     this.id = this.$route.params.id
        //     this.initData()
        // }
        if (this.dataInfo) {
            this.id = this.dataInfo.id
            this.initData()
        }
        this.getRoleList()
        //
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
            this.$api.get(`api/web/${this.id}`)
                .then(res => {
                    // this.form = res.data
                    if (res.data) {
                        this.form = res.data
                        this.form.is_https = +res.data.is_https

                        this.fileList.push({
                            name: res.data.proxy_catechainfile,
                            url: res.data.proxy_catechainfile
                        })
                        this.fileList1.push({
                            name: res.data.proxy_catekeyfile,
                            url: res.data.proxy_catekeyfile
                        })
                        this.fileList2.push({
                            name: res.data.proxy_catefile,
                            url: res.data.proxy_catefile
                        })

                    }
                })
        },
        handlePreview() {},
        handleRemove(file, fileList) {
            //
            if (fileList.length === 0) {
                this.form.proxy_catefile = ''
            }
        },
        handleRemove1(file, fileList) {
            //
            if (fileList.length === 0) {
                this.form.proxy_catekeyfile = ''
            }
        },
        handleRemove2(file, fileList) {
            //
            if (fileList.length === 0) {
                this.form.proxy_catechainfile = ''
            }
        },
        // beforeUpload(file, flag) {
        //     //
        // },
        UploadSuccess(data) {
            this.form.proxy_catefile = data.data.path
        },
        UploadSuccess1(data) {
            this.form.proxy_catekeyfile = data.data.path
        },
        UploadSuccess2(data) {
            this.form.proxy_catechainfile = data.data.path
        },
        submitForm(formName) {
            //
            this.$refs[formName].validate(valid => {
                if (valid) {
                    if (this.form.is_https === 1) {
                        if (!this.form.proxy_catefile && !this.form.proxy_catekeyfile && !this.form.proxy_catechainfile) {
                            return  this.$message.error('请检查上传相关文件！')
                        }
                    }
                    if (this.id) {
                        this.$api.put(`api/web/${this.id}`, this.form)
                            .then(res => {
                                //
                                if (res.code === 2000) {
                                    //
                                    this.$emit('formList')
                                    this.$emit('openEditShow', false)
                                    this.$message.success('修改成功！')
                                }
                            })
                    } else {
                        this.$api.post('api/web', this.form)
                            .then(res => {
                                //
                                if (res.code === 2000) {
                                    //
                                    this.$emit('formList')
                                    this.$emit('openEditShow', false)
                                    this.$message.success('新增成功！')
                                } else {
                                    return false
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
            // this.openEditHadnle = false
            this.$emit('openEditShow', false)
        }
    }
}
</script>
