<template>
    <div>
        <!-- <page-main> -->
        <el-row>
            <el-col :md="24">
                <el-form ref="form" class="addform box-forn" :rules="ruleForm" :model="form" label-width="150px">
                    <el-form-item label="上级菜单" prop="parent_id">
                        <!-- <el-select v-model="form.parent_id" placeholder="请选择上级菜单">
                            <el-option v-for="(item, index) in menus" :key="index" :label="item.name" :value="item.id" />
                        </el-select> -->
                        <SelectTree
                            ref="menuParentTree"
                            :props="{
                                value: 'id', // ID字段名
                                label: 'name', // 显示名称
                                children: 'children' // 子级字段名
                            }"
                            :data="menus"
                            :value="form.parent_id"
                            :clearable="true"
                            :accordion="true"
                            @getValue="(value) => {form.parent_id=value}"
                        />
                    </el-form-item>
                    <el-form-item label="菜单类型" prop="type">
                        <el-radio-group v-model="form.type">
                            <el-radio-button label="M">目录</el-radio-button>
                            <el-radio-button label="C">菜单</el-radio-button>
                            <el-radio-button label="F">按钮</el-radio-button>
                        </el-radio-group>
                    </el-form-item>

                    <el-form-item v-if="form.type != 'F'" label="图标" prop="icon">
                        <!-- <image-upload :url.sync="form.icon" action="http://scrm.1daas.com/api/upload/upload" name="image" /> -->
                        <!-- <el-input v-model="form.icon" clearable :readonly="true" style="width: 100%;" placeholder="菜单图标名称" @focus="selectIcon" /> -->
                        <el-popover
                            placement="bottom-start"
                            width="460"
                            trigger="click"
                            @show="$refs['iconSelect'].reset()"
                        >
                            <IconSelect ref="iconSelect" @selected="selected" />
                            <el-input slot="reference" v-model="form.icon" placeholder="点击选择图标" readonly>
                                <!-- <svg-icon
                                    v-if="form.icon"
                                    slot="prefix"
                                    :icon-class="form.icon"
                                    class="el-input__icon"
                                    style="height: 32px; width: 16px;"
                                /> -->
                                <!-- <i :class="form.icon" /> <span>{{ form.icon }}</span> -->
                                <!-- <i v-else slot="prefix" class="el-icon-search el-input__icon" /> -->
                            </el-input>
                        </el-popover>
                    </el-form-item>
                    <div class="box-fonr">
                        <el-form-item label="菜单名称" prop="name">
                            <el-input v-model="form.name" />
                        </el-form-item>
                        <el-form-item label="列表排序" prop="listsort">
                            <el-input v-model="form.listsort" />
                        </el-form-item>
                    </div>
                    <div v-if="form.type !=='F' " class="box-fonr">
                        <el-form-item style="width: 50%;">
                            <span slot="label">
                                <el-tooltip content="选择是外链则路由地址需要以`http(s)://`开头" placement="top">
                                    <i class="el-icon-question" />
                                </el-tooltip>
                                是否外链
                            </span>
                            <el-radio-group v-model="form.is_frame">
                                <el-radio label="1">是</el-radio>
                                <el-radio label="0">否</el-radio>
                            </el-radio-group>
                        </el-form-item>
                        <el-form-item prop="path">
                            <span slot="label">
                                <el-tooltip content="访问的路由地址，如：`user`，如外网地址需内链访问则以`http(s)://`开头" placement="top">
                                    <i class="el-icon-question" />
                                </el-tooltip>
                                路由地址
                            </span>
                            <el-input v-model="form.path" placeholder="请输入路由地址" />
                        </el-form-item>
                    </div>
                    <div v-if="form.type ==='F' " class="box-fonr">
                        <el-form-item label="前端权限字符" prop="perms">
                            <el-input v-model="form.perms" />
                        </el-form-item>
                        <el-form-item label="后端权限字符" prop="permit">
                            <el-input v-model="form.permit" />
                        </el-form-item>
                    </div>
                    <div v-if="form.type !=='M' && form.type !=='F'" class="box-fonr">
                        <el-form-item label="组件路径" prop="component">
                            <el-input v-model="form.component" />
                        </el-form-item>
                        <el-form-item label="前端权限字符" prop="perms">
                            <el-input v-model="form.perms" />
                        </el-form-item>
                    </div>
                    <div v-if="form.type !=='M' && form.type !=='F'" class="box-fonr">
                        <el-form-item label="超级管理员权限" prop="is_super" style="width: 48.5%;">
                            <el-radio-group v-model="form.is_super">
                                <el-radio label="1">是</el-radio>
                                <el-radio label="0">否</el-radio>
                            </el-radio-group>
                        </el-form-item>
                        <el-form-item label="后端权限字符" prop="permit">
                            <el-input v-model="form.permit" />
                        </el-form-item>
                    </div>
                    <div v-if="form.type !=='F' " class="box-fonr">
                        <el-form-item label="菜单状态" prop="status" style="width: 48.5%;">
                            <span slot="label">
                                <el-tooltip content="选择停用则路由将不会出现在侧边栏，也不能被访问" placement="top">
                                    <i class="el-icon-question" />
                                </el-tooltip>
                                菜单状态
                            </span>
                            <el-radio-group v-model="form.status">
                                <el-radio label="1">正常</el-radio>
                                <el-radio label="0">停用</el-radio>
                            </el-radio-group>
                        </el-form-item>
                        <el-form-item prop="visible">
                            <span slot="label">
                                <el-tooltip content="选择隐藏则路由将不会出现在侧边栏，但仍然可以访问" placement="top">
                                    <i class="el-icon-question" />
                                </el-tooltip>
                                显示状态
                            </span>
                            <el-radio-group v-model="form.visible">
                                <el-radio label="1">显示</el-radio>
                                <el-radio label="0">隐藏</el-radio>
                            </el-radio-group>
                        </el-form-item>
                    </div>
                    <div v-if="form.type ==='F' " class="box-fonr">
                        <el-form-item label="菜单状态" prop="status" style="width: 48.5%;">
                            <span slot="label">
                                <el-tooltip content="选择停用则路由将不会出现在侧边栏，也不能被访问" placement="top">
                                    <i class="el-icon-question" />
                                </el-tooltip>
                                菜单状态
                            </span>
                            <el-radio-group v-model="form.status">
                                <el-radio label="1">正常</el-radio>
                                <el-radio label="0">停用</el-radio>
                            </el-radio-group>
                        </el-form-item>
                        <el-form-item label="超级管理员权限" prop="is_super" style="width: 48.5%;">
                            <el-radio-group v-model="form.is_super">
                                <el-radio label="1">是</el-radio>
                                <el-radio label="0">否</el-radio>
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
        <Icon ref="icon" @getValue="value => form.icon = value" />
    </div>
</template>
<script>
// import store from '@/store/index'
import IconSelect from '@/components/IconSelect'
import Icon from './icon'
import SelectTree from '@/components/treeSelect/treeSelect.vue'
export default {
    components: {
        Icon,
        SelectTree,
        IconSelect
    },
    props: {
        dataInfo: {
            type: Object
        }
    },
    data() {
        return {
            id: '',
            form: {
                id: '',
                name: '',
                permit: '',
                perms: '',
                parent_id: '',
                listsort: '',
                is_frame: '',
                component: '',
                path: '',
                type: 'C',
                status: '1',
                visible: '1',
                icon: '',
                is_super: '0'
            },
            ruleForm: {
                name: [
                    { required: true, message: '请输入菜单名称', trigger: 'blur' }
                    // { min: 3, max: 5, message: '长度在 3 到 5 个字符', trigger: 'blur' }
                ],
                parent_id: [
                    { required: true, message: '请选择上级菜单', trigger: 'change' }
                ],
                is_super: [
                    { required: true, message: '请选择超级管理员权限', trigger: 'change' }
                ],
                is_frame: [
                    { required: true, message: '请输入是否外链', trigger: 'change' }
                ],
                listsort: [
                    { required: true, message: '请输入排序', trigger: 'blur' }
                ],
                // listsort
                type: [
                    { required: true, message: '请选择菜单类型', trigger: 'change' }
                ],
                status: [
                    { required: true, message: '请选择菜单状态', trigger: 'change' }
                ],
                visible: [
                    { required: true, message: '请选择显示或隐藏', trigger: 'change' }
                ]
            },
            menus: []
        }
    },
    mounted() {
        this.initMenus()
        // this.$store.commit('settings/setTitle', '编辑页面')
        // //
        // this.$route.meta.title = '编辑页面'
        // this.initData()
        // if (this.$route.name == 'menuModuleEdit') {
        //     this.id = this.$route.params.id
        //     this.initData()
        // }
        if (this.dataInfo) {
            this.type = 'edit'
            this.id = this.dataInfo.id
            this.initData()
        }
    },
    methods: {
        // 选择图标
        selected(name) {
            //
            this.form.icon = name
        },
        selectIcon() {
            this.$refs.icon.visible = true
        },
        initData() {
            this.$api.get(`api/menu/${ this.id}`)
                .then(res => {
                    //
                    this.form = res.data
                })
        },
        changSwich(index, tab) {
            // this.form.status = index
            // this.form.status = index === false ? '0' : '1'
            // //
            if (tab === 0) {
                this.form.status = index
            } else {
                this.form.visible = index
            }
            //

        },
        changVisble(index, tab) {
            if (tab === 0) {
                this.form.status = index
            } else {
                this.form.visible = index
            }

            // let statusB = index === false ? '0' : '1'
            // this.form.visible =
            //
            // this.form.visible = statusB

        },
        initMenus() {
            // const _this = this
            // const roleLists = this.$store.state.menu.routes[0].children
            // roleLists.forEach(item => {
            //     var obj1 = {
            //         path: item.path,
            //         name: item.meta.title
            //     }
            //     _this.menus.push(obj1)
            // })
            this.$api.post('api/menu/searchList')
                .then(res => {
                    //
                    let arry = res.data
                    // arry.map(item => {
                    //     item.children.map(itemed => {
                    //         delete itemed.children
                    //     })
                    // })
                    //
                    this.menus = arry
                    // const menu = { id: 0, name: '主类目', child: [] }
                    // menu.child = this.handleTree(res.data, 'id')
                    // this.menus.push(menu)
                    // //
                })
        },
        submitForm(formName) {
            this.form.id = ''
            //
            this.$refs[formName].validate(valid => {
                if (valid) {
                    // this.$message.success('新增成功！')
                    // //
                    // // this.$router.push('/systemManage/menuModuleList')
                    // this.$emit('formList')
                    // this.$emit('openEditShow', false)

                    //
                    if (this.id) {
                        // this.form.id = this.id
                        this.$api.put(`api/menu/${this.id}`, this.form)
                            .then(res => {
                                if (res.code === 2000) {
                                    this.$emit('formList')
                                    this.$emit('openEditShow', false)
                                    this.$message.success('新增成功！')
                                }

                            })
                    } else {
                        this.$api.post('api/menu/', this.form)
                            .then(res => {
                                if (res.code === 2000) {
                                    this.$emit('formList')
                                    this.$emit('openEditShow', false)
                                    this.$message.success('新增成功！')
                                }

                            })
                    }

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
<style scoped>
.prin-box {
    width: 600px !important;
}
</style>
