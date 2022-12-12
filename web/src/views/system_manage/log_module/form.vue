<template>
    <div>
        <!-- <page-main> -->
        <el-row>
            <el-col :md="24" :lg="22">
                <el-form ref="form" :model="form" label-width="130px">
                    <el-form-item label="请求参数" prop="username">
                        <div class="items-form">
                            <p v-text="form.oper_param" />
                        </div>
                    </el-form-item>
                    <el-form-item label="返回参数" prop="realname">
                        <div class="items-form">
                            <p v-text="form.json_result" />
                        </div>
                    </el-form-item>
                </el-form>
            </el-col>
        </el-row>
        <!-- </page-main> -->
    </div>
</template>
<script>
// import store from '@/store/index'

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
                oper_param: {

                },
                json_result: {

                }
            },
            roleList: []
        }
    },
    mounted() {
        //
        if (this.dataInfo) {
            this.form = this.dataInfo
        }

        this.initData()
    },
    methods: {
        initData() {

        }

    }
}
</script>
<style scoped>
.items-form {
    padding: 5px 15px;
    width: 100%;
    height: auto;
    border: 1px solid #dcdfe6;
    font-size: inherit;
    color: #606266;
    background-color: #fff;
    border-radius: 4px;
}
</style>
