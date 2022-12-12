<template>
    <div>
        <!-- <page-main> -->
        <el-row>
            <el-col :md="24" :lg="20">
                <el-form ref="form" :inline="true" :rules="ruleForm" :model="form" label-width="120px">
                    <el-form-item label="配置名称" prop="name">
                        <el-input v-model="form.name" readonly />
                    </el-form-item>
                    <el-form-item label="配置类型" prop="type">
                        <el-radio-group v-model="form.type" readonly>
                            <el-radio-button label="0">默认</el-radio-button>
                            <el-radio-button label="1">文件</el-radio-button>
                            <el-radio-button label="2">图片</el-radio-button>
                        </el-radio-group>
                    </el-form-item>
                    <el-form-item label="key" prop="key">
                        <el-input v-model="form.key" readonly />
                    </el-form-item>
                    <el-form-item label="value" prop="value">
                        <el-input v-model="form.value" readonly />
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
            image: '',
            fileList: [],
            form: {
                name: '',
                type: 0,
                key: '',
                value: '',
                created_at: '',
                updated_at: ''
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
            }
        }
    },
    mounted() {
        // this.id = this.$route.params.id

        //
        this.id = this.idInfo
        this.initData()
    },
    methods: {
        handleSuccess1() {},
        handlePreview() {},
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
        }
    }
}
</script>
