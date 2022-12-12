<template>
    <div>
        <page-main>
            <!-- <el-button type="primary" icon="el-icon-plus" @click="addHandle">新增</el-button> -->
            <search-bar>
                <el-form ref="queryForm" :model="search" :inline="true" size="small" label-width="100px">
                    <el-form-item label="类型">
                        <el-select v-model="search.type_id" style="width: 100%;"
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
                    <el-form-item label="被攻击站点">
                        <el-input v-model="search.Hostname" placeholder="请输入被攻击站点" clearable @keydown.enter.native="searchHandle" @clear="searchHandle" />
                    </el-form-item>
                    <el-form-item label="攻击ip">
                        <el-input v-model="search.attack_ip" placeholder="请输入攻击ip" clearable @keydown.enter.native="searchHandle" @clear="searchHandle" />
                    </el-form-item>
                    <el-form-item label="状态码">
                        <el-input v-model="search.status" placeholder="请输入状态码" clearable @keydown.enter.native="searchHandle" @clear="searchHandle" />
                    </el-form-item>
                    <el-form-item label="请求方法">
                        <el-input v-model="search.method" placeholder="请输入请求方法" clearable @keydown.enter.native="searchHandle" @clear="searchHandle" />
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
            <el-table ref="table" class="list-table" :data="dataList" :loading="loading" border stripe highlight-current-row>
                <el-table-column type="index" width="50" />
                <el-table-column prop="attack_ip" label="攻击源IP" width="150" />
                <el-table-column prop="Hostname" label="被攻击站点" />
                <el-table-column prop="Url" label="攻击url" />
                <el-table-column prop="type_name" label="攻击类型" />
                <el-table-column prop="method" label="请求方法" width="80" />
                <el-table-column prop="status" label="响应码" width="80" />
                <el-table-column prop="Time" label="攻击时间" width="160" />
                <el-table-column label="操作" width="80" align="center">
                    <template slot-scope="scope">
                        <el-button type="warning" size="mini" plain @click="detailHandle(scope.row)">详情</el-button>
                        <!-- <el-button type="primary" size="mini" plain @click="editHandle(scope.row)">编辑</el-button> -->
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
        <el-dialog v-if="openEditHadnle" :key="timer" :title="titleText" :visible.sync="openEditHadnle" width="81%">
            <stationManageEdit :data-info="dataInfo" @openEditShow="openEditShow" />
            <!-- <stationManageEdit :data-info="dataInfo" @openEditShow="openEditShow" @formList="initData" /> -->
        </el-dialog>
    </div>
</template>
<script>
import stationManageEdit from './form'
export default {
    components: {
        stationManageEdit
    },
    props: {},
    data() {
        return {
            search: {

                type_id: '',
                status: '',
                pageSize: '10',
                pageNum: '1',
                orderByColumn: 'id',
                isAsc: 'desc',
                Hostname: '',
                beginTime: '',
                endTime: '',
                attack_ip: '',

                method: ''
            },
            dataList: [],
            type_idname: '',
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
            dateRange: []
        }
    },
    mounted() {
        this.initData()
        setTimeout(() => {
            this.getRoleList()
        }, 500)

    },
    methods: {
        getRoleList() {

            this.$api.post('api/ruleType/searchList')
                .then(res => {
                    //
                    if (res.data) {
                        this.roleList  = res.data

                    }
                })
        },
        searchHandle() {
            //
            if (this.dateRange) {
                this.search.beginTime = this.dateRange[0]
                this.search.endTime = this.dateRange[1]
            }
            this.initData()
        },
        detailHandle(row) {

            this.timer = new Date().getTime()
            this.openEditHadnle = true
            this.dataInfo  = row
        },
        addHandle() {
            // this.$router.push({name: 'configModuleAdd'})
            this.timer = new Date().getTime()
            this.titleText = '新增'
            this.openEditHadnle = true
        },
        editHandle(row) {
            //
            this.timer = new Date().getTime()
            this.titleText = '编辑'
            this.openEditHadnle = true
            this.dataInfo  = row
            // this.$router.push({name: 'configModuleEdit', params: {id: row.id, row: row}})
        },
        openEditShow(val) {
            this.openEditHadnle = val
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
            this.$api.get('api/protectLog', {
                params: this.search
            })
                .then(res => {
                    //
                    if (res.data) {
                        this.dataList = res.data.list
                        this.page.total = Number(res.data.count)

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
