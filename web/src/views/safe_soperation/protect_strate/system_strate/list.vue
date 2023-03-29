<template>
    <div>
        <page-main>
            <!-- <el-button type="primary" icon="el-icon-plus" @click="addHandle">新增</el-button> -->
            <search-bar>
                <el-form ref="queryForm" :inline="true" :model="search" size="small" label-width="100px">
                    <el-form-item label="是否加黑">
                        <el-select v-model="search.is_black" placeholder="请选择">
                            <el-option label="全部" value="" />
                            <el-option label="是" value="1" />
                            <el-option label="否" value="0" />
                        </el-select>
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
                        <!-- <el-input v-model="search.type_id" /> -->
                    </el-form-item>

                    <el-form-item>
                        <el-button type="primary" icon="el-icon-search" @click="searchHandle">筛 选</el-button>
                        <el-button icon="el-icon-refresh" size="mini" @click="resetQuery('queryForm')">重置</el-button>
                    </el-form-item>
                </el-form>
            </search-bar>
            <el-row :gutter="10" class="mb8">
                <el-button
                    v-if="utils.hasPermission('sysrules.syncconfig')"
                    type="primary"
                    plain
                    size="mini"
                    @click="handlConfig"
                >
                    同步策略
                </el-button>
            </el-row>
            <el-table ref="table" class="list-table" :data="dataList" :loading="loading" border stripe
                      highlight-current-row
            >
                <el-table-column prop="id" label="Id" />
                <el-table-column prop="type_name" label="类型名称" />
                <!-- <el-table-column prop="rule_content" label="规则内容">
                    <template slot-scope="scope">
                        <el-popover trigger="hover" placement="top">
                            <p>规则内容: {{ scope.row.rule_content }}</p>
                            <div slot="reference" class="name-wrapper">
                                <el-tag size="medium">{{ scope.row.black_type }}</el-tag>
                            </div>
                        </el-popover>
                    </template>
                </el-table-column> -->
                <el-table-column prop="is_black" label="是否加黑">
                    <template slot-scope="scope">
                        {{ scope.row.is_black == 0 ? '不加黑' : '加黑' }}
                    </template>
                </el-table-column>
                <!-- <el-table-column prop="black_type" label="黑名单类型">
                    <template slot-scope="scope">
                        {{ scope.row.black_type == 0?'永久': scope.row.black_type == 1?'小时':'天' }}
                    </template>
                </el-table-column> -->
                <!-- <el-table-column prop="black_num" label="黑名单数量" /> -->
                <!-- <el-table-column prop="black_time" label="黑名单时间" /> -->
                <!-- <el-table-column prop="black_append_rule" label="黑名单规则" /> -->

                <!-- <el-table-column prop="created_at" label="创建时间" width="160" /> -->
                <!-- <el-table-column prop="updated_at" label="更新时间" width="160" /> -->
                <el-table-column label="操作" width="200" fixed="right">
                    <template slot-scope="scope">
                        <el-button v-if="utils.hasPermission('sysrules.update') || utils.hasPermission ('*.*')" type="primary" size="mini" plain @click="editHandle(scope.row)">修改</el-button>
                        <el-button type="warning" size="mini" plain @click="detailHandle(scope.row)">详情</el-button>
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
        <el-dialog v-if="openEditHadnle" :key="timer" :title="titleText" :visible.sync="openEditHadnle" width="700px">
            <stationManageEdit :data-info="dataInfo" @openEditShow="openEditShow" @formList="initData" />
        </el-dialog>
        <el-dialog v-if="openDetailHadnle" :key="timer" title="详情" :visible.sync="openDetailHadnle" width="65%">
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
                id: '',
                black_type: '',
                type_id: '',
                status: '',
                pageSize: '10',
                pageNum: '1',
                orderByColumn: 'id',
                isAsc: 'desc'
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
        addHandle() {
            // this.$router.push({name: 'systemStrateAdd'})
            this.timer = new Date().getTime()
            this.titleText = '新增'
            this.openEditHadnle = true
        },
        openEditShow(val) {
            this.openEditHadnle = val
        },
        editHandle(row) {
            //
            this.timer = new Date().getTime()
            this.titleText = '编辑'
            this.openEditHadnle = true
            this.dataInfo = row
            // this.$router.push({name: 'systemStrateEdit', params: {id: row.id, row: row}})
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
            // this.$router.push({name: 'systemStrateDetail', params: {id: row.id}})
            this.openDetailHadnle = true
            this.idInfo = row
        },
        syncHandle(row) {
            this.$confirm(`确认将「${row.black_type}」执行同步策略 吗？`, '确认信息').then(() => {

            }).catch(() => {
            })
        },
        deleteHandle(row) {
            this.$confirm('确定删除' + row.black_type + ', 是否继续?', '提示', {
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
            this.$api.get('api/sysRules', {
                params: this.search
            })
                .then(res => {
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
        handlConfig() {
            this.$confirm('确定同步?', '提示', {
                confirmButtonText: '确定',
                cancelButtonText: '取消',
                type: 'warning'
            }).then(() => {
                this.loading = true
                this.$api.post('api/sysRules/syncConfig')
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
