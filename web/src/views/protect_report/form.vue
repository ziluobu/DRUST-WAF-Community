<template>
    <div>
        <el-row>
            <el-col :md="24" :lg="22">
                <el-form ref="form" :rules="ruleForm" :model="form" label-width="120px">
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
                            filter-placeholder="请输入城市拼音"
                            :data="leftList"
                            :titles="['选择站点', '确定站点']"
                        />
                    </el-form-item>
                    <el-form-item label="备注">
                        <el-input v-model="form.note" type="textarea" />
                    </el-form-item>
                    <el-form-item>
                        <el-button type="primary" @click="submitForm('form')">确定</el-button>
                        <el-button @click="resetForm('form')">重置</el-button>
                    </el-form-item>
                    <!-- <div class="btn-box sure-box">
                        <el-button type="primary" @click="submitForm('form')">确定</el-button>
                    </div> -->
                </el-form>
            </el-col>
        </el-row>

        <el-row>
            <el-col :md="24" :lg="24">
                <h3 class="ben-class">自定义报告</h3>
                <el-table :data="tableData" border style="width: 100%;">
                    <el-table-column prop="created_at" label="申请日期" />
                    <el-table-column prop="username" label="申请人" />
                    <el-table-column prop="name" label="选择时间段">
                        <template slot-scope="scope">
                            <div>{{ scope.row.begin_time + '至' + scope.row.end_time }}</div>
                        </template>
                    </el-table-column>
                    <el-table-column prop="web_ids" label="站点">
                        <template slot-scope="scope">
                            <el-button plain size="small" @click="handleDetils(scope.row)">查看站点详情</el-button>
                        </template>
                    </el-table-column>
                    <el-table-column prop="note" label="备注" />
                    <el-table-column prop="status" label="任务状态">
                        <template slot-scope="scope">
                            <el-tag :size="scope.row.status == 2?'danger': scope.row.status == 1?'success':'warning'">
                                {{ scope.row.status == 0 ? '生成中' : scope.row.status == 1 ? '已生成' : '生成失败' }}
                            </el-tag>
                        </template>
                    </el-table-column>
                    <el-table-column
                        fixed="right"
                        label="操作"
                        width="100"
                    >
                        <template slot-scope="scope">
                            <el-button type="text" size="small" @click="handleClick(scope.row)">下载</el-button>
                        </template>
                    </el-table-column>
                </el-table>
            </el-col>
            <div class="ben-box">
                <el-button type="info" @click="getBack('form')">返回</el-button>
            </div>
        </el-row>
        <el-dialog v-if="openReportHadnle" :key="timer" title="站点列表" :visible.sync="openReportHadnle" :close-on-click-modal="false" append-to-body destroy-on-close>
            <el-table ref="table" class="list-table" :data="dataWebList" :loading="loading" border stripe>
                <el-table-column
                    type="index"
                    width="50"
                />
                <el-table-column prop="web_name" label="站点域名" />
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
            this.$api.get('api/web', {
                params: this.search
            })
                .then(res => {
                    //
                    this.dataWebList = res.data.list
                    let dataList = res.data.list
                    dataList.map(item => {
                        this.leftList.push({
                            key: item.id,
                            label: item.web_sysname
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
        handleDetils() {
            this.timer = new Date().getTime()
            this.openReportHadnle = !this.openReportHadnle
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
        handleClick() {
            this.$api.get('report/reportZdyDown')
                .then()
        }
    }
}
</script>
<style >
.ben-class {
    background: #f6f9fd;
    color: #444;

    /* padding: 10px 0 10px 18px; */
}
.ben-class::before {
    position: absolute;
    left: 1px;
    top: 30px;
    display: block;
    content: "";
    width: 2px;
    height: 18px;
    background: #4c9afb !important;
}
.ben-box {
    float: right;
    margin-top: 20px;
}
.sure-box {
    justify-content: flex-start;
}

/* justify-content: flex-start; */
</style>
