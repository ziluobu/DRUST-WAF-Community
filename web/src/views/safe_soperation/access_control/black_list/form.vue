<template>
    <div>
        <!-- <page-main> -->
        <el-row>
            <el-col :md="24">
                <el-form ref="form" :inline="true" :rules="ruleForm" class="addform" :model="form" label-width="120px">
                    <el-form-item label="IP" prop="ip">
                        <el-input v-model="form.ip" />
                    </el-form-item>
                    <el-form-item label="原因" prop="reason">
                        <el-input v-model="form.reason" />
                    </el-form-item>
                    <div class="nox">
                        <el-form-item label="限定时长" prop="expire_time">
                            <el-switch
                                v-model="form.expire_time"
                                active-value="1"
                                inactive-value="0"
                            />
                            <span style="margin-left: 20px; font-weight: bold;">如果关闭限定时长，即为 永久放行</span>

                        <!-- <el-date-picker v-model="form.expire_time" type="date" /> -->
                        </el-form-item>
                        <div v-if="form.expire_time === '1'" class="swich-box">
                            <el-input-number v-model="dayNum" type="number" controls-position="right"

                                             :min="0" :max="365" style="width: 100px;" @input.native="onInput" @change="handleChange"
                            />
                            天
                            <el-input-number v-model="timeNum" type="number" controls-position="right"

                                             :min="0" :max="60" style="width: 100px;" @input.native="onInput" @change="handleChange1"
                            />
                            小时
                            <span>
                                <span slot="label">
                                    <el-tooltip content="失效30天" placement="top">
                                        <i class="el-icon-question" />
                                    </el-tooltip>

                                </span>
                                失效时间:{{ gettime }}
                                失效后将会删除本条新增，释放记录。可在失效前延长限定时间</span>
                        </div>
                    </div>
                </el-form>
            </el-col>
        </el-row>
        <!-- </page-main> -->
        <!-- <fixed-action-bar>
            <el-button type="primary" @click="submitForm('form')">确定</el-button>
            <el-button type="info" @click="resetForm('form')">取消</el-button>
        </fixed-action-bar> -->
        <div class="btn-box">
            <el-button type="primary" @click="submitForm('form')">确定</el-button>
            <el-button type="info" @click="resetForm('form')">取消</el-button>
        </div>
    </div>
</template>
<script>
// import store from '@/store/index'
import moment from 'moment'
export default {
    props: {
        dataInfo: {
            type: Object,
            default: () => {}
        }
    },
    data() {
        return {
            id: '',
            form: {
                ip: '',
                reason: '',
                expire_time: '0'
            },
            ruleForm: {
                ip: [
                    { required: true, message: '请输入IP地址', trigger: 'blur' }
                ],
                reason: [
                    { required: true, message: '请输入原因', trigger: 'blur' }
                ],
                expire_time: [
                    { required: true, message: '请选择过期时间', trigger: 'change' }
                ]
            },
            dayNum: '',
            timeNum: '',
            day: '',
            time: '2',
            gettime: ''
        }
    },
    mounted() {

        // this.$store.commit('settings/setTitle', '编辑页面')
        // //
        // this.$route.meta.title = '编辑页面'
        // this.initData()
        // if (this.$route.name == 'blackListEdit') {
        //     this.id = this.$route.params.id
        //     this.initData()
        // }
        if (this.dataInfo) {
            this.id = this.dataInfo.id
            this.form = this.dataInfo
            this.initData()

        }
        this.getTimeDate()
    },
    methods: {
        getTimeDate() {
            var _this = this
            _this.gettime = moment().format('YYYY-MM-DD HH:mm:ss')
            //

        },
        initData() {
            this.$api.get(`api/ipblack/${this.id}`)
                .then(res => {
                    this.form = res.data
                    if (this.form.expire_time !== '0') {
                        this.form.expire_time = '1'
                    }
                })
        },
        handleChange(value) {
            var _this = this
            var date = moment()
            _this.gettime = date.add(value, 'days').format('YYYY-MM-DD HH:mm:ss')

        },
        handleChange1(value) {
            var _this = this
            var date = moment()
            _this.gettime = date.add(value, 'hours').format('YYYY-MM-DD HH:mm:ss')
        },
        submitForm(formName) {
            if (this.form.expire_time == '1') {
                this.form.expire_time = moment(new Date(this.gettime)).valueOf()
            }
            this.$refs[formName].validate(valid => {
                if (valid) {
                    if (this.id) {
                        this.$api.put(`api/ipblack/${this.id}`, this.form)
                            .then(res => {
                                if (res.code === 2000) {
                                    this.$emit('formList')
                                    this.$emit('openEditShow', false)
                                    this.$message.success('新增成功！')
                                }

                            })
                    } else {
                        this.$api.post('api/ipblack/', this.form)
                            .then(res => {
                                if (res.code === 2000) {
                                    this.$emit('formList')
                                    this.$emit('openEditShow', false)
                                    this.$message.success('新增成功！')
                                }

                            })
                    }
                } else {
                    //
                    return false
                }
            })
        },
        resetForm(formName) {
            this.$refs[formName].resetFields()
            this.$emit('openEditShow', false)
        }
    }
}
</script>
<style>
.swich-box {
    width: 73%;
    margin: auto;
    line-height: 40px;
}
</style>
