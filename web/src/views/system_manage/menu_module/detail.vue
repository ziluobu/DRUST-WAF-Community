<template>
    <div>
        <!-- <page-main> -->
        <el-row>
            <el-col :md="24">
                <el-form ref="form" :inline="true" class="addform" :rules="ruleForm" :model="form" label-width="120px">
                    <el-form-item label="菜单名称" prop="name">
                        <el-input v-model="form.name" />
                    </el-form-item>
                    <el-form-item label="后端权限字符" prop="permit">
                        <el-input v-model="form.permit" />
                    </el-form-item>
                    <el-form-item label="前端权限字符" prop="perms">
                        <el-input v-model="form.perms" />
                    </el-form-item>
                    <el-form-item label="上级菜单" prop="parent_id">
                        <el-select v-model="form.region" placeholder="请选择上级菜单">
                            <el-option v-for="(item, index) in menus" :key="index" :label="item.name" :value="item.path" />
                        </el-select>
                    </el-form-item>
                    <el-form-item label="列表排序" prop="listsort">
                        <el-input v-model="form.listsort" />
                    </el-form-item>
                    <el-form-item label="外链" prop="is_frame">
                        <el-input v-model="form.is_frame" />
                    </el-form-item>
                    <el-form-item label="组件路径" prop="component">
                        <el-input v-model="form.component" />
                    </el-form-item>
                    <el-form-item label="路由地址" prop="path">
                        <el-input v-model="form.path" />
                    </el-form-item>
                    <el-form-item label="超级管理员权限" prop="is_super">
                        <el-input v-model="form.is_super" />
                    </el-form-item>
                    <el-form-item label="创建时间" prop="created_at">
                        <el-input v-model="form.created_at" />
                    </el-form-item>
                    <el-form-item label="更新时间" prop="updated_at">
                        <el-input v-model="form.updated_at" />
                    </el-form-item>
                    <el-form-item label="菜单类型" prop="type">
                        <el-radio-group v-model="form.type">
                            <el-radio-button label="M">目录</el-radio-button>
                            <el-radio-button label="C">菜单</el-radio-button>
                            <el-radio-button label="F">按钮</el-radio-button>
                        </el-radio-group>
                    </el-form-item>
                    <div>
                        <el-form-item label="菜单状态" prop="status">
                            <el-switch v-model="form.status" />
                        </el-form-item>
                        <el-form-item label="显示/隐藏" prop="visible">
                            <el-switch v-model="form.visible" />
                        </el-form-item>
                    </div>
                    <el-form-item label="图标" prop="icon">
                        <image-upload :url.sync="form.icon" action="http://scrm.1daas.com/api/upload/upload" name="image" />
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
    data() {
        return {
            id: '',
            form: {
                name: '',
                permit: '',
                perms: '',
                parent_id: '',
                listsort: '',
                is_frame: '',
                component: '',
                path: '',
                type: '',
                status: '',
                visible: '',
                icon: '',
                is_super: '',
                created_at: '',
                updated_at: ''
            },
            ruleForm: {
                name: [
                    { required: true, message: '请输入菜单名称', trigger: 'blur' }
                    // { min: 3, max: 5, message: '长度在 3 到 5 个字符', trigger: 'blur' }
                ],
                parent_id: [
                    { required: true, message: '请选择上级菜单', trigger: 'change' }
                ],
                is_frame: [
                    { required: true, message: '请输入外链', trigger: 'change' }
                ],
                type: [
                    { required: true, message: '请选择菜单类型', trigger: 'change' }
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
            this.$api.get('api/menu/*')
                .then(res => {
                    //
                    this.form = res.data
                })
        }
    }
}
</script>
