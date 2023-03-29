<template>
    <div>
        <!-- <page-main> -->
        <el-row>
            <el-col :md="24">
                <el-form ref="form" class="addform" :inline="true" :model="form" label-width="120px">
                    <el-form-item label="单位名称" prop="group_id">
                        <!-- <el-input v-model="form.group_id" /> -->
                        <el-select v-model="form.group_id" style="width: 100%;" disabled

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
                        <el-input v-model="form.ip" readonly />
                    </el-form-item>
                    <el-form-item label="联系人" prop="contact">
                        <el-input v-model="form.contact" readonly />
                    </el-form-item>
                    <el-form-item label="联系方式" prop="phone">
                        <el-input v-model="form.phone" readonly />
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
                group_id: '',
                ip: '',
                concat: '',
                phone: '',
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
        getRoleList() {
            this.$api.post('api/group/searchList')
                .then(res => {
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
        }
    }
}
</script>
