<template>
    <div>
        <el-row>
            <div class="block">
                <el-date-picker
                    v-model="value1"
                    type="date"
                    value-format="yyyy-MM-dd"
                    placeholder="选择日期"
                    @change="getDate"
                />
            </div>
        </el-row>
        <el-row :gutter="20" style="margin: -10px 10px;">
            <el-col :md="6">
                <page-main class="page-contions">
                    <div class="visit-num">
                        <div class="card-col-tit">
                            <span class="span-non">网站访问次数</span>
                        </div>
                        <div class="card-col-text">
                            <strong class="col-h3">{{ visitTotal }}</strong> <span class="col-span">次</span>
                            <!-- <label class="ip_bg">0</label> -->
                        </div>
                    </div>
                </page-main>
            </el-col>
            <el-col :md="6">
                <page-main class="page-contions">
                    <div class="visit-num">
                        <div class="card-col-tit">
                            <span class="span-non">拦截攻击次数</span>
                        </div>
                        <div class="card-col-text">
                            <strong class="col-h3">{{ attackTotal }}</strong> <span class="col-span">次</span>
                            <!-- <label class="ip_bg">0</label> -->
                        </div>
                    </div>
                </page-main>
            </el-col>
            <el-col :md="6">
                <page-main class="page-contions">
                    <div class="visit-num">
                        <div class="card-col-tit">
                            <span class="span-non">网站总入流量 </span>
                        </div>
                        <div class="card-col-text">
                            <strong class="col-h3">{{ visitFlowTotal }}</strong>
                            <!-- <label class="ip_bg">0</label> -->
                        </div>
                    </div>
                </page-main>
            </el-col>
            <el-col :md="6">
                <page-main class="page-contions">
                    <div class="visit-num">
                        <div class="card-col-tit">
                            <span class="span-non">疑似攻击源IP</span>
                        </div>
                        <div class="card-col-text">
                            <strong class="col-h3">{{ attackIpCount }}</strong> <span class="col-span">次</span>
                            <!-- <label class="ip_bg">0</label> -->
                        </div>
                    </div>
                </page-main>
            </el-col>
        </el-row>
        <!-- <el-row>
            <div class="block">
                <el-date-picker
                    v-model="value2"
                    type="date"
                    value-format="yyyy-MM-dd"
                    placeholder="选择日期"
                    @change="getDetList"
                />
            </div>
        </el-row> -->
        <el-row :gutter="20" style="margin: -10px 10px;">
            <el-col :md="24">
                <div class="ivu-col ">
                    <h3>网站攻击趋势</h3>
                    <el-card class="box-card">
                        <div id="main" class="excr-box " />
                    </el-card>
                </div>
            </el-col>
        </el-row>
        <el-row :gutter="20" style="margin: -10px 10px;">
            <el-col :md="12">
                <page-main class="page-contions">
                    <div class="table-index">
                        受攻击域名当日排行
                    </div>
                    <el-divider />
                    <el-table
                        :data="domainRank"
                        height="250"
                        border
                        style="width: 100%;"
                        :header-cell-style="{background:'#e1e4e5',color:'#80878f'}"
                    >
                        <el-table-column
                            prop="name"
                            label="受攻击域名当日排行"
                        />
                        <el-table-column
                            prop="total"
                            label="次数及占比"
                        />
                    </el-table>
                </page-main>
            </el-col>
            <el-col :md="12">
                <page-main class="page-contions">
                    <div class="table-index">
                        攻击类型排行
                    </div>
                    <el-divider />
                    <el-table
                        :data="typeRank"
                        height="250"
                        border
                        style="width: 100%;"
                        :header-cell-style="{background:'#e1e4e5',color:'#80878f'}"
                    >
                        <el-table-column
                            prop="name"
                            label="攻击类型分布"
                        />
                        <el-table-column
                            prop="total"
                            label="次数及占比"
                        />
                    </el-table>
                </page-main>
            </el-col>
        </el-row>
        <el-row :gutter="20" style="margin: -10px 10px;">
            <el-col :md="12">
                <page-main class="page-contions">
                    <div class="table-index">
                        攻击区域分布情况
                    </div>
                    <el-divider />
                    <el-table
                        :data="regionRank"
                        height="250"
                        border
                        style="width: 100%;"
                        :header-cell-style="{background:'#e1e4e5',color:'#80878f'}"
                    >
                        <el-table-column
                            prop="name"
                            label="攻击区域分布情况"
                        />
                        <el-table-column
                            prop="total"
                            label="次数及占比"
                        />
                    </el-table>
                </page-main>
            </el-col>
            <el-col :md="12">
                <page-main class="page-contions">
                    <div class="table-index">
                        攻击源IP排行
                    </div>
                    <el-divider />
                    <el-table
                        :data="ipRank"
                        height="250"
                        border
                        style="width: 100%;"
                        :header-cell-style="{background:'#e1e4e5',color:'#80878f'}"
                    >
                        <el-table-column
                            prop="name"
                            label="攻击源IP"
                        />
                        <el-table-column
                            prop="total"
                            label="次数及占比"
                        />
                    </el-table>
                </page-main>
            </el-col>
        </el-row>
    </div>
</template>
<script>
import moment from 'moment'
const Echarts = require('echarts')

export default {
    data() {
        return {
            tableData: [],
            typeRank: [], // 攻击类型
            domainRank: [], // 受攻击域名排行
            ipRank: [], // 攻击源IP排行
            regionRank: [], // 攻击区域分布情况
            visitFlowTotal: '0',
            attackIpCount: '0',
            attackTotal: '0',
            visitTotal: '0',
            attackArry: [],
            attackNum: [],
            visitNum: [],
            visitArry: [],
            value1: '',
            value2: '',
            dataObj: ''

        }
    },
    mounted() {
        // this.unit.hasPermission()
        this.utils.hasPermission()
        //
        this.dataObj = moment().format('YYYY-MM-DD')
        this.getList()
        this.getInit()

        // this.getEchars()
    },
    methods: {
        getList() {
            //
            // now = moment().format('YYYY-MM-DD');//2020-07-06

            this.$api.post('api/index/visitAttackOverView', {
                date: this.dataObj
            })
                .then(res => {
                    //
                    if (res.data) {
                        this.visitTotal = res.data.visitTotal
                        this.attackTotal = res.data.attackTotal
                        this.attackIpCount = res.data.attackIpCount
                        this.visitFlowTotal = res.data.visitFlowTotal
                        let arry1 = res.data.visit
                        let arry = res.data.attack
                        arry.forEach(item => {
                            this.attackArry.push(item.date),

                            this.attackNum.push(item.num)
                        })
                        arry1.forEach(item => {
                            this.visitArry.push(item.date),

                            this.visitNum.push(item.num)
                        })
                        if (this.attackArry) {
                            this.getEchars()
                        }
                    }

                })

        },
        getInit() {
            this.$api.post('api/index/wafWarnPolicyRank', {
                date: this.dataObj
            })
                .then(res => {
                    //
                    if (res.data) {
                        this.typeRank = res.data.typeRank
                        this.domainRank = res.data.domainRank
                        this.ipRank = res.data.ipRank
                        this.regionRank = res.data.regionRank

                    }

                })
        },
        getDate(val) {

            this.dataObj = val
            this.getList()
            this.getInit()
        },
        getDetList(val) {
            this.dataObj = val
            this.getInit()
        },
        getEchars() {
            var chartDom = document.getElementById('main')
            var myChart = Echarts.init(chartDom)
            var option
            option = {
                title: {
                    text: ''
                },
                tooltip: {
                    trigger: 'axis',
                    axisPointer: {
                        type: 'cross',
                        label: {
                            backgroundColor: '#6a7985'
                        }
                    }
                },
                legend: {
                    data: ['攻击趋势', '访问趋势'],
                    selected: {
                        '访问趋势': false,
                        '攻击趋势': true
                    }
                },
                toolbox: {
                    feature: {
                        saveAsImage: {}
                    }
                },
                grid: {
                    left: '3%',
                    right: '4%',
                    bottom: '3%',
                    containLabel: true
                },
                dataZoom: [ {
                    type: 'inside', // 支持鼠标滚动缩放
                    start: 0, // 默认数据初始缩放范围为10%到90%
                    end: 90
                } ],
                xAxis: [
                    {
                        type: 'category',
                        boundaryGap: false,
                        axisLabel: {
                            // interval: num,
                            show: true,
                            textStyle: {
                                color: '#a9a9a9', // 更改坐标轴文字颜色
                                fontSize: 10 // 更改坐标轴文字大小
                            },
                            rotate: 40,
                            formatter: function(v) {
                                var date = new Date(v)
                                return `${('0' + date.getHours()).slice(-2)}:${('0' + date.getMinutes()).slice(-2)}`
                                // return `${('0' + date.getHours()).slice(-2)}:${('0' + date.getMinutes()).slice(-2)}:${('0' + date.getSeconds()).slice(-2)}`;
                            },
                            showMinLabel: true, // 显示最小值
                            showMaxLabel: true // 显示最大值

                        },
                        data: this.attackArry
                    }
                ],
                yAxis: [
                    {
                        type: 'value'
                    }
                ],
                series: [
                    {
                        name: '攻击趋势',
                        type: 'line',
                        stack: 'Total',

                        symbol: 'circle', // 拐点设置为实心
                        symbolSize: 8, // 拐点大小

                        data: this.attackNum
                    },

                    {
                        name: '访问趋势',
                        type: 'line',
                        symbol: 'circle', // 拐点设置为实心
                        symbolSize: 3, // 拐点大小
                        stack: '总量',
                        animation: true, // false: hover圆点不缩放 .true:hover圆点默认缩放
                        lineStyle: {
                            normal: {
                                color: '#11c2e6', // 折线的颜色
                                width: '1'// 折线粗细
                            }
                        },
                        itemStyle: {
                            normal: {
                                color: '#2e4854', // 拐点颜色
                                borderColor: '#2e4854 ', // 拐点边框颜色
                                borderWidth: 2// 拐点边框大小
                            },
                            emphasis: {
                                color: '#2e4854 '// hover拐点颜色定义
                            }
                        },
                        areaStyle: {normal: {
                            color: '#fff'// 折线下面区块的颜色
                        }},
                        data: this.visitNum
                    }
                ]
            }
            option && myChart.setOption(option)
        }
    }
}
</script>
<style less>
.page-contions {
    margin: 20px 0 !important;
    box-shadow: 0 1px 2px 0 rgb(0 0 0 / 9%);
    border-radius: 2px;
    margin-right: 20px;
    padding: 10px !important;
}
.card-col-tit {
    border-bottom: 1px solid #e8ecf4;
    font-size: 14px;
    font-weight: 400;
    font-stretch: normal;
    background: url('../../assets/images/tit1.png') no-repeat 0;

    /* line-height: 35px; */
    color: #666;
}
.span-non {
    margin-left: 25px;
    font-size: 14px;
    font-family: PingFang-SC-Regular;
    color: #666;
    line-height: 30px;
    display: inline-block;
}
.card-col-text {
    padding-left: 10px;
    margin: 3px 0 6px;
}
.col-h3 {
    color: #333;
    font-size: 26px;
}
.col-span {
    font-size: 12px;
    color: #666;
}
.ip_bg {
    opacity: 0.3;
    min-height: 28px;
    min-width: 100px;
    display: inline-block;
    background-image: url('../../assets/images/ipv6.png');
    background-repeat: no-repeat;
    background-size: 100% 23px;
    color: #4c9afb !important;
    font-size: 10px;
    padding-left: 25px;
    text-align: center;
    line-height: 28px;
    margin-left: 25px;
}
.excr-box {
    height: 400px;
}
.table-index {
    /* background-color: #f2f4f5; */
    border-radius: 4px;
    font-family: PingFang-SC-Medium;
    font-size: 18px;

    /* padding: 13px 20px; */
    color: #333;
    font-weight: bolder;
}
.block {
    display: flex;
    justify-content: flex-end;
    width: 99%;
    margin-top: 10px;
}
</style>
<style>
.el-divider--horizontal {
    margin: 15px 0;
}
</style>
