<template>
    <div>
        <!-- <page-main> -->
        <el-row>
            <!-- :lg="12" -->
            <el-col :md="24">
                <el-form ref="form" :inline="true" :rules="ruleForm" :model="form" label-width="120px">
                    <el-form-item label="系统名称" prop="web_sysname">
                        <el-input v-model="form.web_sysname" readonly />
                    </el-form-item>
                    <el-form-item label="防护域名" prop="web_name">
                        <el-input v-model="form.web_name" readonly />
                    </el-form-item>
                    <el-form-item label="源端口" prop="web_port">
                        <el-input v-model="form.web_port" readonly />
                    </el-form-item>
                    <el-form-item label="源站地址" prop="source_ip">
                        <el-input v-model="form.source_ip" readonly />
                    </el-form-item>
                    <el-form-item label="代理名称" prop="proxy_name">
                        <el-input v-model="form.proxy_name" readonly />
                    </el-form-item>
                    <el-form-item label="源端口" prop="web_port">
                        <el-input v-model="form.web_port" readonly />
                    </el-form-item>
                    <el-form-item label="目的端口" prop="dst_port">
                        <el-input v-model="form.dst_port" readonly />
                    </el-form-item>
                    <el-form-item label="所属单位" prop="group_id">
                        <el-select v-model="form.group_id" style="width: 100%;" readonly
                                      
                                   placeholder="请选择单位"
                        >
                            <el-option v-for="item in roleList"
                                       :key="item.id"
                                       :value="item.id"
                                       :label="item.group_name"
                            />
                        </el-select>
                    </el-form-item>
                    <el-form-item label="网站" prop="web_active">
                        <el-input v-model="form.web_active" readonly />
                    </el-form-item>
               
                    <el-form-item label="创建时间" prop="created_at">
                        <el-input v-model="form.created_at" readonly />
                    </el-form-item>
                    <el-form-item label="更新时间" prop="updated_at">
                        <el-input v-model="form.updated_at" readonly />
                    </el-form-item>
                    <div>
                        <el-form-item label="状态" prop="is_https">
                            <div style="width: 190px;">
                                <!-- <el-switch v-model="form.is_https"  /> -->
                                <el-radio-group v-model="form.is_https" readonly>
                                    <el-radio :label="0">关闭</el-radio>
                                    <el-radio :label="1">开启</el-radio>
                                </el-radio-group>
                            </div>
                            <!-- <el-switch v-model="form.is_https" readonly /> -->
                        </el-form-item>
                        <el-form-item label="模式" prop="protect_status">
                            <el-radio-group v-model="form.protect_status" readonly>
                                <el-radio-button label="1">防护模式</el-radio-button>
                                <el-radio-button label="0">转发模式</el-radio-button>
                            </el-radio-group>
                        </el-form-item>
                    </div>
                    <div v-if="form.is_https == '1'">
                        <el-form-item label="证书文件" prop="proxy_catefile">
                            <div>{{ form.proxy_catefile }}</div>
                        </el-form-item>
                    </div>
                    <div v-if="form.is_https == '1'"> 
                        <el-form-item label="密钥文件" prop="proxy_catekeyfile">
                            <div>{{ form.proxy_catekeyfile }}</div>
                        </el-form-item>
                    </div>
                    <div v-if="form.is_https == '1'">
                        <el-form-item label="证书链文件" prop="proxy_catechainfile">
                            <div>{{ form.proxy_catechainfile }}</div>
                        </el-form-item>
                    </div>
                </el-form>
            </el-col>
        </el-row>
        <!-- </page-main> -->
    </div>
</template>
<script>
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
                web_sysname: '',
                web_name: '',
                web_port: '',
                source_ip: '',
                proxy_name: '',
                dst_port: '',
                is_https: 0,
                proxy_catefile: '',
                proxy_catekeyfile: '',
                proxy_catechainfile: '',
                protect_status: 1,
                group_id: '1',
                web_active: '',
                created_at: '',
                updated_at: ''
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
                ]
            },
            roleList: []
        }
    },
    mounted() {
        // this.id = this.$route.params.id
        this.id = this.idInfo
        this.initData()
        this.getRoleList()
    },
    methods: {
        getRoleList() {
            
            // web/searchList
            this.$api.post('api/group/searchList')
                .then(res => {
                
                    if (res.data) {
                        this.roleList  = res.data
                     
                    }
                })
        },
        initData() {
            this.$api.get(`api/web/${this.id}`)
                .then(res => {
                    this.form = res.data
                    this.form.is_https = +res.data.is_https

                })
        },
        handlePreview() {},
        handleRemove() {},
        beforeUpload() {},
        changeUpload() {}
    }
}
</script>
