<template>
    <div>
        <page-main>
            <el-button v-if="utils.hasPermission('globalrules.store') || utils.hasPermission ('*.*')"
                       size="small" type="primary" icon="el-icon-plus" @click="addHandle"
            >
                新增
            </el-button>
            <search-bar>
                <el-form ref="queryForm" :model="search" :inline="true" size="small" label-width="100px">
                    <el-form-item label="网站">
                        <el-select v-model="search.web_id" style="width: 100%;"
                                   filterable
                                   allow-create
                                   default-first-option
                                   placeholder="请选择网站"
                        >
                            <el-option v-for="item in webList"
                                       :key="item.id"
                                       :value="item.id"
                                       :label="item.name"
                            />
                        </el-select>
                        <!-- <el-input v-model="search.web_id" placeholder="请输入网站" clearable @keydown.enter.native="searchHandle" @clear="searchHandle" /> -->
                    </el-form-item>

                    <el-form-item label="类型">
                        <el-select v-model="search.type_id" multiple style="width: 100%;"
                                   filterable
                                   allow-create
                                   default-first-option
                                   placeholder="请选择类型"
                        >
                            <el-option v-for="item in roleList"
                                       :key="item.id"
                                       :value="item.id"
                                       :label="item.name"
                            />
                        </el-select>
                        <!-- <el-input v-model="search.type_id" placeholder="请输入类型" clearable @keydown.enter.native="searchHandle" @clear="searchHandle" /> -->
                    </el-form-item>

                    <el-form-item label="状态">
                        <el-select v-model="search.status">
                            <el-option label="全部" value="" />
                            <el-option value="0" label="禁用" />
                            <el-option value="1" label="阻断" />
                            <el-option value="2" label="告警" />
                        </el-select>
                    </el-form-item>

                    <el-form-item>
                        <el-button type="primary" icon="el-icon-search" @click="searchHandle">筛 选</el-button>
                        <el-button icon="el-icon-refresh" size="mini" @click="resetQuery('queryForm')">重置</el-button>
                    </el-form-item>
                </el-form>
            </search-bar>
            <!-- <batch-action-bar v-if="batch.enable" :data="dataList" :selection-data="batch.selectionDataList" @check-all="$refs.table.toggleAllSelection()" @check-null="$refs.table.clearSelection()"> -->
            <el-row :gutter="10" class="mb8">
                <el-button
                    v-if="utils.hasPermission('globalrules.syncconfig') || utils.hasPermission ('*.*')"
                    type="primary"
                    plain
                    size="mini"
                    @click="handlConfig"
                >
                    同步策略
                </el-button>
                <el-button v-if="utils.hasPermission('globalrules.changestatus') || utils.hasPermission ('*.*')" size="small" plain
                           type="warning" :disabled="multiple1"
                           @click="multiOnchangeHandle"
                >
                    改变状态
                </el-button>
                <el-button v-if="utils.hasPermission('globalrules.destroy') || utils.hasPermission ('*.*')" size="small" plain icon="el-icon-delete"
                           type="danger" :disabled="multiple"
                           @click="handleDelete"
                >
                    删除
                </el-button>
            </el-row>
            <!-- <el-button size="small" @click="multiOnchangeHandle($event)">批量操作</el-button> -->
            <!-- </batch-action-bar> -->
            <!-- @selection-change="batch.selectionDataList = $event" -->
            <el-table ref="table" class="list-table" :data="dataList" :loading="loading" border stripe
                      highlight-current-row
                      @selection-change="handleSelectionChange"
            >
                <el-table-column type="selection" width="55" align="center" />
                <!-- <el-table-column prop="web_id" label="网站" /> -->
                <el-table-column prop="request_uri" label="请求url" />
                <el-table-column prop="describe" label="描述" />
                <el-table-column prop="is_black" width="80" label="黑名单">
                    <template slot-scope="scope">
                        {{ scope.row.is_black == 0 ? '不加黑' : '加黑' }}
                    </template>
                </el-table-column>
                <el-table-column prop="status" label="状态" width="70">
                    <template slot-scope="scope">
                        <el-tag :size="scope.row.status == 0?'danger': scope.row.status == 1?'':'warning'">
                            {{ scope.row.status == 0 ? '禁用' : scope.row.status == 1 ? '阻断' : '告警' }}
                        </el-tag>
                        <!-- <el-switch v-model="scope.row.status" @change="onChangeStatus($event, scope.row)" /> -->
                    </template>
                </el-table-column>
                <el-table-column prop="admin_id" label="管理员" width="100" />
                <el-table-column prop="created_at" label="创建时间" width="160" />
                <!-- <el-table-column prop="request_method" label="响应方法" />
                <el-table-column prop="param_site" label="参数">
                    <template slot-scope="scope">
                        <el-popover trigger="hover" placement="top">
                            <div v-for="(item, index) in scope.row.param_content" :key="index">
                                <p>key: {{ item.key }}</p>
                                <p>value: {{ item.value }}</p>
                                <p>operator: {{ $common.replaceStr(item.operator) }}</p>
                            </div>
                            <div slot="reference" class="name-wrapper">
                                <el-tag size="medium">{{ scope.row.param_site }}</el-tag>
                            </div>
                        </el-popover>
                    </template>
                </el-table-column>

                <el-table-column prop="black_type" label="黑名单类型">
                    <template slot-scope="scope">
                        {{ scope.row.black_type == 0?'永久': scope.row.black_type == 1?'小时':'天' }}
                    </template>
                </el-table-column>
                <el-table-column prop="black_num" label="黑名单数量" />
                <el-table-column prop="black_time" label="黑名单时间" />
                <el-table-column prop="type_id" label="类型" />
                <el-table-column prop="rule_content" label="规则内容" />

                <el-table-column prop="updated_at" label="更新时间" width="160" /> -->
                <el-table-column label="操作" width="160" fixed="right">
                    <template slot-scope="scope">
                        <el-button v-if="utils.hasPermission('globalrules.update') || utils.hasPermission ('*.*')" type="primary" size="mini" plain @click="editHandle(scope.row)">编辑</el-button>
                        <el-button v-if="utils.hasPermission('globalrules.show') || utils.hasPermission ('*.*')" type="warning" size="mini" plain @click="detailHandle(scope.row)">详情</el-button>
                        <!-- <el-button type="danger" size="mini" plain @click="deleteHandle(scope.row)">删除</el-button> -->
                        <!-- <el-dropdown @command="handleMoreOperating($event, scope.row)">
                            <el-button size="mini">
                                更多操作<i class="el-icon-arrow-down el-icon--right" />
                            </el-button>
                            <el-dropdown-menu slot="dropdown">
                                <el-dropdown-item command="detail">详情</el-dropdown-item>
                                <el-dropdown-item command="sync">同步策略</el-dropdown-item>
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
        </page-main>

        <el-dialog v-if="openDetailHadnle" :key="timer" title="详情" :visible.sync="openDetailHadnle" width="70%">
            <stationManageDetail :id-info="idInfo" />
        </el-dialog>
        <el-dialog v-if="openEditHadnle" :key="timer" :title="titleText" :visible.sync="openEditHadnle" width="60%"
                   :close-on-click-modal="false" append-to-body destroy-on-close
        >
            <stationManageEdit :data-info="dataInfo" @openEditShow="openEditShow" @formList="initData" />
        </el-dialog>
        <el-dialog title="改变状态" :visible.sync="multiStatusVisible" width="30%">
            <el-form :model="multiStatusForm">
                <el-form-item label="状态">
                    <el-radio-group v-model="multiStatusForm.status">
                        <el-radio :label="&quot;0&quot;">禁用</el-radio>
                        <el-radio :label="&quot;1&quot;">阻断</el-radio>
                        <el-radio :label="&quot;2&quot;">告警</el-radio>
                    </el-radio-group>
                </el-form-item>
            </el-form>
            <div slot="footer" class="dialog-footer">
                <el-button @click="multiStatusVisible = false">取 消</el-button>
                <el-button type="primary" @click="handleStatus">确 定</el-button>
            </div>
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
                web_id: '',
                type_id: '',
                status: '',
                pageSize: '10',
                pageNum: '1',
                orderByColumn: 'id',
                isAsc: 'desc'
            },
            // 批量操作
            batch: {
                enable: true,
                selectionDataList: []
            },
            multiStatusVisible: false,
            multiStatusForm: {
                status: '0'
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
            webList: [],
            multiple1: true,
            multiple: true,
            ids: []
        }
    },
    mounted() {
        this.initData()
        this.getRoleList()
    },
    methods: {
        getRoleList() {
            this.$api.post('api/web/searchList')
                .then(res => {
                    //
                    if (res.data) {
                        this.webList = res.data
                    }
                })
            this.$api.post('api/ruleType/searchList')
                .then(res => {
                    //
                    if (res.data) {
                        this.roleList = res.data
                    }
                })
        },
        searchHandle() {
            //
            this.initData()
        },

        onChangeStatus(val, row) {
            this.$confirm(`确认${val ? '启用' : '停用'}「${row.web_id}」吗？`, '确认信息').then(() => {
                // this.$api.post('mock/pages_example/manager/change/status', {
                //     id: row.id,
                //     status: val
                // }).then(() => {
                //     this.$message.success({
                //         message: `模拟${val ? '启用' : '停用'}成功`,
                //         center: true
                //     })
                // })
            }).catch(() => {
                row.status = !val
            })
        },
        addHandle() {
            // this.$router.push({name: 'webProtectAdd'})
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
            // this.$router.push({name: 'webProtectEdit', params: {id: row.id, row: row}})
            this.timer = new Date().getTime()
            this.titleText = '编辑'
            this.openEditHadnle = true
            this.dataInfo = row
        },
        handleMoreOperating(command, row) {
            switch (command) {
                case 'detail':
                    this.detailHandle(row)
                    break
                case 'sync':
                    this.syncHandle(row)
                    break
                case 'delete':
                    this.deleteHandle(row)
                    break
            }
        },
        detailHandle(row) {
            // this.$router.push({name: 'webProtectDetail', params: {id: row.id}})
            this.openDetailHadnle = true
            this.idInfo = row.id
        },
        syncHandle(row) {
            this.$confirm(`确认将「${row.web_id}」执行同步策略 吗？`, '确认信息').then(() => {

            }).catch(() => {
            })
        },
        // 多选框选中数据
        handleSelectionChange(selection) {
            this.ids = selection.map(item => item.id)
            // this.deleteHandle()
            this.single = selection.length != 1
            this.multiple = !selection.length
            this.multiple1 = !selection.length
        },
        /** 删除按钮操作 */
        handleDelete(row) {
            const infoIds = row.id || this.ids
            this.$confirm('是否确认删除该条' + '的数据项？?', '确认信息', {
                confirmButtonText: '确定',
                cancelButtonText: '取消',
                type: 'warning'
            }).then(() => {
                this.$api.delete(`api/globalRules/${infoIds}`)
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
        multiOnchangeHandle() {
            this.multiStatusVisible = true
        },
        handleStatus(row) {
            const infoIds = row.id || this.ids
            // this.$confirm('是否确认改变该条' + '的数据项？?', '确认信息', {
            //     confirmButtonText: '确定',
            //     cancelButtonText: '取消',
            //     type: 'warning'
            // }).then(() => {
            this.$api.put('api/globalRules/changeStatus/', {
                id: infoIds,
                status: this.multiStatusForm.status
            })
                .then(res => {
                    if (res.code === 2000) {
                        this.initData()
                        this.$message({
                            type: 'success',
                            message: '操作成功!'
                        })
                    }
                })

            // }).catch(() => {
            //     this.$message({
            //         type: 'info',
            //         message: '已取消删除'
            //     })
            // })
        },
        deleteHandle(row) {
            this.$confirm('确定删除' + row.web_id + ', 是否继续?', '提示', {
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
            this.$api.get('api/globalRules', {
                params: this.search
            })
                .then(res => {
                    if (res.data) {
                        this.dataList = res.data.list
                        this.page.total = Number(res.data.count)
                        this.multiStatusVisible = false
                    }

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
        handlConfig() {
            this.$confirm('确定同步?', '提示', {
                confirmButtonText: '确定',
                cancelButtonText: '取消',
                type: 'warning'
            }).then(() => {
                this.loading = true
                this.$api.post('api/globalRules/syncConfig')
                    .then(res => {
                        if (res.data) {
                            this.initData()
                            this.$message({
                                type: 'success',
                                message: '操作成功!'
                            })
                        }
                    })
            })
        },
        /** 重置按钮操作 */
        resetQuery(formName) {
            this.page.currentPage = 1
            this.dateRange = []
            this.search = {}
            this.$refs[formName].resetFields()
            // this.$refs.tables.sort(this.defaultSort.prop, this.defaultSort.order)
            this.initData()
        }
    }
}
</script>
