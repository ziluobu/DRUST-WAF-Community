<template>
    <div>
        <page-main>
            <el-row>
                <el-col :md="24" :lg="12">
                    <el-form ref="form" :rules="ruleForm" :model="form" label-width="120px">
                        <el-form-item label="活动名称" prop="name">
                            <el-input v-model="form.name" />
                        </el-form-item>
                        <el-form-item label="活动区域" prop="region">
                            <el-select v-model="form.region" placeholder="请选择活动区域">
                                <el-option label="区域一" value="shanghai" />
                                <el-option label="区域二" value="beijing" />
                            </el-select>
                        </el-form-item>
                        <el-form-item label="活动时间" prop="date">
                            <el-col :span="11">
                                <el-date-picker v-model="form.date1" type="date" placeholder="选择日期" style="width: 100%;" />
                            </el-col>
                            <el-col class="line" :span="2">-</el-col>
                            <el-col :span="11">
                                <el-time-picker v-model="form.date2" placeholder="选择时间" style="width: 100%;" />
                            </el-col>
                        </el-form-item>
                        <el-form-item label="即时配送" prop="delivery">
                            <el-switch v-model="form.delivery" />
                        </el-form-item>
                        <el-form-item label="活动性质" prop="type">
                            <el-checkbox-group v-model="form.type">
                                <el-checkbox label="美食/餐厅线上活动" name="type" />
                                <el-checkbox label="地推活动" name="type" />
                                <el-checkbox label="线下主题活动" name="type" />
                                <el-checkbox label="单纯品牌曝光" name="type" />
                            </el-checkbox-group>
                        </el-form-item>
                        <el-form-item label="特殊资源" prop="resource">
                            <el-radio-group v-model="form.resource">
                                <el-radio label="线上品牌商赞助" />
                                <el-radio label="线下场地免费" />
                            </el-radio-group>
                        </el-form-item>
                        <el-form-item label="活动形式" prop="textarea">
                            <el-input v-model="form.desc" type="textarea" />
                        </el-form-item>
                        <el-form-item label="活动名称" prop="name">
                            <el-input v-model="form.name" />
                        </el-form-item>
                        <el-form-item label="活动区域" prop="region">
                            <el-select v-model="form.region" placeholder="请选择活动区域">
                                <el-option label="区域一" value="shanghai" />
                                <el-option label="区域二" value="beijing" />
                            </el-select>
                        </el-form-item>
                        <el-form-item label="活动时间" prop="date">
                            <el-col :span="11">
                                <el-date-picker v-model="form.date1" type="date" placeholder="选择日期" style="width: 100%;" />
                            </el-col>
                            <el-col class="line" :span="2">-</el-col>
                            <el-col :span="11">
                                <el-time-picker v-model="form.date2" placeholder="选择时间" style="width: 100%;" />
                            </el-col>
                        </el-form-item>
                        <el-form-item label="即时配送" prop="delivery">
                            <el-switch v-model="form.delivery" />
                        </el-form-item>
                        <el-form-item label="活动性质" prop="type">
                            <el-checkbox-group v-model="form.type">
                                <el-checkbox label="美食/餐厅线上活动" name="type" />
                                <el-checkbox label="地推活动" name="type" />
                                <el-checkbox label="线下主题活动" name="type" />
                                <el-checkbox label="单纯品牌曝光" name="type" />
                            </el-checkbox-group>
                        </el-form-item>
                        <el-form-item label="特殊资源" prop="resource">
                            <el-radio-group v-model="form.resource">
                                <el-radio label="线上品牌商赞助" />
                                <el-radio label="线下场地免费" />
                            </el-radio-group>
                        </el-form-item>
                        <el-form-item label="活动形式" prop="textarea">
                            <el-input v-model="form.desc" type="textarea" />
                        </el-form-item>
                        <el-form-item label="活动名称" prop="name">
                            <el-input v-model="form.name" />
                        </el-form-item>
                        <el-form-item label="活动区域" prop="region">
                            <el-select v-model="form.region" placeholder="请选择活动区域">
                                <el-option label="区域一" value="shanghai" />
                                <el-option label="区域二" value="beijing" />
                            </el-select>
                        </el-form-item>
                        <el-form-item label="活动时间" prop="date1">
                            <el-col :span="11">
                                <el-date-picker v-model="form.date1" type="date" placeholder="选择日期" style="width: 100%;" />
                            </el-col>
                            <el-col class="line" :span="2">-</el-col>
                            <el-col :span="11">
                                <el-time-picker v-model="form.date2" placeholder="选择时间" style="width: 100%;" />
                            </el-col>
                        </el-form-item>
                        <el-form-item label="即时配送">
                            <el-switch v-model="form.delivery" />
                        </el-form-item>
                        <el-form-item label="活动性质">
                            <el-checkbox-group v-model="form.type">
                                <el-checkbox label="美食/餐厅线上活动" name="type" />
                                <el-checkbox label="地推活动" name="type" />
                                <el-checkbox label="线下主题活动" name="type" />
                                <el-checkbox label="单纯品牌曝光" name="type" />
                            </el-checkbox-group>
                        </el-form-item>
                        <el-form-item label="特殊资源">
                            <el-radio-group v-model="form.resource">
                                <el-radio label="线上品牌商赞助" />
                                <el-radio label="线下场地免费" />
                            </el-radio-group>
                        </el-form-item>
                        <el-form-item label="活动形式">
                            <el-input v-model="form.desc" type="textarea" />
                        </el-form-item>
                    </el-form>
                </el-col>
            </el-row>
        </page-main>
        <fixed-action-bar>
            <el-button type="primary" @click="submitForm('form')">立即创建</el-button>
            <el-button type="info" @click="resetForm('form')">取消</el-button>
        </fixed-action-bar>
    </div>
</template>
<script>
// import store from '@/store/index'

export default {
    data() {
        return {
            id: '',
            form: {
                name: '',
                date1: '',
                date2: '',
                type: '',
                delivery: '',
                resource: '',
                desc: '',
                region: ''
            },
            ruleForm: {
                name: [
                    { required: true, message: '请输入活动名称', trigger: 'blur' }
                    // { min: 3, max: 5, message: '长度在 3 到 5 个字符', trigger: 'blur' }
                ],
                region: [
                    { required: true, message: '请选择活动区域', trigger: 'change' }
                ],
                date1: [
                    { type: 'date', required: true, message: '请选择日期', trigger: 'change' }
                ]
            }
        }
    },
    mounted() {
        this.id = this.$route.params.id
        // this.$store.commit('settings/setTitle', '编辑页面')
        // //
        // this.$route.meta.title = '编辑页面'
        // this.initData()
        //
    },
    methods: {
        initData() {
            this.$api.get('insurance/count/')
                .then()
        },
        submitForm(formName) {
            this.$refs[formName].validate(valid => {
                if (valid) {
                    alert('submit!')
                    //
                    this.$router.push('/demo/list')
                } else {
                    //
                    return false
                }
            })
        },
        resetForm(formName) {
            this.$refs[formName].resetFields()
        }
    }
}
</script>
