<template>
    <div>
        <page-main>
            <el-button v-if="utils.hasPermission('assets.store') || utils.hasPermission ('*.*')" type="primary" icon="el-icon-plus" @click="addHandle">新增</el-button>
            <search-bar>
                <el-form ref="queryForm" :model="search" :inline="true" size="small" label-width="100px">
                    <el-form-item label="单位名称">
                        <el-select v-model="search.group_id" style="width: 100%;"

                                   placeholder="请选择单位"
                        >
                            <el-option v-for="item in roleList"
                                       :key="item.id"
                                       :value="item.id"
                                       :label="item.group_name"
                            />
                        </el-select>
                        <!-- <el-input v-model="search.name" placeholder="请输入单位名称" clearable @keydown.enter.native="searchHandle" @clear="searchHandle" /> -->
                    </el-form-item>

                    <el-form-item label="联系人">
                        <el-input v-model="search.concat" placeholder="请输入联系人" clearable @keydown.enter.native="searchHandle" @clear="searchHandle" />
                    </el-form-item>

                    <el-form-item label="联系方式">
                        <el-input v-model="search.phone" placeholder="请输入联系方式" clearable @keydown.enter.native="searchHandle" @clear="searchHandle" />
                    </el-form-item>

                    <el-form-item>
                        <el-button type="primary" icon="el-icon-search" @click="searchHandle">筛 选</el-button>
                        <el-button icon="el-icon-refresh" size="mini" @click="resetQuery('queryForm')">重置</el-button>
                    </el-form-item>
                </el-form>
            </search-bar>
            <el-row :gutter="10" class="mb8">
                <el-col :span="1.5">
                    <!-- v-hasPermi="['monitor:logininfor:remove']" -->
                    <el-button
                        v-if="utils.hasPermission('assets.destroy') || utils.hasPermission ('*.*')"
                        type="danger"
                        plain
                        icon="el-icon-delete"
                        size="mini"
                        :disabled="multiple"
                        @click="handleDelete"
                    >
                        删除
                    </el-button>
                </el-col>
            </el-row>
            <el-table ref="table" class="list-table" :data="dataList" :loading="loading" border
                      stripe highlight-current-row @selection-change="handleSelectionChange"
            >
                <el-table-column type="selection" width="55" align="center" />
                <el-table-column prop="group_id" label="单位名称" />
                <el-table-column prop="ip" label="IP" />
                <el-table-column prop="contact" label="联系人" />
                <el-table-column prop="phone" label="联系方式" />
                <el-table-column prop="created_at" label="创建时间" />
                <el-table-column prop="updated_at" label="更新时间" />
                <el-table-column label="操作" width="200" align="center">
                    <template slot-scope="scope">
                        <el-button v-if="utils.hasPermission('assets.update') || utils.hasPermission ('*.*')" type="primary" size="mini" plain @click="editHandle(scope.row)">编辑</el-button>
                        <el-button v-if="utils.hasPermission('assets.show') || utils.hasPermission ('*.*')" type="warning" size="mini" plain @click="detailHandle(scope.row)">详情</el-button>
                        <!-- <el-dropdown @command="handleMoreOperating($event, scope.row)">
                            <el-button size="mini">
                                更多操作<i class="el-icon-arrow-down el-icon--right" />
                            </el-button>
                            <el-dropdown-menu slot="dropdown">
                                <el-dropdown-item command="detail">详情</el-dropdown-item>
                                <el-dropdown-item command="delete" divided>删除</el-dropdown-item>
                            </el-dropdown-menu>
                        </el-dropdown> -->
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
            <el-dialog v-if="openDetailHadnle" :key="timer" title="详情" :visible.sync="openDetailHadnle" width="50%">
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
                concat: '',
                phone: '',
                group_id: '',
                ip: '',
                orderByColumn: 'id',
                isAsc: 'desc',
                pageSize: '10',
                pageNum: '1'
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
            dataInfo: '',
            idInfo: '',
            roleList: [],
            multiple: true,
            ids: []
        }
    },
    mounted() {
        this.initData()
        setTimeout(() => {
            this.getRoleList()
        }, 500)

    },
    methods: {
        searchHandle() {
            //
            this.initData()
        },
        getRoleList() {

            // web/searchList
            this.$api.post('api/group/searchList')
                .then(res => {
                    //
                    if (res.data) {
                        this.roleList  = res.data
                        this.dataList.forEach(item => {
                            this.roleList.forEach(items => {
                                if (item.group_id === items.id) {
                                    item.group_id = items.group_name
                                }
                            })
                        })
                    }
                })
        },
        addHandle() {
            // this.$router.push({name: 'propertyManageAdd'})
            this.timer = new Date().getTime()
            this.titleText = '新增'
            this.dataInfo = ''
            this.openEditHadnle = true
        },
        editHandle(row) {
            //
            this.timer = new Date().getTime()
            this.titleText = '编辑'
            this.openEditHadnle = true
            this.dataInfo  = row
            // this.$router.push({name: 'propertyManageEdit', params: {id: row.id, row: row}})
        },
        openEditShow(val) {
            this.openEditHadnle = val
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
            this.timer = new Date().getTime()
            this.openDetailHadnle = true
            this.idInfo = row.id
            // this.$router.push({name: 'propertyManageDetail', params: {id: row.id}})

        },
        // 多选框选中数据
        handleSelectionChange(selection) {
            this.ids = selection.map(item => item.id)
            // this.deleteHandle()
            this.single = selection.length != 1
            this.multiple = !selection.length
        },
        /** 删除按钮操作 */
        handleDelete(row) {
            const infoIds = row.id || this.ids
            this.$confirm('是否确认删除该条' + '的数据项？?', '确认信息', {
                confirmButtonText: '确定',
                cancelButtonText: '取消',
                type: 'warning'
            }).then(() => {
                this.$api.delete(`api/assets/${infoIds}`)
                    .then(res => {
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
        deleteHandle(row) {
            this.$confirm('确定删除' + row.name + ', 是否继续?', '提示', {
                confirmButtonText: '确定',
                cancelButtonText: '取消',
                type: 'warning'
            }).then(() => {
                this.$message({
                    type: 'success',
                    message: '删除成功!'
                })
            }).catch(() => {
                this.$message({
                    type: 'info',
                    message: '已取消删除'
                })
            })
        },
        initData() {
            this.$api.get('api/assets', {
                params: this.search
            }
            )
                .then(res => {
                    //
                    this.dataList = res.data.list
                    this.page.total = Number(res.data.count)
                })
        },
        handleSizeChange(val) {
            //
            this.search.pageSize = val
            this.initData()
        },
        handleCurrentChange(val) {
            //
            this.search.pageNum = val
            this.initData()
        },
        /** 重置按钮操作 */
        resetQuery(formName) {
            this.page.currentPage = 1
            // this.search = {}
            this.search.phone = ''
            this.search.concat = ''
            this.search.group_id = ''
            this.$refs[formName].resetFields()
            // this.$refs.tables.sort(this.defaultSort.prop, this.defaultSort.order)
            this.initData()
        }
    }
}
</script>
