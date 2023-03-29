<template>
    <div>
        <!-- <page-main> -->
        <el-row>
            <el-col :md="24">
                <el-form ref="form" :inline="true" class="addform" :model="form" label-width="120px">
                    <el-form-item label="网站" prop="web_id">
                        <!-- <el-input v-model="form.web_id" /> -->
                        <el-input v-model="form.web_id" readonly />
                    </el-form-item>
                    <el-form-item label="响应路径" prop="request_uri">
                        <el-input v-model="form.request_uri" readonly />
                    </el-form-item>
                    <el-form-item label="响应方法" prop="request_method">
                        <el-input v-model="form.request_method" readonly />
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
                    <div>
                        <el-form-item label="移除系统规则" prop="remove_sysrule_id">
                            <!-- <el-input v-model="form.remove_sysrule_id" /> -->
                            <el-select v-model="form.remove_sysrule_id" style="width: 100%;"
                                       filterable
                                       allow-create
                                       readonly
                                       default-first-option
                                       placeholder="请选择网站"
                            >
                                <el-option v-for="item in roleList"
                                           :key="item"
                                           :value="item"
                                           :label="item"
                                />
                            </el-select>
                        </el-form-item>
                        <el-form-item label="状态" prop="status">
                            <el-radio-group v-model="form.status" disabled>
                                <el-radio :label="&quot;0&quot;">禁用</el-radio>
                                <el-radio :label="&quot;1&quot;">开启</el-radio>
                            </el-radio-group>
                            <!-- <el-switch v-model="form.status" /> -->
                        <!-- <el-radio-group v-model="form.status">
                                <el-radio-button label="0">禁用</el-radio-button>
                                <el-radio-button label="1">阻断</el-radio-button>
                                <el-radio-button label="2">告警</el-radio-button>
                            </el-radio-group> -->
                        </el-form-item>
                    </div>

                    <div>
                        <el-form-item label="描述" prop="describe">
                            <el-input v-model="form.describe" readonly type="textarea" />
                        </el-form-item>
                    </div>
                    <div>
                        <el-form-item label="规则内容" prop="rule_content">
                            <el-input v-model="form.rule_content" readonly :autosize="{ minRows: 2, maxRows: 4}" type="textarea" style="width: 400px;" />
                        </el-form-item>
                    </div>
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
                describe: '',
                remove_sysrule_id: '',
                status: true,
                admin_id: '',
                rule_content: '',
                created_at: '',
                updated_at: ''
            },
            roleList: [],
            webList: []
        }
    },
    mounted() {
        this.id = this.idInfo
        this.initData()
        this.getRoleList()
    },
    methods: {
        getRoleList() {
            this.$api.post('api/sysRules/searchList')
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
            this.$api.get(`api/whiteRules/${this.id}`)
                .then(res => {
                    this.form = res.data

                    this.webList.map(items => {
                        if (this.form.web_id == items.id) {
                            this.form.web_id = items.name
                        }
                    })

                })
        }
    }
}
</script>
