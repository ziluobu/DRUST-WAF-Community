<template>
    <div>
        <!-- <page-main> -->
        <el-row>
            <el-col :md="24" :lg="22">
                <el-form ref="form" :rules="ruleForm" :model="form" label-width="120px">
                    <el-form-item label="角色名称" prop="name">
                        <el-input v-model="form.name" />
                    </el-form-item>
                    <el-form-item label="菜单排序" prop="listsort">
                        <el-input-number v-model="form.listsort" controls-position="right" :min="0" />
                    </el-form-item>
                    <el-form-item label="菜单" prop="menuIds">
                        <el-checkbox v-model="deptExpand" @change="handleCheckedTreeExpand($event)">展开/折叠</el-checkbox>
                        <el-checkbox v-model="deptNodeAll" @change="handleCheckedTreeNodeAll($event)">全选/全不选</el-checkbox>
                        <el-checkbox v-model="form.menuCheckStrictly" checked @change="handleCheckedTreeConnect($event)">父子联动</el-checkbox>
                        <el-tree
                            ref="menu"
                            class="tree-border"
                            :data="menuOptions"
                            show-checkbox

                            node-key="id"
                            :check-strictly="!form.menuCheckStrictly"
                            empty-text="加载中，请稍候"
                            :props="defaultProps"
                        />
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
        <!-- <el-dialog title="权限设置" custom-class="el-dialog-s" append-to-body :visible.sync="setVisible" width="50%">
            <el-row v-loading="setLoading">
                <el-tree ref="tree"
                         style="height: 500px; overflow: auto;"
                         :data="treeData"
                         show-checkbox
                         node-key="id"
                         default-expand-all
                         :default-checked-keys="checkedKeys"
                         :props="defaultProps"
                />
            </el-row>
            <div slot="footer" class="dialog-footer">
                <el-button @click="setVisible = false">取 消</el-button>
                <el-button type="primary" @click="addSetSubmit">确 定</el-button>
            </div>
        </el-dialog> -->
    </div>
</template>
<script>
// import store from '@/store/index'
// import SelectTree from '@/components/treeSelect/treeSelect.vue'
export default {
    components: {

        // SelectTree
    },
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
                name: '',
                listsort: '',
                menuIds: ''
            },
            ruleForm: {
                name: [
                    { required: true, message: '请输入角色名称', trigger: 'blur' }

                ],
                listsort: [
                    { required: true, message: '请输入菜单排序', trigger: 'blur' }
                ]
            },
            treeData: [],
            checkedKeys: [],
            defaultProps: {

                children: 'children',
                label: 'name'
            },
            checkedNames: [],
            setLoading: false,
            setVisible: false,
            menus: [],
            // 菜单列表
            menuOptions: [],
            // 部门列表
            deptOptions: [],
            // 是否显示弹出层（数据权限）
            openDataScope: false,
            menuExpand: false,
            menuNodeAll: false,
            deptExpand: false,
            deptNodeAll: false,
            // 选中数组
            ids: []

        }
    },
    mounted() {
        if (this.dataInfo) {
            this.id = this.dataInfo.id
            this.initData()

        }
        setTimeout(() => {
            this.initMenus()
        }, 500)

    },
    methods: {
        /** 根据角色ID查询菜单树结构 */
        initMenus() {
            this.$api.post('api/role/menuTree')
                .then(res => {
                    //
                    this.menuOptions = res.data
                    let arryBox = res.data
                    let menids = []
                    this.$nextTick(() => {
                        arryBox.forEach(item => {
                            this.checkedKeys.forEach(items => {
                                if (item.id === items) {
                                    menids.push(item.id)
                                }
                                item.children.forEach(itemes => {
                                    if (itemes.id === items) {
                                        menids.push(itemes.id)
                                    }
                                    itemes.children.forEach(iteds => {
                                        if (iteds.id === items) {
                                            menids.push(iteds.id)
                                        }
                                        iteds.children.forEach(itemed => {
                                            if (itemed.id === items) {
                                                menids.push(itemed.id)
                                            }
                                            itemed.children.forEach(ited => {
                                                if (ited.id === items) {
                                                    menids.push(ited.id)
                                                }
                                            })
                                        })
                                    })
                                })
                            })
                        })
                        menids.forEach(v => {
                            this.$nextTick(() => {
                                this.$refs.menu.setChecked(v, true, false)
                            })
                        })
                    })
                })
        },
        // 树权限（展开/折叠）
        handleCheckedTreeExpand(value) {

            let treeList = this.menuOptions
            for (let i = 0; i < treeList.length; i++) {
                //
                this.$refs.menu.store.nodesMap[treeList[i].id].expanded = value
            }
        },
        // 树权限（全选/全不选）
        handleCheckedTreeNodeAll(value) {
            this.$refs.menu.setCheckedNodes(value ? this.menuOptions : [])

        },
        // 树权限（父子联动）
        handleCheckedTreeConnect(value) {
            this.form.menuCheckStrictly = !!value
        },
        initRoles() {
            const _this = this
            const roleLists = this.$store.state.menu.routes[0].children
            roleLists.forEach(item => {
                //
                var obj1 = {
                    id: item.path,
                    name: item.meta.title,
                    children: []
                }
                if (item.children && item.children.length > 0) {
                    item.children.forEach(Ite => {
                        var obj2 = {
                            id: Ite.path,
                            name: Ite.meta.title
                        }
                        obj1.children.push(obj2)
                    })
                }
                _this.treeData.push(obj1)
                //
            })
        },

        initData() {
            const roleId = this.id || this.ids
            // const roleMenu = this.initMenus(roleId)
            this.$api.get(`api/role/${roleId}`)
                .then(res => {
                    //
                    this.form = res.data
                    this.checkedKeys = res.data.menuIds
                    this.open = true

                })
        },
        addSetSubmit() {
            const _self = this
            // var ids = this.$refs.tree.getHalfCheckedKeys().concat(this.$refs.tree.getCheckedKeys())
            const checkedNodes = this.$refs.tree.getCheckedNodes()
            checkedNodes.forEach(item => {
                //
                _self.checkedNames.push(item.name)
            })
            _self.form.menuIds = _self.checkedNames.toString()
            this.setVisible = false
        },
        // 所有菜单节点数据
        getMenuAllCheckedKeys() {
            // 目前被选中的菜单节点
            let checkedKeys = this.$refs.menu.getCheckedKeys()
            // 半选中的菜单节点
            let halfCheckedKeys = this.$refs.menu.getHalfCheckedKeys()
            checkedKeys.unshift.apply(checkedKeys, halfCheckedKeys)
            //
            return checkedKeys
        },
        submitForm(formName) {
            this.$refs[formName].validate(valid => {
                if (valid) {
                    this.form.menuIds = this.getMenuAllCheckedKeys()
                    if (this.id) {
                        this.$api.put(`api/role/${this.id}`, this.form)
                            .then(res => {
                                if (res.code === 2000) {
                                    this.$emit('formList')
                                    this.$emit('openEditShow', false)
                                    this.$message.success('新增成功！')
                                }

                            })
                    } else {
                        this.$api.post('api/role/', this.form)
                            .then(res => {
                                if (res.code === 2000) {
                                    this.$emit('formList')
                                    this.$emit('openEditShow', false)
                                    this.$message.success('新增成功！')
                                }

                            })
                    }

                    // this.$message.success('新增成功！')

                    //
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
<style>

.el-dialog-s {
    margin-top: 15vh !important;
    z-index: 11;
}
.tree-border {
    border: 1px solid #e5e6e7;
}
</style>
