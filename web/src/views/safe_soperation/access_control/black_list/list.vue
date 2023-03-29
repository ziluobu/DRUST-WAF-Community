<template>
    <div>
        <page-main>
            <el-button v-if="utils.hasPermission('ipblack.store') || utils.hasPermission ('*.*')" type="primary" icon="el-icon-plus" size="small" @click="addHandle"> 新增</el-button>
            <search-bar>
                <el-form ref="queryForm" :model="search" size="small" :inline="true" label-width="100px">
                    <el-form-item label="IP地址">
                        <el-input v-model="search.ip" placeholder="请输入IP地址" clearable @keydown.enter.native="searchHandle" @clear="searchHandle" />
                    </el-form-item>

                    <el-form-item label="原因">
                        <el-input v-model="search.reason" placeholder="请输入原因" clearable @keydown.enter.native="searchHandle" @clear="searchHandle" />
                    </el-form-item>

                    <el-form-item label="日期">
                        <el-date-picker v-model="search.beginTime" type="daterange" range-separator="至" start-placeholder="开始日期" end-placeholder="结束日期" />
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
                        v-if="utils.hasPermission('ipblack.destroy') || utils.hasPermission ('*.*')"
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
                        v-if="utils.hasPermission('ipblack.import') || utils.hasPermission ('*.*')"
                        type="warning"
                        plain
                        icon="el-icon-upload2"
                        size="mini"
                        @click="handleImport"
                    >
                        导入
                    </el-button>
                </el-col>
            </el-row>
            <el-table ref="table" class="list-table" :data="dataList" :loading="loading" border stripe
                      highlight-current-row @selection-change="handleSelectionChange"
            >
                <el-table-column type="selection" width="55" align="center" />
                <el-table-column prop="ip" label="IP" />
                <el-table-column prop="admin_id" label="管理员" width="180">
                    <template slot-scope="scope">
                        {{ scope.row.admin_id == 0 ? '--':scope.row.admin_id }}
                    </template>
                </el-table-column>
                <el-table-column prop="reason" label="原因" />
                <!--                <el-table-column prop="black_type" label="黑名单类型">-->
                <!--                    <template slot-scope="scope">-->
                <!--                        {{ scope.row.black_type == 0?'永久': scope.row.black_type == 1?'小时':'天' }}-->
                <!--                    </template>-->
                <!--                </el-table-column>-->
                <el-table-column prop="expire_time" label="过期时间">
                    <template slot-scope="scope">
                        <span v-if="scope.row.black_type === '0'">永久</span>
                        <span v-else>{{ scope.row.expire_time }}</span>
                    </template>
                </el-table-column>
                <el-table-column prop="type" label="类型">
                    <template slot-scope="scope">
                        <span v-if="scope.row.type === '0'">管理员添加</span>
                        <span v-if="scope.row.type === '1'">导入</span>
                        <span v-if="scope.row.type === '2'">触发规则</span>
                        <span v-if="scope.row.type === '3'">触发蜜罐</span>
                    </template>
                </el-table-column>
                <el-table-column prop="created_at" label="创建时间" />
                <!--<el-table-column prop="updated_at" label="更新时间" />-->
                <el-table-column label="操作" width="120" align="center">
                    <template slot-scope="scope">
                        <el-button v-if="utils.hasPermission('ipblack.update') || utils.hasPermission ('*.*')" type="primary" size="mini" plain @click="editHandle(scope.row)">编辑</el-button>
                        <!-- <el-button type="warning" size="mini" plain v-if="utils.hasPermission('ipblack.show') || utils.hasPermission ('*.*')"  @click="detailHandle(scope.row)">详情</el-button> -->
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
        </page-main>
        <el-dialog v-if="openDetailHadnle" :key="timer" :id-info="idInfo" title="详情" :visible.sync="openDetailHadnle" width="60%">
            <stationManageDetail @openEditShow="openEditShow" />
        </el-dialog>
        <el-dialog v-if="openEditHadnle" :key="timer" :title="titleText" :visible.sync="openEditHadnle" width="50%" :close-on-click-modal="false" append-to-body destroy-on-close>
            <stationManageEdit :data-info="dataInfo" @openEditShow="openEditShow" @formList="initData" />
        </el-dialog>
        <!-- 用户导入对话框 -->
        <el-dialog :title="upload.title" :visible.sync="upload.open" width="400px" append-to-body>
            <el-upload
                ref="upload"
                :limit="1"
                accept=".xlsx"
                :headers="{token:$store.state.user.token}"
                :action="`${upload.actionUrl}api/ipblack/import`"
                :disabled="upload.isUploading"
                :on-progress="handleFileUploadProgress"
                :on-success="handleFileSuccess"
                :auto-upload="false"
                :data="dataObj"
                drag
            >
                <i class="el-icon-upload" />
                <div class="el-upload__text">将文件拖到此处，或<em>点击上传</em></div>
                <div slot="tip" class="el-upload__tip text-center">
                    <!-- <div slot="tip" class="el-upload__tip">
                        <el-checkbox v-model="upload.updateSupport" /> 是否更新已经存在的用户数据
                    </div> -->
                    <span>仅允许导入xlsx格式文件。</span>
                    <el-link type="primary" :underline="false" style="font-size: 12px; vertical-align: baseline;" :href="`${upload.actionUrl}blackip-template.xlsx`">下载模板</el-link>
                </div>
            </el-upload>
            <div slot="footer" class="dialog-footer">
                <el-button type="primary" @click="submitFileForm">确 定</el-button>
                <el-button @click="upload.open = false">取 消</el-button>
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
                ip: '',
                reason: '',
                beginTime: '',
                pageSize: '10',
                pageNum: '1',
                orderByColumn: 'id',
                isAsc: 'desc',

                endTime: ''
            },
            dataObj: {

                sign: 'sdsdsd',
                timestamp: this.$store.state.user.failure_time
            },
            // 用户导入参数
            upload: {
                // 是否显示弹出层（用户导入）
                open: false,
                // 弹出层标题（用户导入）
                title: '',
                // 是否禁用上传
                isUploading: false,
                // 是否更新已经存在的用户数据
                updateSupport: 0,

                // 上传的地址
                actionUrl: process.env.VUE_APP_API_ROOT
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
            ids: []
        }
    },
    mounted() {
        this.initData()
    },
    methods: {
        searchHandle() {
            //
            if (this.dateRange) {
                this.search.beginTime = this.dateRange[0]
                this.search.endTime = this.dateRange[1]
            }
            this.initData()
        },
        addHandle() {
            // this.$router.push({name: 'blackListAdd'})
            this.timer = new Date().getTime()
            this.titleText = '新增'
            this.openEditHadnle = true
            this.dataInfo = ''
        },
        editHandle(row) {
            //
            // this.$router.push({name: 'blackListEdit', params: {id: row.id, row: row}})
            this.timer = new Date().getTime()
            this.titleText = '编辑'
            this.openEditHadnle = true
            this.dataInfo  = row
        },
        openEditShow(val) {
            //
            this.openEditHadnle = val
            this.openDetailHadnle = val
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
        /** 导入按钮操作 */
        handleImport() {
            this.upload.title = '导入'
            this.upload.open = true
        },
        // 文件上传中处理
        handleFileUploadProgress() {
            //
            this.upload.isUploading = true
        },
        // 文件上传成功处理
        handleFileSuccess(response) {
            //
            this.upload.open = false
            this.upload.isUploading = false
            this.$refs.upload.clearFiles()
            this.$alert('<div style=\'overflow: auto;overflow-x: hidden;max-height: 70vh;padding: 10px 20px 0;\'>' + response.msg + '</div>', '导入结果', { dangerouslyUseHTMLString: true })
            this.initData()
        },
        /** 下载模板操作 */
        importTemplate() {
            // /blackip-template.xlsx

            // this.download('system/user/importTemplate', {
            // }, `user_template_${new Date().getTime()}.xlsx`)
        },
        // 提交上传文件
        submitFileForm() {
            this.$refs.upload.submit()
        },
        detailHandle(row) {
            // this.$router.push({name: 'blackListDetail', params: {id: row.id}})
            this.openDetailHadnle = true
            this.idInfo = row.id
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
            //
            const infoIds = row.id || this.ids
            this.$confirm('是否确认删除相关' + '的数据项？?', '确认信息', {
                confirmButtonText: '确定',
                cancelButtonText: '取消',
                type: 'warning'
            }).then(() => {
                this.$api.delete(`api/ipblack/${infoIds}`)
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
            this.$confirm('确定删除' + row.ip + ', 是否继续?', '提示', {
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
            this.$api.get('api/ipblack', {
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
