<template>
    <div>
        <page-main>
            <search-bar>
                <el-form ref="queryForm" :model="search" size="small" :inline="true" label-width="70px">
                    <el-form-item label="登录人">
                        <el-input v-model="search.loginName" style="width: 240px;" placeholder="请输入登录人" clearable @keydown.enter.native="searchHandle" @clear="searchHandle" />
                    </el-form-item>

                    <el-form-item label="登录IP">
                        <el-input v-model="search.ipaddr" style="width: 240px;" placeholder="请输入登录IP" clearable @keydown.enter.native="searchHandle" @clear="searchHandle" />
                    </el-form-item>

                    <el-form-item label="状态">
                        <el-select v-model="search.status" style="width: 240px;">
                            <el-option label="全部" value="" />
                            <el-option label="成功" value="1" />
                            <el-option label="失败" value="0" />
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
                        v-if="utils.hasPermission('loginlog.destroy') || utils.hasPermission ('*.*')"
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
                        v-if="utils.hasPermission('loginlog.trash') || utils.hasPermission ('*.*')"
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
                      border stripe highlight-current-row @sort-change="handleSortChange" @selection-change="handleSelectionChange"
            >
                <el-table-column type="selection" width="55" align="center" />
                <el-table-column prop="id" label="ID" width="80" />
                <el-table-column prop="login_name" label="登录人" width="120" />
                <el-table-column prop="ipaddr" label="登录IP" />
                <el-table-column prop="login_location" label="登录地址" />
                <el-table-column prop="browser" label="浏览器" width="200" />
                <el-table-column prop="os" label="操作系统" />
                <el-table-column prop="net" label="网络" width="80" />
                <el-table-column prop="status" label="状态" width="70">
                    <template slot-scope="scope">
                        <el-tag :size="scope.row.status==1?'success':'danger'">{{ scope.row.status==1?'成功':'失败' }}</el-tag>
                    </template>
                </el-table-column>
                <el-table-column prop="msg" label="消息" width="100" />
                <el-table-column prop="created_at" label="创建时间" width="160" sortable="custom" :sort-orders="['descending', 'ascending']" />
                <!-- <el-table-column prop="updated_at" label="更新时间" /> -->
                <!-- <el-table-column label="操作" width="200" fixed="right">
                    <template slot-scope="scope">
                        <el-button type="danger" size="mini" plain @click="deleteHandle(scope.row)">删除</el-button>
                        <el-button type="warning" size="mini" plain @click="clearHandle(scope.row)">清空</el-button>
                    </template>
                </el-table-column> -->
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
    </div>
</template>
<script>

export default {
    props: {},
    data() {
        return {
            search: {
                loginName: '',
                ipaddr: '',
                status: '',
                beginTime: '',
                endTime: '',
                pageSize: '10',
                pageNum: '1',
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
            defaultSort: { prop: 'created_at', order: 'desc' }
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
            this.initData()
        },
        deleteHandle(row) {
            this.ids = row.id
            this.$confirm('确定删除' + row.login_name + ', 是否继续?', '确认信息', {
                confirmButtonText: '确定',
                cancelButtonText: '取消',
                type: 'warning'
            }).then(() => {
                this.$api.delete(`api/loginlog/${this.ids}`)
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
        /** 删除按钮操作 */
        handleDelete(row) {
            const infoIds = row.id || this.ids
            this.$confirm('是否确认删除访问编号为' + infoIds + '的数据项？?', '确认信息', {
                confirmButtonText: '确定',
                cancelButtonText: '取消',
                type: 'warning'
            }).then(() => {
                this.$api.delete(`api/loginlog/${infoIds}`)
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
                this.$api.delete('api/loginlog/trash')
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
            }).catch(() => {})
        },
        // 多选框选中数据
        handleSelectionChange(selection) {
            this.ids = selection.map(item => item.id)
            // this.deleteHandle()
            this.single = selection.length != 1
            this.multiple = !selection.length
        },
        clearHandle(row) {
            this.$confirm('确定清空' + row.login_name + ', 是否继续?', '确认信息', {
                confirmButtonText: '确定',
                cancelButtonText: '取消',
                type: 'warning'
            }).then(() => {
                this.$message({
                    type: 'success',
                    message: '删除成功!'
                })
            }).catch(() => {})
        },
        initData() {
            this.$api.get('api/loginlog', {
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

