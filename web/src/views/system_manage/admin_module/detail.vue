<template>
    <div>
        <!-- <page-main> -->
        <el-row>
            <el-col :md="24" :lg="22">
                <el-form ref="form" :model="form" label-width="130px">
                    <el-form-item label="用户名" prop="username">
                        <el-input v-model="form.username" readonly />
                    </el-form-item>
                    <el-form-item label="真实姓名" prop="realname">
                        <el-input v-model="form.realname" readonly />
                    </el-form-item>
                    <el-form-item label="状态">
                        <!-- <el-switch v-model="form.status" /> -->
                        <el-radio-group v-model="form.status" readonly>
                            <el-radio :label="1">启用</el-radio>
                            <el-radio :label="0">冻结</el-radio>
                        </el-radio-group>
                    </el-form-item>
                    <el-form-item label="角色">
                        <el-select v-model="form.roleIds" multiple style="width: 100%;"
                                   filterable
                                   allow-create
                                   default-first-option
                                   placeholder="请选择角色"
                                   readonly
                        >
                            <el-option v-for="item in roleList"
                                       :key="item.id"
                                       :value="item.id"
                                       :label="item.name"
                            />
                        </el-select>
                    </el-form-item>
                    <el-form-item label="最后一次登录时间" prop="last_time">
                        <el-input v-model="form.last_time" readonly />
                    </el-form-item>
                    <el-form-item label="最后一次登录IP" prop="last_ip">
                        <el-input v-model="form.last_ip" readonly />
                    </el-form-item>

                    <el-form-item label="备注" prop="note">
                        <el-input v-model="form.note" readonly type="textarea" />
                    </el-form-item>
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
                username: '',
                password: '',
                realname: '',
                status: 0,
                note: '',
                roleId: '',
                last_time: '',
                last_ip: '',
                created_at: '',
                updated_at: ''
            },
            roleList: []
        }
    },
    mounted() {
        this.id = this.idInfo
        this.initData()
        this.getRoleList()
    },
    methods: {
        initData() {
            this.$api.get(`api/manage/${this.id}`)
                .then(res => {
                    //
                    this.form = res.data
                    if (res.data) {
                        this.form.status = +res.data.status
                    }
                })
        },
        getRoleList() {
            this.$api.post('api/role/searchList')
                .then(res => {
                    //
                    if (res.data) {
                        this.roleList  = res.data
                    }
                })
        }

    }
}
</script>
