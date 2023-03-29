<template>
    <div class="box-contr">
        <h2 class="ben-class">报告选择</h2>
        <el-row class="col-box">
            <el-col :md="24" :lg="24">
                <el-form ref="form" :rules="ruleForm" :model="form" label-width="120px" class="form-box">
                    <el-form-item label="日志时间范围" prop="beginTime">
                        <el-date-picker
                            v-model="dataValue"
                            type="datetimerange"
                            :clearable="false"
                            start-placeholder="请选择起止报告时间"
                            end-placeholder="跨度不可超过一个月"
                            :picker-options="pickerOptions0"
                            value-format="yyyy-MM-dd"
                            @change="dateChange"
                        />
                    </el-form-item>
                    <el-form-item label="站点" prop="web_ids">
                        <el-transfer
                            v-model="rightList"
                            filterable
                            :filter-method="filterMethod"
                            filter-placeholder="请输入域名"
                            :data="leftList"
                            :titles="['选择站点', '确定站点']"
                        />
                    </el-form-item>
                    <el-form-item label="备注">
                        <el-input v-model="form.note" type="textarea" :rows="4" />
                    </el-form-item>
                    <el-form-item v-if="utils.hasPermission('report.addreportzdytask') || utils.hasPermission ('*.*')">
                        <el-button type="primary" @click="submitForm('form')">确定</el-button>
                        <el-button @click="resetForm('form')">重置</el-button>
                    </el-form-item>
                    <!-- <div class="btn-box sure-box">
                        <el-button type="primary" @click="submitForm('form')">确定</el-button>
                    </div> -->
                </el-form>
            </el-col>
        </el-row>
        <h2 class="ben-class card-title">自定义报告</h2>
        <el-row class="col-box">
            <el-col :md="24" :lg="24">
                <el-table :data="tableData" border class="brn-table">
                    <el-table-column prop="created_at" label="申请日期" width="200" />
                    <el-table-column prop="username" label="申请人" width="150" />
                    <el-table-column prop="name" label="选择时间段" width="400">
                        <template slot-scope="scope">
                            <div>{{ scope.row.begin_time + '至' + scope.row.end_time }}</div>
                        </template>
                    </el-table-column>
                    <el-table-column prop="web_ids" label="站点" width="150">
                        <template slot-scope="scope">
                            <el-button plain size="small" @click="handleDetils(scope.row.web_ids)">查看站点详情</el-button>
                        </template>
                    </el-table-column>
                    <el-table-column prop="note" label="备注" />
                    <el-table-column prop="status" label="任务状态" width="100">
                        <template slot-scope="scope">
                            <el-tag :size="scope.row.status == 2?'danger': scope.row.status == 1?'success':'warning'">
                                {{ scope.row.status == 0 ? '生成中' : scope.row.status == 1 ? '已生成' : '生成失败' }}
                            </el-tag>
                        </template>
                    </el-table-column>
                    <el-table-column
                        fixed="right"
                        label="操作"
                        width="160"
                    >
                        <template v-if="scope.row.status == 1" slot-scope="scope">
                            <!--<el-button type="text" size="small" @click="handleClick(scope.row)">下载</el-button>-->
                            <el-button v-if="utils.hasPermission('report.reportzdydown') || utils.hasPermission ('*.*')" type="primary" size="mini" plain @click="handleClick(scope.row)">下载</el-button>
                            <el-button v-if="utils.hasPermission ('*.*')" type="danger" size="mini" plain @click="deleteHandle(scope.row)">删除</el-button>
                        </template>
                    </el-table-column>
                </el-table>
            </el-col>
            <!-- <div class="ben-box">
                <el-button type="info" @click="getBack('form')">返回</el-button>
            </div> -->
        </el-row>
        <el-dialog v-if="openReportHadnle" :key="timer" title="站点列表" :visible.sync="openReportHadnle" :close-on-click-modal="false" append-to-body destroy-on-close>
            <el-table ref="table" class="list-table" :data="dataWebList" :loading="loading" border stripe>
                <el-table-column
                    type="index"
                    width="50"
                />
                <el-table-column prop="" label="站点域名">
                    <template slot-scope="scope">
                        {{ scope.row }}
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
        </el-dialog>
    </div>
</template>
<script>
// import axios from 'axios'
// import store from '@/store/index'
export default {
    data() {

        return {
            form: {

                endTime: '',
                beginTime: '',
                web_ids: [],
                note: ''

            },
            dataValue: [],
            leftList: [],
            // 已选课程列表（右边，只需要key）
            rightList: [],
            search: {
                groud_id: '',
                web_name: '',
                pageSize: '10',
                pageNum: '1',
                orderByColumn: 'id',
                isAsc: 'desc'

            },
            page: {
                currentPage: 1,
                pageSizes: [10, 50, 100],
                pageSize: 10,
                total: 0
            },
            groupSearch: {

                pageSize: '10',
                pageNum: '1',
                orderByColumn: 'id',
                isAsc: 'desc'
            },
            ruleForm: {
                web_ids: [
                    { required: true, message: '请选择站点', trigger: 'blur' }
                ],
                beginTime: [
                    { required: true, message: '请输入时间', trigger: 'blur' }
                ]
                // describe: [
                //     { required: true, message: '请输入描述', trigger: 'blur' }
                // ],
            },
            pickerOptions0: {
                onPick: ({ maxDate, minDate }) => {
                    this.cuttentTime = minDate.getTime()
                    if (maxDate) {
                        this.cuttentTime = ''
                    }
                },
                disabledDate: time => {
                    if (time.getTime() > Date.now()) {
                        return true
                    }      // 今天之后的时间不可选

                    if (this.cuttentTime != '') {
                        const one = 30 * 24 * 3600 * 1000
                        const minTime = this.cuttentTime - one
                        const maxTime = this.cuttentTime + one
                        return time.getTime() < minTime || time.getTime() > maxTime   // 选择日期范围为一个月
                    }
                }
            },
            tableData: [],
            openReportHadnle: false,
            timer: '',
            loading: false,
            dataWebList: [] // 站点猎豹

        }
    },
    mounted() {
        this.getCourseList()
        this.getReportList()
    },
    methods: {
        dateChange(val) {
            //
            if (val) {
                this.form.beginTime = val[0]
                this.form.endTime = val[1]
            }

        },
        // 搜索关键字
        filterMethod(query, item) {
            return item.label.indexOf(query) > -1
        },
        getCourseList() {
            this.$api.post('api/web/searchList', this.search)
                .then(res => {
                    let dataList = res.data
                    dataList.map(item => {
                        this.leftList.push({
                            key: item.id,
                            label: item.name
                        })
                    })

                })
        },
        getReportList() {
            this.$api.get('api/report/getReportZdyTask', {
                params: this.search
            })
                .then(res => {
                    //
                    if (res.data) {
                        this.tableData = res.data.list

                    }

                })
        },
        getWebList() {

        },
        handleDetils(val) {
            this.timer = new Date().getTime()
            this.openReportHadnle = !this.openReportHadnle
            this.dataWebList = val
        },
        // 两个数据ID转换的方法
        changeRefListToCourseIdList(data, fun) {
            let idList = []
            for (let i in data) {
                idList.push(data[i].courseId)
            }
            fun(idList)
        },
        changeCourseIdListToRefList(data, fun) {
            let refList = []
            for (let i in data) {
                let studentCourseRef = {
                    courseId: data[i]
                }
                refList.push(studentCourseRef)
            }
            fun(refList)
        },
        submitForm(formName) {
            //
            //
            this.form.web_ids = this.rightList
            this.$refs[formName].validate(valid => {
                if (valid) {

                    this.$api.post('api/report/addReportZdyTask', this.form)
                        .then(res => {
                            if (res.code === 2000) {
                                // this.$emit('formList')
                                // this.$emit('openEditShow', false)
                                this.getReportList()
                                this.$message.success('新增成功！')
                            }

                        })

                    // this.$router.push('/systemManage/configModuleList')

                } else {
                    //
                    return false
                }
            })
        },
        resetForm(formName) {
            this.dataValue = []
            this.$refs[formName].resetFields()

        },
        getBack() {
            this.$emit('openEditShow', false)
        },
        handleSizeChange(val) {
            //
            this.search.pageSize = val
            this.getCourseList()
        },
        handleCurrentChange(val) {
            //
            this.search.pageNum = val
            this.getCourseList()
        },
        deleteHandle(row) {
            this.$confirm('是否确认删除相关的数据项？', '提示', {
                confirmButtonText: '确定',
                cancelButtonText: '取消',
                type: 'warning'
            }).then(() => {
                this.$api.delete(`api/report/zdydestroy/${row.id}`)
                    .then(res => {
                        //
                        console.log(res)
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
        initData() {
            this.$api.get('api/report/getReportZdyTask', {
                params: this.search
            })
                .then(res => {
                    //
                    if (res.data) {
                        this.tableData = res.data.list

                    }

                })
        },
        handleClick(row) {
            //
            let elmentF = document.createElement('iframe')
            elmentF.src = process.env.VUE_APP_API_ROOT + `api/report/reportZdyDown/${row.id}`
            elmentF.style.display = 'none'
            document.body.appendChild(elmentF)
            // axios({
            //     method: 'get',
            //     url: process.env.VUE_APP_API_ROOT + `api/report/reportZdyDown/${row.id}`,
            //     headers: {
            //         'token': store.state.user.token
            //     },
            //     responseType: 'blob' // 设置接收数据的类型
            // }).then(function(res) {
            //     //
            //     const fileName = res.headers['content-disposition'].split('=')[1]
            //     const _res = res.data
            //     let blob = new Blob([_res])
            //     let downloadElement = document.createElement('a')
            //     let href = window.URL.createObjectURL(blob) // 创建下载的链接
            //     downloadElement.href = href
            //     downloadElement.download = fileName // 下载后文件名
            //     document.body.appendChild(downloadElement)
            //     downloadElement.click() // 点击下载
            //     document.body.removeChild(downloadElement) // 下载完成移除元素
            //     window.URL.revokeObjectURL(href) // 释放掉blob对象

            //     // //
            // })
            // this.$api.get(`api/report/reportZdyDown/${row.id}`)
            //     .then(res => {
            //         //

            //     })
        }
    }
}
</script>
<style lang="less" scoped>
.box-contr {
    // position: relative;
}
.col-box {
    width: 98%;
    background: #fff;
    margin: auto;
    margin-top: 10px;
}
.form-box {
    margin-top: 20px;
    width: 80%;
}
.ben-class {
    background: #f6f9fd;
    color: #444;
    // padding: 10px 0 10px 18px;
    margin-left: 25px;
}
.ben-class::before {
    position: absolute;
    left: 15px;
    top: 2px;
    display: block;
    content: "";
    width: 3px;
    height: 27px;
    background: #4c9afb !important;
}
.card-title::before {
    top: 15px;
}
.ben-box {
    float: right;
    margin-top: 20px;
}
.sure-box {
    justify-content: flex-start;
}
/deep/.el-transfer-panel {
    width: 350px;
}
/deep/.el-textarea {
    width: 50%;
}
.brn-table {
    width: 98%;
    margin: auto;
    margin-bottom: 10px;
    margin-top: 10px;
}

/* justify-content: flex-start; */
</style>
