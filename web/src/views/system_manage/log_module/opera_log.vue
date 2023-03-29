<template>
    <div>
        <page-main>
            <search-bar>
                <el-form ref="queryForm" :model="search" size="small" :inline="true" label-width="70px">
                    <el-form-item label="用户名">
                        <el-input v-model="search.username" style="width: 240px;" placeholder="请输入用户名" clearable
                                  @keydown.enter.native="searchHandle" @clear="searchHandle"
                        />
                    </el-form-item>

                    <el-form-item label="模块名称">
                        <el-input v-model="search.module" style="width: 240px;" placeholder="请输入模块名称" clearable
                                  @keydown.enter.native="searchHandle" @clear="searchHandle"
                        />
                    </el-form-item>

                    <el-form-item label="请求方法" prop="request_method">
                        <el-select v-model="search.request_method" style="width: 240px;" placeholder="请选择" clearable>
                            <el-option label="GET" value="GET" />
                            <el-option label="POST" value="POST" />
                            <el-option label="OPTIONS" value="OPTIONS" />
                            <el-option label="HEAD" value="HEAD" />
                            <el-option label="PUT" value="PUT" />
                            <el-option label="DELETE" value="DELETE" />
                            <el-option label="TRACE" value="TRACE" />
                            <el-option label="CONNECT" value="CONNECT" />
                        </el-select>
                    </el-form-item>

                    <el-form-item label="API状态" prop="api_status">
                        <el-select v-model="search.api_status" style="width: 240px;" placeholder="请选择" clearable>
                            <el-option label="无" value="" />
                            <el-option label="2000" value="2000" />
                            <el-option label="3000" value="3000" />
                            <el-option label="3001" value="3001" />
                            <el-option label="4002" value="4002" />
                            <el-option label="4003" value="4003" />
                            <el-option label="4004" value="4004" />
                            <el-option label="4100" value="4100" />
                            <el-option label="4101" value="4101" />
                            <el-option label="4102" value="4102" />
                            <el-option label="4300" value="4300" />
                        </el-select>
                    </el-form-item>

                    <el-form-item label="日期范围">
                        <el-date-picker
                            v-model="dateRange"
                            size="small"
                            style="width: 240px;"
                            value-format="yyyy-MM-dd"
                            type="daterange"
                            range-separator="-"
                            start-placeholder="开始日期"
                            end-placeholder="结束日期"
                        />
                        <!-- <el-date-picker v-model="search.beginTime" type="daterange" range-separator="至" start-placeholder="开始日期" end-placeholder="结束日期" /> -->
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
                        v-if="utils.hasPermission('opertlog.destroy') || utils.hasPermission ('*.*')"
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
                <el-col :span="1.5">
                    <el-button
                        v-if="utils.hasPermission('opertlog.trash') || utils.hasPermission ('*.*')"
                        type="danger"
                        plain
                        icon="el-icon-delete"
                        size="mini"
                        @click="handleClean"
                    >
                        清空
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
            <el-table ref="table" class="list-table" :data="dataList" :default-sort="defaultSort" :loading="loading"
                      border stripe highlight-current-row @sort-change="handleSortChange"
                      @selection-change="handleSelectionChange"
            >
                <el-table-column type="selection" width="55" align="center" />
                <el-table-column prop="module" label="模块名称" width="100" />
                <el-table-column prop="request_method" label="请求方法" width="80" />
                <!--                <el-table-column prop="admin_id" label="管理员ID" />-->
                <el-table-column prop="username" label="用户名" width="100" />
                <el-table-column prop="oper_url" label="操作路径" />
                <el-table-column prop="oper_ip" label="操作IP" />
                <el-table-column prop="oper_location" label="操作地址" />
                <el-table-column prop="status" label="状态" width="80">
                    <template slot-scope="scope">
                        <el-tag :size="scope.row.status==200?'success':'danger'">
                            {{ scope.row.status == 200 ? '成功' : '失败' }}
                        </el-tag>
                    </template>
                </el-table-column>
                <el-table-column prop="api_status" label="API状态" width="80">
                    <template slot-scope="scope">
                        <el-tag :size="scope.row.api_status==2000?'success':'danger'">
                            {{ scope.row.api_status == 2000 ? '成功' : '失败' }}
                        </el-tag>
                    </template>
                </el-table-column>
                <el-table-column prop="created_at" label="创建时间" width="160" sortable="custom"
                                 :sort-orders="['descending', 'ascending']"
                />
                <!-- <el-table-column prop="updated_at" label="更新时间" width="160" /> -->
                <el-table-column label="操作" width="80" fixed="right">
                    <template slot-scope="scope">
                        <el-button type="warning" size="mini" plain @click="getDeatais(scope.row)">详情</el-button>
                        <!-- <el-button type="danger" size="mini" plain @click="deleteHandle(scope.row)">删除</el-button>
                        <el-button type="warning" size="mini" plain @click="clearHandle(scope.row)">清空</el-button> -->
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
        </page-main>
        <el-dialog v-if="openDetailHadnle" :key="timer" title="详情" :visible.sync="openDetailHadnle" width="50%">
            <deatails :data-info="dataInfo" />
        </el-dialog>
    </div>
</template>
<script>
import deatails from './form'
export default {
    components: {
        deatails
    },
    props: {},
    data() {
        return {
            search: {
                module: '',
                username: '',
                request_method: '',
                api_status: '',
                beginTime: '',
                endTime: '',
                pageSize: '10',
                pageNum: '',
                orderByColumn: 'id',
                isAsc: 'desc'
            },
            dataList: [],
            loading: false,
            page: {
                currentPage: 1,
                pageSizes: [20, 50, 100],
                pageSize: 20,
                total: 0
            },
            // 选中数组
            ids: [],
            // 非多个禁用
            multiple: true,
            // 日期范围
            dateRange: [],
            // 默认排序
            defaultSort: {prop: 'created_at', order: 'desc'},
            dataInfo: {},
            openDetailHadnle: false,
            timer: ''
        }
    },
    mounted() {
        this.initData()
    },
    methods: {
        searchHandle() {
            this.page.currentPage = '1'
            if (this.dateRange) {
                this.search.beginTime = this.dateRange[0]
                this.search.endTime = this.dateRange[1]
            }

            this.initData()
        },
        getDeatais(row) {
            this.timer = new Date().getTime()
            this.dataInfo = row
            this.openDetailHadnle = true
        },
        /** 删除按钮操作 */
        handleDelete(row) {
            //
            const infoIds = row.id || this.ids
            this.$confirm('是否确认删除访问编号为' + infoIds + '的数据项？?', '确认信息', {
                confirmButtonText: '确定',
                cancelButtonText: '取消',
                type: 'warning'
            }).then(() => {
                this.$api.delete(`api/opertlog/${infoIds}`)
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
        /** 清空按钮操作 */
        handleClean() {
            this.$confirm('是否确认清空所有登录日志数据项', '确认信息', {
                confirmButtonText: '确定',
                cancelButtonText: '取消',
                type: 'warning'
            }).then(() => {
                this.$api.delete('api/opertlog/trash')
                    .then(res => {
                        //
                        if (res.code === 2000) {
                            this.initData()
                            this.$message({
                                type: 'success',
                                message: '清空成功!'
                            })
                        }
                    })
            }).catch(() => {
            })
        },
        deleteHandle(row) {
            this.$confirm('确定删除' + row.module + ', 是否继续?', '确认信息', {
                confirmButtonText: '确定',
                cancelButtonText: '取消',
                type: 'warning'
            }).then(() => {
                this.$message({
                    type: 'success',
                    message: '删除成功!'
                })
            }).catch(() => {
            })
        },
        // 多选框选中数据
        handleSelectionChange(selection) {
            this.ids = selection.map(item => item.id)
            // this.deleteHandle()
            this.single = selection.length != 1
            this.multiple = !selection.length
        },
        /** 重置按钮操作 */
        resetQuery(formName) {
            this.page.currentPage = '1'
            this.dateRange = []
            this.search = {}
            this.$refs[formName].resetFields()
            // this.$refs.tables.sort(this.defaultSort.prop, this.defaultSort.order)
            this.initData()
        },
        /** 排序触发事件 */
        handleSortChange(column) {
            //   this.search.orderByColumn = column.prop
            this.search.isAsc = column.order
            if (this.search.isAsc === 'ascending') {
                this.search.isAsc = 'asc'
            } else {
                this.search.isAsc = 'desc'
            }
            this.initData()
        },
        clearHandle(row) {
            this.$confirm('确定清空' + row.module + ', 是否继续?', '确认信息', {
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
            this.$api.get('api/opertlog', {
                params: this.search
            })
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
        }
    }
}
</script>
