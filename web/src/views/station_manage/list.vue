<template>
    <div>
        <page-main>
            <el-button v-if="utils.hasPermission('web.store') || utils.hasPermission ('*.*')"
                       size="small" type="primary" icon="el-icon-plus" @click="addHandle"
            >
                新增
            </el-button>
            <search-bar>
                <el-form ref="queryForm" :model="search" :inline="true" size="small" label-width="100px">
                    <el-form-item label="所属单位">
                        <el-select v-model="search.group_id" style="width: 100%;"
                                   filterable
                                   allow-create
                                   default-first-option
                                   placeholder="请选择单位"
                        >
                            <el-option v-for="item in roleList"
                                       :key="item.id"
                                       :value="item.id"
                                       :label="item.group_name"
                            />
                        </el-select>
                    </el-form-item>

                    <el-form-item label="防护域名">
                        <el-input v-model="search.web_name" placeholder="请输入防护域名" clearable
                                  @keydown.enter.native="searchHandle" @clear="searchHandle"
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
                        v-if="utils.hasPermission('web.destroy') || utils.hasPermission ('*.*')"
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
                    <!-- v-hasPermi="['monitor:logininfor:remove']" -->
                    <el-button
                        v-if="utils.hasPermission('web.syncconfig') || utils.hasPermission ('*.*')"
                        type="primary"
                        plain
                        size="mini"
                        @click="handlConfig"
                    >
                        同步策略
                    </el-button>
                </el-col>
            </el-row>
            <el-table ref="table" v-loading="loading" class="list-table" :data="dataList" border stripe
                      highlight-current-row
                      @selection-change="handleSelectionChange"
            >
                <el-table-column type="selection" width="55" align="center" />
                <el-table-column prop="web_sysname" label="站点名称" />
                <el-table-column prop="web_name" label="站点域名">
                    <template slot-scope="scope">
                        <!-- <el-link type="primary" :href="'https://'+scope.row.web_name" target="_blank"> -->
                        <el-link type="primary" :href="scope.row.link_url" target="_blank">
                            {{ scope.row.web_name }}
                        </el-link>
                        <div>源端口:{{ scope.row.web_port }}</div>
                    </template>
                </el-table-column>
                <!-- <el-table-column prop="web_port" label="源端口" /> -->
                <el-table-column prop="source_ip" label="源站地址" />
                <el-table-column prop="protect_status" label="模式" width="100">
                    <template slot-scope="scope">
                        <el-tag :size="scope.row.protect_status==1?'success':'primary'">{{ scope.row.protect_status==1? '防护模式' : '转发模式' }}</el-tag>
                    </template>
                </el-table-column>
                <!-- <el-table-column prop="proxy_name" label="代理名称" /> -->
                <!-- <el-table-column prop="dst_port" label="目的端口" /> -->
                <el-table-column prop="is_https" label="https" width="80">
                    <template slot-scope="scope">
                        <el-tag :size="scope.row.is_https==1?'success':'info'">{{ scope.row.is_https==1?'是':'否' }}</el-tag>
                    </template>
                </el-table-column>
                <el-table-column prop="is_parse" label="是否解析" width="80">
                    <template slot-scope="scope">
                        <el-tag :size="scope.row.is_parse==1?'success':'danger'">{{ scope.row.is_parse==1?'是':'否' }}</el-tag>
                    </template>
                </el-table-column>
                <!-- <el-table-column prop="proxy_catefile" label="证书文件" /> -->
                <!-- <el-table-column prop="proxy_catekeyfile" label="密钥文件" /> -->
                <!-- <el-table-column prop="proxy_catechainfile" label="证书链文件" /> -->
                <!--  -->
                <!--<el-table-column prop="group_id" label="所属单位" />-->
                <!--                <el-table-column prop="web_active" label="web_active" />-->
                <el-table-column prop="created_at" label="创建时间" width="160" />
                <!-- <el-table-column prop="updated_at" label="更新时间" width="160" />-->
                <el-table-column label="操作" width="200" fixed="right">
                    <template slot-scope="scope">
                        <el-button v-if="utils.hasPermission('web.update') || utils.hasPermission ('*.*')" type="primary" size="mini" plain @click="editHandle(scope.row)">编辑</el-button>
                        <el-button v-if="utils.hasPermission('web.show') || utils.hasPermission ('*.*')" type="warning" size="mini" plain @click="detailHandle(scope.row)">详情</el-button>
                        <!-- <el-dropdown @command="handleMoreOperating($event, scope.row)">
                            <el-button size="mini">
                                更多操作<i class="el-icon-arrow-down el-icon--right" />
                            </el-button>
                            <el-dropdown-menu slot="dropdown">
                                <el-dropdown-item command="detail">详情</el-dropdown-item>
                                <el-dropdown-item command="station">同步站点</el-dropdown-item>
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
                groud_id: '',
                web_name: '',
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
            this.$api.post('api/group/searchList')
                .then(res => {
                    //
                    if (res.data) {
                        this.roleList = res.data
                    }
                })
        },
        searchHandle() {
            this.initData()
        },
        addHandle() {
            // this.$router.push({name: 'stationManageAdd'})
            this.timer = new Date().getTime()
            this.titleText = '新增站点'
            this.openEditHadnle = true
            this.dataInfo = ''
        },
        editHandle(row) {
            //
            this.timer = new Date().getTime()
            this.titleText = '编辑站点'
            this.openEditHadnle = true
            this.dataInfo = row
            // this.$router.push({name: 'stationManageEdit', params: {id: row.id, row: row}})
        },
        openEditShow(val) {
            this.openEditHadnle = val
        },
        handleMoreOperating(command, row) {
            switch (command) {
                case 'detail':
                    this.detailHandle(row)
                    break
                case 'station':
                    this.stationHandle(row)
                    break
                case 'delete':
                    this.deleteHandle(row)
                    break
            }
        },
        detailHandle(row) {
            // this.$router.push({name: 'stationManageDetail', params: {id: row.id}})

            this.openDetailHadnle = true
            this.idInfo = row.id
        },
        stationHandle(row) {
            this.$confirm(`确认同步站点「${row.web_sysname}」吗？`, '确认信息').then(() => {
                this.$api.post('mock/pages_example/manager/password/reset', {
                    id: row.id
                }).then(() => {
                    this.$message.success({
                        message: '模拟重置成功',
                        center: true
                    })
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
        /** 删除按钮操作 */
        handleDelete(row) {
            const infoIds = row.id || this.ids
            this.$confirm('是否确认删除该条' + '的数据项？?', '确认信息', {
                confirmButtonText: '确定',
                cancelButtonText: '取消',
                type: 'warning'
            }).then(() => {
                this.$api.delete(`api/web/${infoIds}`)
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
            this.$confirm('确定删除' + row.web_sysname + ', 是否继续?', '提示', {
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
            this.$api.get('api/web', {
                params: this.search
            })
                .then(res => {
                    //
                    this.dataList = res.data.list
                    let arry = res.data.list
                    arry.map(item => {
                        this.roleList.map(items => {
                            if (item.group_id === items.id) {
                                item.group_id = items.group_name
                            }
                        })
                    })
                    this.page.total = Number(res.data.count)

                })
        },
        handlConfig() {
            this.$confirm('确定同步?', '提示', {
                confirmButtonText: '确定',
                cancelButtonText: '取消',
                type: 'warning'
            }).then(() => {
                this.loading = true
                this.$api.post('api/web/syncConfig')
                    .then(res => {
                        if (res.data) {
                            this.initData()
                            this.$message({
                                type: 'success',
                                message: '操作成功!'
                            })
                            // this.loading = false
                        }
                    })
            })
        },
        /** 重置按钮操作 */
        resetQuery(formName) {
            this.page.currentPage = 1
            // this.search = {}
            this.search.web_name = ''
            this.search.group_id = ''
            this.$refs[formName].resetFields()
            // this.$refs.tables.sort(this.defaultSort.prop, this.defaultSort.order)
            this.initData()
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
