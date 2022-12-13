<template>
    <div>
        <!-- <page-main> -->
        <el-row>
            <el-col :md="24" :lg="22">
                <el-form ref="form" :rules="ruleForm" :model="form" label-width="120px">
                    <el-form-item label="角色名称" prop="name">
                        <el-input v-model="form.name" readonly />
                    </el-form-item>
                    <el-form-item label="列表排序" prop="listsort">
                        <el-input v-model="form.listsort" readonly />
                    </el-form-item>
                    <el-form-item label="菜单" prop="menuIds">
                        <el-input v-model="form.menuIds" @focus="setVisible = true" />
                    </el-form-item>
                    <el-form-item label="创建时间" prop="created_at">
                        <el-input v-model="form.created_at" readonly />
                    </el-form-item>
                    <el-form-item label="更新时间" prop="updated_at">
                        <el-input v-model="form.updated_at" readonly />
                    </el-form-item>
                    <el-form-item label="备注" prop="note">
                        <el-input v-model="form.note" readonly />
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
                name: '',
                listsort: '',
                note: '',
                menuIds: [],
                created_at: '',
                updated_at: ''
            },
            ruleForm: {

            }
        }
    },
    mounted() {
        this.id = this.idInfo
        this.initData()
    },
    methods: {
        initData() {
            this.$api.get(`api/role/${this.id}`)
                .then(res => {
                    //
                    this.form = res.data
                })
        }
    }
}
</script>
