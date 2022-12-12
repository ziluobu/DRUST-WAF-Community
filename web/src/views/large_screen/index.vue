<template>
    <!-- @click="buttoncli" -->
    <div v-cloak id="skynet" class="main-box">
        <el-tooltip
            effect="dark"
            :content="isFullscreen ? `取消全屏` : `全屏`"
            placement="bottom-end"
        >
            <i
                class="iconfont"
                :class="isFullscreen ? 'icon-quxiaoquanping2' : 'icon-quanping2'"
            />
        </el-tooltip>
        <div class="top">
            <div>
                <img id="icon" style="display: none;">
                <p id="brandName">外网攻击态势</p>
            </div>
        </div>
        <div class="center">
            <span>
                <img src="../../assets/images/element.png">
            </span>
            <span>时间轴</span>
            <!-- <span :class="[timeSelected==1 ? 'active' : '', 'time']" @click="changeTime(1)">近1小时</span>
            <span :class="[timeSelected==12 ? 'active' : '', 'time']" @click="changeTime(12)">近12小时</span>
            <span :class="[timeSelected==24 ? 'active' : '', 'time']" @click="changeTime(24)">近24小时</span> -->
            <span :class="[timeSelected==0 ? 'active' : '', 'time']" @click="changeTime(0)">近24小时</span>
            
            <span :class="[timeSelected==1 ? 'active' : '', 'time']" @click="changeTime(1)">近12小时</span>
            <span :class="[timeSelected==2 ? 'active' : '', 'time']" @click="changeTime(2)">近1小时</span>
        </div>
        <div class="bottom">
            <div class="bottom-left">
                <div class="left-col-1">
                    <div class=" attack_ip_bg">
                        <!-- <span class="title-content" v-text="titleText">国内攻击源IP排名Top5</span> -->
                        <span class="glyphicon glyphicon-sort" style="transform: rotate(90deg); cursor: pointer;" @click="changeRank()" />
                    </div>
                    <div class="content content1">
                        <dv-scroll-board :config="config" style="width: 100%; height: 100%;" />
                    </div>
                </div>

                <div class="left-col-2">
                    <div class="title-box attack_asset_bg">
                        <span class="title-content">被攻击蜜罐类型排名Top5</span>
                    </div>
                    <div class="content">
                        <contentCol v-if="flagBox3" :data-rank="dataRank" />
                        <!-- <no-data-item v-show="attactDestAssetNoData" /> -->
                    </div>
                </div>
            </div>

            <div class="bottom-center">
                <BottomCenterTop v-if="flag" ref="bottomList" :data-info="dataInfo" :data-attack="dataAttack" />
                <div class="center-bottom">
                    <div class="title-box attack_list">
                        <span class="title-content">网站攻击趋势</span>
                    </div>
                    <div class="content">
                        <div style="height: 100%;">
                            <div id="quBox" style="height: 100%;" />
                        </div>
                    </div>
                </div>
            </div>
            <div class="bottom-right">
                <div class="right-col-1">
                    <div class="title-box leak_bg">
                        <span class="title-content">被攻击域名Top5</span>
                    </div>
                    <!-- <div class="content"> -->
                    <rightcenterBottom v-if="flagBox" :data-rank="dataRank" />
                    <!-- <dv-scroll-ranking-board :config="config1" style="width: 100%; height: 100%;" /> -->
                    <!-- <div v-show="!attackTypeNoData" ref="attackType" style="width: 100%; height: 100%;" /> -->
                    <!-- <no-data-item v-show="attackTypeNoData" /> -->
                    <!-- </div> -->
                <!-- <div class="content leak_attack_type">
                    <div v-for="(item,index) in leakAttackTypeInfo" v-show="leakAttackTypeInfo.length>0" class="leak-attack-content" @click="leakAttackTypeDrillDown(item.key)">
                        <div v-text="index+1" class="leak-num-bg"></div>
                        <div v-text="item.key" class="leak-text-left" :title="item.key"></div>
                        <div class="leak-text-right">
                            <div class="shadow-text" v-text="toThousands(88888888888)"></div>
                            <div v-text="toThousands(item.count)" class="real-text"></div>
                        </div>
                    </div>
                    <no-data-item v-show="leakAttackTypeInfo.length<1"></no-data-item>
                </div> -->
                </div>
                <div class="right-col-2">
                    <div class="title-box attack_type_bg">
                        <span class="title-content">攻击源IPTop5</span>
                    </div>
                    <!-- <div class="content"> -->
                    <rightBottomList1 v-if="flagBox1" :data-rank="dataRank" />
                    <!-- <dv-scroll-ranking-board :config="config1" style="width: 100%; height: 100%;" /> -->
                    <!-- <div v-show="!attackTypeNoData" ref="attackType" style="width: 100%; height: 100%;" /> -->
                    <!-- <no-data-item v-show="attackTypeNoData" /> -->
                    <!-- </div> -->
                </div>
                <div class="right-col-3">
                    <div class="title-box stony_creep_type_bg">
                        <span class="title-content">攻击类型分布Top5</span>
                    </div>
                    <div class="content " style="height: calc(100% - 0.5rem);">
                        <BottomRightF v-if="flagBox2" :data-rank="dataRank" />
                    </div>
                </div>
                <div class="right-col-4">
                    <div class="title-box stony_creep_type_bg">
                        <span class="title-content">攻击区域Top5</span>
                    </div>
                    <div class="content" style="height: calc(100% - 0.5rem);">
                        <BottomRightList v-if="flagBox4" :data-rank="dataRank" />
                    </div>
                </div>
            </div>
        </div>
    </div>
<!-- <template id="noDataItem">
    <div style="width: 100%;height: 100%;position: absolute;top: 0;left:0;">
        <svg style="fill:#eeeeee;height:3.75rem;width: 100%;position: absolute;top: calc(50% - 2.625rem);">
            <use xlink:href="../../static/images/commonImg/noData.svg#noData"></use>
        </svg>
        <div style="font-size: 0.75rem;color: #eeeeee;position: absolute;width: 100%;top: calc(50% + 1.875rem);text-align: center;">暂无数据，正在为您持续监测</div>
    </div> -->
</template>
<script>
// const Echarts = require('echarts')
const Echarts = require('echarts')
import screenfull from 'screenfull'
import moment from 'moment'
import BottomCenterTop from './BottomCenter1'
import contentCol from './contentColList'
import BottomRightList from './BottomRightList'
import BottomRightF from './BottomRightF'
import rightBottomList1 from './rightBottomList'
import rightcenterBottom from './rightcenterBottom'
export default {
    components: {
        BottomCenterTop,
        contentCol,
        BottomRightList,
        BottomRightF,
        rightBottomList1,
        rightcenterBottom
    },
    data() {
        return {
            timeSelected: 0,
            attackList: [],
            attackSrcNoData: false,
            attackDestDistributedNoData: false,
            titleText: '国内攻击源IP排名Top5',
            config: {
                // rowNum: 10,
                // waitTime: 3000,
                // align: ['center'],
                // header: ['时间', '域名', '攻击ip']
                // data: [
                //     ['2021-01-01', '2.2.3.2', '中国联通'],
                //     ['2021-02-01', '2.2.3.2', '中国联通'],
                //     ['2021-03-01', '2.2.3.2', '中国联通'],
                //     ['2021-04-01', '2.2.3.2', '中国联通'],
                //     ['2021-05-01', '2.2.3.2', '中国联通'],
                //     ['2021-06-01', '2.2.3.2', '中国联通'],
                //     ['2021-07-01', '2.2.3.2', '中国联通'],
                //     ['2021-08-01', '2.2.3.2', '中国联通'],
                //     ['2021-09-01', '2.2.3.2', '中国联通'],
                //     ['2021-10-01', '2.2.3.2', '中国联通'],
                //     ['2021-11-01', '2.2.3.2', '中国联通'],
                //     ['2021-12-01', '2.2.3.2', '中国联通'],
                //     ['2022-01-01', '2.2.3.2', '中国联通'],
                //     ['2022-02-01', '2.2.3.2', '中国联通'],
                //     ['2020-01-01', '2.2.3.2', '中国联通'],
                //     ['2020-02-01', '2.2.3.2', '中国联通'],
                //     ['2020-03-01', '2.2.3.2', '中国联通'],
                //     ['2020-04-01', '2.2.3.2', '中国联通'],
                //     ['2020-05-01', '2.2.3.2', '中国联通'],
                //     ['2020-06-01', '2.2.3.2', '中国联通']
                // ]
            },
            attackArry: [],
            attackNum: [],
            visitNum: [],
            visitArry: [],
            dataInfo: {},
            dataAttack: [],
            dataRank: [],
            dataConfig: [],
            isFullscreen: false,
            flag: false,
            flagBox: false,
            flagBox1: false,
            flagBox2: false,
            flagBox3: false,
            flagBox4: false,
            domainRank: [],
            timer: null
        }
    },
    mounted() {
        this.init()
        this.dataObj = moment().format('YYYY-MM-DD')
        this.getList()
        this.getInfo()
        this.timer = setInterval(() => {
            this.init()
            this.dataObj = moment().format('YYYY-MM-DD')
            this.getList()
            this.getInfo()

        }, 10000)
      
    },
    // 清除定时器，不然页面会卡死
    beforeDestroy() {
        this.$once('hook:beforeDestroy', () => {
            clearInterval(this.timer)
        })
    },
    methods: {
        buttoncli() {
            if (!screenfull.isEnabled) {
                // 如果不允许进入全屏，发出不允许提示
                this.$message({
                    message: '不支持全屏',
                    type: 'warning'
                })
                return false
            }
            screenfull.toggle()
        },
        change() {      
            this.isFullscreen = screenfull.isFullscreen
        },
        init() {
            if (screenfull.enabled) {
                // screenfull.toggle() // 默认全屏显示
                // screenfull.on('change', this.change)
                screenfull.on('change', this.change)
            }
        },
      
        changeTime(index) {
            this.timeSelected = index
            this.getList()
            this.getInfo()
        },
        changeRank() {

        },
        srcIpDrillDown() {
            
        },
        getInfo() {
            this.attackArry = []
            this.visitArry = []
            this.attackNum = [] 
            this.visitNum = []
            this.$api.post('api/screen/visitAttackOverView', {
                date: this.timeSelected
            })
                .then(res => {
                    if (res.data) {
                        this.flag = true
                        this.dataInfo = res.data
                       
                        let arry1 = res.data.visit
                        let arry = res.data.attack
                  
                        arry.forEach(item => {
                            this.attackArry.push(item.date)
                            
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
            this.$api.post('api/screen/wafWarnPolicyRank', {
                date: this.timeSelected
            })
                .then(res => {
              
                    if (res.data) {
                       
                        this.dataRank = res.data
                        this.flag = true
                        this.flagBox = true
                        this.flagBox1 = true
                        this.flagBox2 = true
                        this.flagBox3 = true
                        this.flagBox4 = true
                    }
                })
       
        },
        getList() {
            this.$api.post('api/screen/realTimeAttackLog', {
                date: this.timeSelected
            })
                .then(res => {
                    if (res.data) {
                        // this.dataAttack = res.data
                        // this.$refs.bottomList.init(res.data)
                      
                        let arryBox = res.data
                        var data = []
                        arryBox.map(val => {
                            var item = []
                            item.push(val.occur_time, val.hostName,  val.attackIp)
                            data.push(item)
                        })
                        this.config = {
                            rowNum: 9,
                            waitTime: 1000,
                            align: ['center'],
                            header: ['时间', '域名', '攻击ip'],
                            data: data
                        }
                        this.dataAttack = res.data
                        
                    }
                })

        },
        getEchars() {
            var chartDom = document.getElementById('quBox')
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
                        '访问趋势': true,
                        '攻击趋势': false
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
                            },
                            showMinLabel: true, // 显示最小值
                            showMaxLabel: true // 显示最大值

                        },
                        data: this.attackArry
                  
                    }
                ],
                yAxis: [
                    {
                        type: 'value',
                        splitLine: {
                            show: false
                        },
                        axisLabel: {
                            show: true,
                            textStyle: {
                                color: '#fff'   // 这里用参数代替了
                            }
                        }
                    }
                ],
                series: [
                    {
                        name: '攻击趋势',
                        type: 'line',
                        stack: 'Total',
                      
                        symbol: 'circle', // 拐点设置为实心
                        symbolSize: 2, // 拐点大小
                     
                        data: this.attackNum
                    },
      
                    {
                        name: '访问趋势',
                        type: 'line',
                        symbol: 'circle', // 拐点设置为实心
                        symbolSize: 8, // 拐点大小
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
                            color: '#0F3776'// 折线下面区块的颜色
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
<style >
@import "../../assets/styles/screen.css";
.dv-scroll-board .header {
    background: #15285c !important;
    font-size: 14px !important;
}
.dv-scroll-board .rows .row-item {
    background: transparent !important;
    font-size: 12px !important;
}
.dv-scroll-ranking-board .ranking-column .inside-column {
    background-color: #2c869f !important;

    /* margin-top: 0 !important; */
}
.dv-scroll-ranking-board .ranking-column {
    /* border-bottom: 0 !important; */
}
.dv-scroll-ranking-board .ranking-info .ranking-value {
    /* font-size: 14px !important; */
    color: #2c869f !important;
    font-weight: bolder;
}
.dv-scroll-ranking-board .row-item {
    /* align-items: center; */
}
</style>