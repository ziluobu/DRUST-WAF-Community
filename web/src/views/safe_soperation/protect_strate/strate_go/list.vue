<template>
    <div>
        <page-main>
            <el-button v-if="utils.hasPermission('whiterules.store') || utils.hasPermission ('*.*')"
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

                    <el-form-item>
                        <el-button type="primary" icon="el-icon-search" @click="searchHandle">筛 选</el-button>
                        <el-button icon="el-icon-refresh" size="mini" @click="resetQuery('queryForm')">重置</el-button>
                    </el-form-item>
                </el-form>
            </search-bar>
            <el-row :gutter="10" class="mb8">
                <el-button
                    v-if="utils.hasPermission('whiterules.syncconfig') || utils.hasPermission ('*.*')"
                    type="primary"
                    plain
                    size="mini"
                    @click="handlConfig"
                >
                    同步策略
                </el-button>

                <el-button v-if="utils.hasPermission('whiterules.destroy') || utils.hasPermission ('*.*')" size="small" plain icon="el-icon-delete"
                           type="danger" :disabled="multiple"
                           @click="handleDelete"
                >
                    删除
                </el-button>
            </el-row>
            <el-table ref="table" class="list-table" :data="dataList" :loading="loading" border stripe
                      highlight-current-row @selection-change="handleSelectionChange"
            >
                <el-table-column type="selection" width="55" align="center" />
                <el-table-column prop="web_id" label="域名">
                    <!-- <template slot-scope="scope">
                        <el-popover trigger="hover" placement="top">
                            <p>域名: {{ scope.row.web_id }}</p>
                            <p>规则内容: {{ scope.row.rule_content }}</p>
                            <div slot="reference" class="name-wrapper">
                                <el-tag size="medium">{{ scope.row.web_id }}</el-tag>
                            </div>
                        </el-popover>
                    </template> -->
                </el-table-column>
                <el-table-column prop="admin_id" label="用户" />
                <el-table-column prop="remove_sysrule_id" label="系统规则" />
                <el-table-column prop="describe" label="描述" />
                <!-- <el-table-column prop="request_uri" label="响应路径" /> -->
                <!-- <el-table-column prop="request_method" label="响应方法" /> -->
                <!-- <el-table-column prop="status" label="状态">
                    <template slot-scope="scope">
                        {{ scope.row.status == 0?'禁用':'开启' }}
                    </template>
                </el-table-column> -->

                <el-table-column prop="created_at" label="创建时间" width="160" />
                <!-- <el-table-column prop="updated_at" label="更新时间" width="160" /> -->

                <el-table-column label="操作" width="200" fixed="right">
                    <template slot-scope="scope">
                        <el-button v-if="utils.hasPermission('whiterules.update') || utils.hasPermission ('*.*')" type="primary" size="mini" plain @click="editHandle(scope.row)">编辑</el-button>
                        <el-button v-if="utils.hasPermission('whiterules.show') || utils.hasPermission ('*.*')" type="warning" size="mini" plain @click="detailHandle(scope.row)">详情</el-button>
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
        <el-dialog v-if="openDetailHadnle" :key="timer" title="详情" :visible.sync="openDetailHadnle" width="60%">
            <stationManageDetail :id-info="idInfo" />
        </el-dialog>
        <el-dialog v-if="openEditHadnle" :key="timer" :title="titleText" :visible.sync="openEditHadnle" width="60%"
                   :close-on-click-modal="false" append-to-body destroy-on-close
        >
            <stationManageEdit :data-info="dataInfo" @openEditShow="openEditShow" @formList="initData" />
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
                web_id: ''
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
            webList: [],
            multiple: true,
            ids: []
        }
    },
    mounted() {
        this.getRoleList(),
        this.initData()

    },
    methods: {
        searchHandle() {
            //
        },
        getRoleList() {
            this.$api.post('api/web/searchList')
                .then(res => {
                    //
                    if (res.data) {
                        this.webList = res.data
                    }
                })

        },
        addHandle() {
            // this.$router.push({name: 'strateGoAdd'})
            this.timer = new Date().getTime()
            this.titleText = '新增'
            this.openEditHadnle = true
            this.dataInfo = ''
        },
        editHandle(row) {
            //
            // this.$router.push({name: 'strateGoEdit', params: {id: row.id, row: row}})
            this.timer = new Date().getTime()
            this.titleText = '编辑'
            this.openEditHadnle = true
            this.dataInfo = row
        },
        openEditShow(val) {
            this.openEditHadnle = val
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
            // this.$router.push({name: 'strateGoDetail', params: {id: row.id}})
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
                this.$api.delete(`api/whiteRules/${infoIds}`)
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
        handlConfig() {
            this.$confirm('确定同步?', '提示', {
                confirmButtonText: '确定',
                cancelButtonText: '取消',
                type: 'warning'
            }).then(() => {
                this.loading = true
                this.$api.post('api/whiteRules/syncConfig')
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
        initData() {
            this.$api.get('api/whiteRules', {
                params: this.search
            })
                .then(res => {
                    this.dataList = res.data.list
                    let arry = res.data.list
                    arry.map(item => {

                        this.webList.map(items => {
                            if (item.web_id === items.id) {
                                item.web_id = items.name
                            }
                        })
                    })
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
            this.dateRange = []
            this.search = {}
            this.$refs[formName].resetFields()
            // this.$refs.tables.sort(this.defaultSort.prop, this.defaultSort.order)
            this.initData()
        }
    }
}
</script>
