<template>
    <div>
        <page-main>
            <el-button v-if="utils.hasPermission('config.store') || utils.hasPermission ('*.*')"
                       size="small" type="primary" icon="el-icon-plus" @click="addHandle"
            >
                新增
            </el-button>
            <search-bar>
                <el-form ref="queryForm" :model="search" :inline="true" size="small" label-width="100px">
                    <el-form-item label="模块名">
                        <el-input v-model="search.name" placeholder="请输入模块名" clearable @keydown.enter.native="searchHandle" @clear="searchHandle" />
                    </el-form-item>

                    <el-form-item label="模块类型">
                        <el-select v-model="search.type">
                            <el-option label="默认" value="0" />
                            <el-option label="文件" value="1" />
                            <el-option label="图片" value="2" />
                        </el-select>
                    </el-form-item>
                    <el-form-item label="key">
                        <el-input v-model="search.key" placeholder="请输入key" clearable @keydown.enter.native="searchHandle" @clear="searchHandle" />
                    </el-form-item>
                    <el-form-item label="value">
                        <el-input v-model="search.value" placeholder="请输入value" clearable @keydown.enter.native="searchHandle" @clear="searchHandle" />
                    </el-form-item>
                    <!-- <el-col :span="8">
                            <el-form-item label="key">
                                <el-input v-model="search.key" placeholder="请输入key" clearable @keydown.enter.native="searchHandle" @clear="searchHandle" />
                            </el-form-item>
                        </el-col>
                        <el-col :span="8">
                            <el-form-item label="value">
                                <el-input v-model="search.value" placeholder="请输入value" clearable @keydown.enter.native="searchHandle" @clear="searchHandle" />
                            </el-form-item>
                        </el-col> -->

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
                        v-if="utils.hasPermission('config.destroy') || utils.hasPermission ('*.*')"
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

                <!-- <el-col :span="1.5">
        <el-button
          type="warning"
          plain
          icon="el-icon-download"
          size="mini"
          @click="handleExport"
        >导出</el-button>
      </el-col> -->
            </el-row>
            <el-table ref="table" class="list-table" :data="dataList" :loading="loading" border stripe highlight-current-row
                      :default-sort="{prop: 'id', order: 'descending'}"
                      @selection-change="handleSelectionChange"
            >
                <el-table-column type="selection" width="55" align="center" />
                <el-table-column prop="name" label="模块名" sortable />
                <el-table-column prop="type" label="配置类型" sortable>
                    <template slot-scope="scope">
                        {{ scope.row.type == 0?'默认':scope.row.type==1?'文件':'图片' }}
                    </template>
                </el-table-column>
                <el-table-column prop="key" label="key" />
                <el-table-column prop="value" label="value">
                    <template slot-scope="scope">
                        {{ $common.escape2Html(scope.row.value) }}
                    </template>
                </el-table-column>
                <el-table-column prop="created_at" label="创建时间" width="160" />
                <el-table-column prop="updated_at" label="更新时间" width="160" />
                <el-table-column label="操作" width="200" align="center">
                    <template slot-scope="scope">
                        <el-button v-if="utils.hasPermission('config.update') || utils.hasPermission ('*.*')" type="primary" size="mini" plain @click="editHandle(scope.row)">编辑</el-button>
                        <el-button type="warning" size="mini" plain @click="detailHandle(scope.row)">详情</el-button>
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
            <el-dialog v-if="openEditHadnle" :key="timer" :title="titleText" :visible.sync="openEditHadnle" width="700px">
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
                type: '',
                key: '',
                value: '',
                pageNum: '1',
                pageSize: '10',
                orderByColumn: 'id',
                isAsc: 'asc'
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
            idInfo: '1',
            // 选中数组
            ids: [],
            // 非多个禁用
            multiple: true
        }
    },
    mounted() {
        this.initData()
    },
    methods: {
        searchHandle() {
            this.initData()
            //
        },
        /** 重置按钮操作 */
        resetQuery(formName) {
            this.page.currentPage = 1
            this.dateRange = []
            this.search = {}
            this.$refs[formName].resetFields()
            // this.$refs.tables.sort(this.defaultSort.prop, this.defaultSort.order)
            this.initData()
        },
        addHandle() {
            // this.$router.push({name: 'configModuleAdd'})
            this.timer = new Date().getTime()
            this.titleText = '新增'
            this.openEditHadnle = true
            this.dataInfo = ''
        },
        openEditShow(val) {
            this.openEditHadnle = val
        },
        editHandle(row) {
            //
            // this.$router.push({name: 'configModuleEdit', params: {id: row.id, row: row}})
            this.timer = new Date().getTime()
            this.titleText = '编辑'
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
            //
            this.timer = new Date().getTime()
            this.idInfo = row.id
            // this.$router.push({name: 'configModuleDetail', params: {id: row.id}})
            this.openDetailHadnle = true
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
            //
            this.$confirm('是否确认删除该' + '数据项？?', '确认信息', {
                confirmButtonText: '确定',
                cancelButtonText: '取消',
                type: 'warning'
            }).then(() => {
                this.$api.delete(`api/config/${infoIds}`)
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
            this.loading = true
            this.$api.get('api/config', {
                params: this.search
            })
                .then(res => {
                    this.loading = false
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
        }
    }
}
</script>
