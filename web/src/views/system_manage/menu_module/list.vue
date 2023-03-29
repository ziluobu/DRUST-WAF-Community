<template>
    <div>
        <page-main>
            <el-button v-if="utils.hasPermission('menu.store') || utils.hasPermission ('*.*')"
                       size="small" type="primary" icon="el-icon-plus" @click="addHandle"
            >
                新增
            </el-button>
            <search-bar>
                <el-form ref="queryForm" :model="search" :inline="true" size="small" label-width="100px">
                    <el-form-item label="菜单名称">
                        <el-input v-model="search.name" placeholder="请输入菜单名称" clearable @keydown.enter.native="searchHandle" @clear="searchHandle" />
                    </el-form-item>

                    <el-form-item label="状态">
                        <el-select v-model="search.status" placeholder="请选择">
                            <el-option label="全部" value="" />
                            <el-option label="正常" value="1" />
                            <el-option label="停用" value="0" />
                        </el-select>
                    </el-form-item>
                    <el-form-item>
                        <el-button type="primary" icon="el-icon-search" @click="searchHandle">筛 选</el-button>
                        <el-button icon="el-icon-refresh" size="mini" @click="resetQuery('queryForm')">重置</el-button>
                    </el-form-item>
                </el-form>
            </search-bar>
            <!-- <el-button
                type="info"
                plain
                icon="el-icon-sort"
                size="mini"
                @click="toggleExpandAll"
            >
                展开/折叠
            </el-button> -->
            <el-table ref="table" class="list-table" :data="dataList" :loading="loading" border stripe highlight-current-row
                      row-key="id"
                      :default-expand-all="isExpandAll"
                      :tree-props="{children: 'children', hasChildren: 'hasChildren'}"
            >
                <el-table-column prop="name" label="菜单名称" width="150" />
                <el-table-column prop="icon" label="图标">
                    <template slot-scope="scope">
                        <i :class="scope.row.icon " />
                    </template>
                </el-table-column>
                <el-table-column prop="listsort" label="排序" />
                <el-table-column prop="type" label="菜单类型">
                    <template slot-scope="scope">
                        {{ scope.row.type=='M' ?"目录":(scope.row.type=='C'?"菜单":"按钮") }}
                    </template>
                </el-table-column>
                <el-table-column prop="status" label="菜单状态">
                    <template slot-scope="scope">
                        <el-tag :size="scope.row.status==1?'success':'danger'">{{ scope.row.status==1?'正常':'停用' }}</el-tag>
                    </template>
                </el-table-column>
                <el-table-column prop="created_at" label="创建时间" width="160" />
                <el-table-column label="操作" width="200" fixed="right">
                    <template slot-scope="scope">
                        <el-button v-if="utils.hasPermission('menu.update') || utils.hasPermission ('*.*')" type="primary" size="mini" plain @click="editHandle(scope.row)">编辑</el-button>
                        <el-dropdown @command="handleMoreOperating($event, scope.row)">
                            <el-button size="mini">
                                更多操作<i class="el-icon-arrow-down el-icon--right" />
                            </el-button>
                            <el-dropdown-menu slot="dropdown">
                                <el-dropdown-item v-if="utils.hasPermission('menu.show') || utils.hasPermission ('*.*')" command="detail">详情</el-dropdown-item>
                                <el-dropdown-item v-if="utils.hasPermission('menu.destroy') || utils.hasPermission ('*.*')" command="delete" divided>删除</el-dropdown-item>
                            </el-dropdown-menu>
                        </el-dropdown>
                    </template>
                </el-table-column>
            </el-table>
            <el-pagination
                :current-page="page.currentPage"
                :page-sizes="page.pageSizes"
                :page-size="page.pageSize"
                :total="page.total"
                layout="total, sizes, ->, prev, pager, next, jumper"
                :hide-on-single-page="false"
                class="pagination" background @size-change="handleSizeChange" @current-change="handleCurrentChange"
            />
            <el-dialog v-if="openEditHadnle" :key="timer" :title="titleText" :visible.sync="openEditHadnle" width="50%">
                <stationManageEdit :data-info="dataInfo" @openEditShow="openEditShow" @formList="initData" />
            </el-dialog>
            <el-dialog v-if="openDetailHadnle" :key="timer" title="详情" :visible.sync="openDetailHadnle" width="60%">
                <stationManageDetail :id-info="idInfo" />
            </el-dialog>
        </page-main>
    </div>
</template>
<script>
import stationManageEdit from './form'
import stationManageDetail from './detail'
export default {
    components: {
        stationManageEdit,
        stationManageDetail
    },
    props: {},
    data() {
        return {
            search: {
                name: '',
                status: ''
            },
            dataList: [],
            loading: false,
            page: {
                currentPage: 1,
                pageSizes: [10, 50, 100],
                pageSize: 10,
                total: 0
            },
            openEditHadnle: false,
            openDetailHadnle: false,
            titleText: '编辑',
            timer: '',
            dataInfo: {},
            idInfo: '',
            // 是否展开，默认全部折叠
            isExpandAll: false,
            table: true
        }
    },
    mounted() {
        this.initData()
    },
    methods: {
        searchHandle() {
            //
            this.initData()
        },
        /** 重置按钮操作 */
        resetQuery(formName) {
            this.search = {}
            this.$refs[formName].resetFields()
            // this.$refs.tables.sort(this.defaultSort.prop, this.defaultSort.order)
            this.initData()
        },
        addHandle() {
            // this.$router.push({name: 'menuModuleAdd'})
            this.timer = new Date().getTime()
            this.titleText = '添加菜单'
            this.openEditHadnle = true
            this.dataInfo = ''
        },
        openEditShow(val) {
            this.openEditHadnle = val
        },
        editHandle(row) {
            //
            // this.$router.push({name: 'menuModuleEdit', params: {id: row.id, row: row}})
            this.timer = new Date().getTime()
            this.titleText = '编辑菜单'
            this.openEditHadnle = true
            this.dataInfo  = row
        },
        handleMoreOperating(command, row) {
            switch (command) {
                case 'detail':
                    this.detailHandle(row)
                    break
                case 'delete':
                    this.deleteHandle(row)
                    break
            }
        },
        detailHandle(row) {
            // this.$router.push({name: 'menuModuleDetail', params: {id: row.id}})
            this.openDetailHadnle = true
            this.idInfo = row.id
        },
        deleteHandle(row) {
            this.$confirm('确定删除' + row.name + ', 是否继续?', '提示', {
                confirmButtonText: '确定',
                cancelButtonText: '取消',
                type: 'warning'
            }).then(() => {
                this.$api.delete(`api/menu/${row.id}`)
                    .then(res => {
                        //
                        if (res.code === 2000) {
                            this.initData()
                            this.$message({
                                type: 'success',
                                message: '删除成功!'
                            })
                        }
                    })
            }).catch(() => {
                this.$message({
                    type: 'info',
                    message: '已取消删除'
                })

            })
        },
        initData() {
            let parsObj = {
                name: this.search.name,
                status: this.search.status
            }
            this.$api.get('api/menu', {
                params: parsObj
            }).then(res => {
                if (res.data) {
                    let arr = res.data.list
                    let id1 = arr.findIndex(item => {
                        if (item.id === '3') {
                            return true
                        }
                    })
                    arr.splice(id1, 0)
                    let response = JSON.parse(JSON.stringify(arr).replace(/parent_id/g, 'parentId'))
                    this.dataList = this.handleTree(response, 'id')
                    //
                }
            })

        },
        toggleExpandAll() {
            this.table = false
            this.isExpandAll = !this.isExpandAll
            this.$nextTick(() => {
                this.table = true
            })
        },
        handleSizeChange() {
            //
            this.initData()
        },
        handleCurrentChange() {
            //
            this.initData()
        }
    }
}
</script>
