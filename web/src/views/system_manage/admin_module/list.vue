<template>
    <div>
        <page-main>
            <el-button v-if="utils.hasPermission('manage.store') || utils.hasPermission ('*.*')" type="primary"
                       size="small" icon="el-icon-plus" @click="addHandle"
            >
                新增
            </el-button>
            <search-bar>
                <el-form ref="queryForm" :model="search" size="small" :inline="true" label-width="100px">
                    <el-form-item label="用户名">
                        <el-input v-model="search.username" placeholder="请输入用户名" clearable @keydown.enter.native="searchHandle" @clear="searchHandle" />
                    </el-form-item>

                    <el-form-item label="状态">
                        <el-select v-model="search.status">
                            <el-option
                                v-for="item in statusList"
                                :key="item.value"
                                :label="item.label"
                                :value="item.value"
                            />
                            <!-- <option label="全部" value="" />
                            <option label="启用" value="1" />
                            <option label="冻结" value="0" /> -->
                        </el-select>
                    </el-form-item>

                    <el-form-item label="日期">
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
                        v-if="utils.hasPermission('manage.destroy') || utils.hasPermission ('*.*')"
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
            <el-table ref="table" class="list-table" :data="dataList"
                      :loading="loading" border stripe highlight-current-row @selection-change="handleSelectionChange"
            >
                <el-table-column type="selection" width="55" align="center" />
                <el-table-column prop="username" label="用户名" />
                <el-table-column prop="realname" label="真实姓名" />
                <el-table-column prop="email" label="邮箱" />
                <el-table-column prop="last_ip" label="最后登录IP" width="120" />
                <el-table-column prop="last_time" label="最后登录时间" width="160" />
                <el-table-column prop="status" label="状态" width="80">
                    <template slot-scope="scope">
                        <!-- <el-tag :size="scope.row.status==1?'success':'danger'">{{ scope.row.status==1?'启用':'冻结' }}</el-tag> -->
                        <!-- {{ scope.row.status == 1?'启用':'冻结' }} -->
                        <el-switch v-if="utils.hasPermission('manage.changestatus') || utils.hasPermission ('*.*')" v-model="scope.row.status" active-value="1"
                                   inactive-value="0" @change="onChangeStatus(scope.row.status, scope.row)"
                        />
                        <div v-else>
                            <el-tag :size="scope.row.status==1?'success':'danger'">{{ scope.row.status==1?'启用':'冻结' }}</el-tag>
                        </div>
                    </template>
                </el-table-column>
                <el-table-column prop="created_at" label="创建时间" width="160" />
                <!--<el-table-column prop="updated_at" label="更新时间" width="160" />-->
                <!--<el-table-column prop="note" label="备注" />-->
                <el-table-column label="操作" width="200" align="center">
                    <template slot-scope="scope">
                        <el-button v-if="utils.hasPermission('manage.update') || utils.hasPermission ('*.*')" type="primary" size="mini" plain @click="editHandle(scope.row)">编辑</el-button>
                        <el-dropdown @command="handleMoreOperating($event, scope.row)">
                            <el-button size="mini">
                                更多操作<i class="el-icon-arrow-down el-icon--right" />
                            </el-button>
                            <el-dropdown-menu slot="dropdown">
                                <el-dropdown-item command="detail">详情</el-dropdown-item>
                                <el-dropdown-item v-if="utils.hasPermission('manage.resetpwd') || utils.hasPermission ('*.*')" command="resetPassword">重置密码</el-dropdown-item>
                                <!-- <el-dropdown-item command="delete" divided>删除</el-dropdown-item> -->
                            </el-dropdown-menu>
                        </el-dropdown>
                        <!-- <el-button type="danger" size="mini" plain @click="deleteHandle(scope.row)">删除</el-button> -->
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
        <el-dialog v-if="openEditHadnle" :key="timer" :title="titleText" :visible.sync="openEditHadnle" width="700px">
            <stationManageEdit :data-info="dataInfo" @openEditShow="openEditShow" @formList="initData" />
        </el-dialog>
        <el-dialog v-if="openDetailHadnle" :key="timer" title="详情" :visible.sync="openDetailHadnle" width="700px">
            <stationManageDetail :id-info="idInfo" />
        </el-dialog>
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
                username: '',
                status: '',
                beginTime: '',
                pageSize: 10,
                pageNum: 1,
                orderByColumn: 'id',
                isAsc: 'desc',
                endTime: ''
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
            dateRange: [],
            multiple: true,
            // 选中数组
            ids: [],
            statusList: [
                {
                    value: '',
                    label: '全部'
                }, {
                    value: '1',
                    label: '启用'
                }, {
                    value: '0',
                    label: '停用'
                }
            ]
        }
    },
    mounted() {
        this.initData()
    },
    methods: {
        searchHandle() {
            if (this.dateRange) {
                this.search.beginTime = this.dateRange[0]
                this.search.endTime = this.dateRange[1]
            }

            this.initData()
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
        onChangeStatus(val, row) {
            this.$confirm(`确认${val == 1 ? '启用' : '停用'}「${row.username}」吗？`, '确认信息').then(() => {
                this.$api.put('api/manage/changeStatus', {
                    id: row.id

                }).then(() => {
                    this.$message.success({
                        message: `${val == 1 ? '启用' : '停用'}成功`,
                        center: true
                    })
                    this.initData()
                })
            }).catch(() => {
                row.status = !val
            })
        },
        openEditShow(val) {
            this.openEditHadnle = val
        },
        addHandle() {
            //
            // this.$router.push({name: 'adminModuleAdd'})
            this.timer = new Date().getTime()
            this.titleText = '新增管理员'
            this.openEditHadnle = true
        },
        editHandle(row) {
            //
            // this.$router.push({name: 'adminModuleEdit', params: {id: row.id, row: row}})
            this.timer = new Date().getTime()
            this.titleText = '编辑管理员'
            this.openEditHadnle = true
            this.dataInfo  = row
        },
        handleMoreOperating(command, row) {
            switch (command) {
                case 'detail':
                    this.detailHandle(row)
                    break
                case 'resetPassword':
                    this.resetPassHandle(row)
                    break
                case 'delete':
                    this.deleteHandle(row)
                    break
            }
        },
        detailHandle(row) {
            //
            this.timer = new Date().getTime()
            // this.$router.push({name: 'adminModuleDetail', params: {id: row.id}})
            this.openDetailHadnle = true
            this.idInfo = row.id
        },
        resetPassHandle(row) {
            let pass = 'DXyf@2022'
            this.$confirm(`确认将「${row.username}」的密码重置为 “${pass}” 吗？`, '确认信息').then(() => {
                this.$api.put('api/manage/resetPwd', {
                    id: row.id,
                    password: pass
                }).then(() => {
                    this.$message.success({
                        message: '重置成功',
                        center: true
                    })
                    this.initData()
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
        // /** 重置按钮操作 */
        // resetQuery(formName) {
        //     this.page.currentPage = '1'
        //     this.dateRange = []
        //     this.search = {}
        //     this.$refs[formName].resetFields()
        //     // this.$refs.tables.sort(this.defaultSort.prop, this.defaultSort.order)
        //     this.initData()
        // },
        /** 删除按钮操作 */
        handleDelete(row) {
            const infoIds = row.id || this.ids
            this.$confirm('是否确认删除该条' + '的数据项？?', '确认信息', {
                confirmButtonText: '确定',
                cancelButtonText: '取消',
                type: 'warning'
            }).then(() => {
                this.$api.delete(`api/manage/${infoIds}`)
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
            this.$confirm('确定删除' + row.username + ', 是否继续?', '确认信息', {
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
            this.$api.get('api/manage', {
                params: this.search
            })
                .then(res => {
                    //
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
