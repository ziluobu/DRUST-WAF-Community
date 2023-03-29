<template>
    <div>
        <!-- <page-main> -->

        <el-form ref="form" :inline="true" :model="form" label-width="130px">
            <el-form-item label="被攻击站点" prop="type">
                <el-input v-model="form.Hostname" readonly />
            </el-form-item>
            <el-form-item label="攻击url" prop="Url">
                <el-input v-model="form.Url" readonly />
            </el-form-item>
            <el-form-item label="请求方法" prop="method">
                <el-input v-model="form.method" readonly />
            </el-form-item>
            <el-form-item label="攻击源IP" prop="attack_ip">
                <el-input v-model="form.attack_ip" readonly />
            </el-form-item>
            <el-form-item label="攻击类型" prop="type_name">
                <el-input v-model="form.type_name" readonly />
            </el-form-item>
            <el-form-item label="规则id" prop="rule_id">
                <el-input v-model="form.rule_id" readonly />
            </el-form-item>
            <el-form-item label="攻击时间" prop="Time">
                <el-input v-model="form.Time" readonly />
            </el-form-item>
            <el-form-item label="状态码" prop="status">
                <el-input v-model="form.status" readonly />
            </el-form-item>
            <div>
                <el-form-item label="描述" prop="msg">
                    <el-input v-model="form.msg" readonly />
                </el-form-item>
            </div>
            <div>
                <el-form-item label="审计日志头" prop="PartA">
                    <el-input type="textarea" class="tarea" v-html="keepTextStyle(form.PartA)" />
                </el-form-item>
            </div>
            <div>
                <el-form-item label="请求头" prop="PartB">
                    <el-input type="textarea" class="tarea"
                              v-html="keepTextStyle(form.PartB)"
                    />
                </el-form-item>
            </div>
            <div>
                <el-form-item label="请求体" prop="PartC">
                    <el-input type="textarea" class="tarea"
                              v-html="keepTextStyle(form.PartC)"
                    />
                </el-form-item>
            </div>
            <div>
                <el-form-item label="审计日志追踪内容" t prop="name">
                    <el-input type="textarea" class="tarea"
                              v-html="keepTextStyle(form.PartH)"
                    />
                </el-form-item>
            </div>
            <el-form-item label="最终响应头" prop="PartF">
                <el-input type="textarea" class="tarea"
                          v-html="keepTextStyle(form.PartF)"
                />
            </el-form-item>
        </el-form>

        <!-- </page-main> -->
        <!-- <fixed-action-bar>
            <el-button type="primary" @click="submitForm('form')">确定</el-button>
            <el-button type="info" @click="resetForm('form')">取消</el-button>
        </fixed-action-bar> -->
        <div class="btn-box">
            <!-- <el-button type="primary" @click="submitForm('form')">确定</el-button> -->
            <el-button type="info" @click="resetForm('form')">取消</el-button>
        </div>
    </div>
</template>
<script>
// import store from '@/store/index'

export default {
    props: {
        dataInfo: {
            type: Object
        }
    },
    data() {
        return {
            id: '',
            form: {
                name: '',
                type: '',
                key: '',
                value: ''
            }

        }
    },
    mounted() {

        // this.$store.commit('settings/setTitle', '编辑页面')
        // //
        // this.$route.meta.title = '编辑页面'
        // this.initData()
        // if (this.$route.name == 'configModuleEdit') {
        //     this.id = this.$route.params.id
        //     this.initData()
        // }
        if (this.dataInfo) {
            this.id = this.dataInfo.id
            this.form = this.dataInfo
            // this.initData()
        }
    },
    methods: {
        initData() {
            // this.$api.get('api/config/*')
            //     .then(res => {
            //         //
            //         this.form = res.data
            //     })
        },
        keepTextStyle(val) {
            //
            //
            //
            return  (val + '').replace(/\n/g, '<br/>')
        },
        submitForm(formName) {
            this.$refs[formName].validate(valid => {
                if (valid) {
                    this.$message.success('新增成功！')
                    //
                    // this.$router.push('/systemManage/configModuleList')
                    this.$emit('formList')
                    this.$emit('openEditShow', false)
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
.tarea {
    /* width: 200px; */
    max-width: 1000px;
    border: 1px solid #dcdfe6;
    padding: 10px;
}
</style>
