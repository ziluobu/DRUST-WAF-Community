<template>
    <div class="content content1">
        <div v-for="(data,index) in attackSrcIpInfo" :key="index" class="left-col-1-row">
            <div class="col-1-col-1">
                <img src="../../assets/images/element1.png">
            </div>
            <div class="col-1-col-2" @click="srcIpDrillDown(data.name)">
                <div class="src_info">
                    <span v-text="data.name" />
                    <span v-text="toThousands(data.value)" />
                </div>
                <div ref="domIpEcharts" class="src_chart" />
            </div>
        </div>
    </div>
</template>
<script>
const Echarts = require('echarts')
const screen_width = window.screen.width
export default {
    props: {
        dataRank: {
            type: Object,
            default: () => {}
        }
    },
    data() {
        return {
            attackSrcIpInfo: [

            ],
            attackSrcIpOption: {}
        }
    },
    watch: {
        dataRank(old, ves) {
            //
            this.domainRank = ves.domainRank
            if (this.attackSrcIpInfo.length > 0) {
                this.attackSrcNoData = false
                this.$nextTick(function() {
                    this.drawAttackdomIpEcharts()
                })
            }

        }
    },
    mounted() {
        this.attackSrcIpOption = {
            tooltip: {
                show: false
            },
            legend: {
                show: false
            },
            grid: {
                left: '0',
                right: '0',
                bottom: '30%',
                top: '0'
            },
            xAxis: {
                type: 'value',
                show: false,
                max: 0
            },
            yAxis: {
                show: false,
                type: 'category'
            },
            series: [
                {
                    name: '攻击源IP排名',
                    type: 'bar',
                    stack: '攻击源IP排名',
                    barWidth: '20%',
                    itemStyle: {
                        normal: {
                            color: '#5affff'
                        },
                        emphasis: {
                            color: '#5affff'
                        }
                    },
                    z: 20,
                    data: []
                },
                {
                    name: '最大数值',
                    type: 'bar',
                    stack: '最大数值',
                    barWidth: '20%',
                    itemStyle: {
                        normal: {
                            color: '#eeeeee'
                        },
                        emphasis: {
                            color: '#eeeeee'
                        }
                    },
                    silent: true,
                    barGap: '-100%',
                    data: []
                }
            ]
        }
        //
        if (this.dataRank.domainRank) {
            this.attackSrcIpInfo = this.dataRank.domainRank

            if (this.attackSrcIpInfo.length > 0) {
                this.attackSrcNoData = false
                this.$nextTick(function() {
                    this.drawAttackdomIpEcharts()
                })
            }
        }
    },
    methods: {
        symbolBig() {
            if (screen_width < 2000) {
                return 20
            } else if (2000 < screen_width && screen_width < 3000) {
                return 18
            } else if (3000 < screen_width && screen_width < 4000) {
                return 24
            } else {
                return 30
            }
        },
        symbolBigArr() {
            if (screen_width < 2000) {
                return [20, 5]
            } else if (2000 < screen_width && screen_width < 3000) {
                return [30, 7.5]
            } else if (3000 < screen_width && screen_width < 4000) {
                return [40, 10]
            } else {
                return [50, 12.5]
            }
        },
        bubbleSize() {
            if (screen_width < 2000) {
                return 6
            } else if ((2000 < screen_width) && (screen_width < 3000)) {
                return 8

            } else if ((3000 < screen_width) && (screen_width < 4000)) {
                return 14
            } else {
                return 20
            }
        },
        itemGapSize() {
            if (screen_width < 2000) {
                return 8
            } else if ((2000 < screen_width) && (screen_width < 3000)) {
                return 12

            } else if ((3000 < screen_width) && (screen_width < 4000)) {
                return 16
            } else {
                return 20
            }
        },

        imageSize() {
            if (screen_width < 2000) {
                return 40
            } else if ((2000 < screen_width) && (screen_width < 3000)) {
                return 75

            } else if ((3000 < screen_width) && (screen_width < 4000)) {
                return 100
            } else {
                return 125
            }
        },
        fontSize() {
            if (screen_width < 2000) {
                return 12
            }
            if (2000 < screen_width && screen_width < 3000) {
                return 18
            }
            if (3000 < screen_width && screen_width < 4000) {
                return 24
            }
            if (4000 < screen_width && screen_width < 5000) {
                return 30
            }
            if (5000 < screen_width && screen_width < 6000) {
                return 36
            } else return 12
        },
        subStringCommon(str, len, hasDot) {
            let newLength = 0
            let newStr = ''
            let chineseRegex = '/[^\x00-\xff]/g'
            let singleChar = ''
            let strLength = str.replace(chineseRegex, '**').length
            for (let i = 0; i < strLength; i++) {
                singleChar = str.charAt(i).toString()
                if (singleChar.match(chineseRegex) != null) {
                    newLength += 2
                } else {
                    newLength++
                }
                if (newLength > len) {
                    break
                }
                newStr += singleChar
            }

            if (hasDot && strLength > len) {
                newStr += '...'
            }
            return newStr
        },
        toThousands(num) {
            if (num != undefined) {
                let str_n = num.toString()
                let result = ''
                while (str_n.length > 3) {
                    result = ',' + str_n.slice(-3) + result
                    str_n = str_n.slice(0, str_n.length - 3)
                }
                if (str_n) {
                    return str_n + result
                }
            }
            return num
        },
        howMany(max) {
            let data = {}
            let value = 1
            let unit = '次'
            if (max > 1000000000) {
                value = 1000000000
                unit = '十亿次'
            } else if (max > 1000000) {
                value = 1000000
                unit = '百万次'
            } else if (max > 10000) {
                value = 10000
                unit = '万次'
            } else if (max > 1000) {
                value = 1000
                unit = '千次'
            }
            data['value'] = value
            data['unit'] = unit
            return data
        },
        srcIpDrillDown() {

        },
        drawAttackdomIpEcharts() {
            // this.attackSrcIpInfo = [  ]
            const domIpEchartsDom = this.$refs.domIpEcharts
            let option = this.attackSrcIpOption
            //
            for (let i = 0; i < domIpEchartsDom.length; i++) {
                if (this.attackSrcIpInfo[i].name == 'NULL') {
                    this.attackSrcIpInfo[i].name = '未知'
                }
                this['domIpEcharts' + i] = Echarts.init(domIpEchartsDom[i], null, { renderer: 'svg' })
                option.series[0].data = []
                option.series[1].data = []
                option.series[0].data.push({
                    id: this.attackSrcIpInfo[i].name,
                    value: this.attackSrcIpInfo[i].value
                })
                option.series[1].data.push(this.attackSrcIpInfo[0].value)
                option.xAxis.max = this.attackSrcIpInfo[0].value
                this['domIpEcharts' + i].setOption(option)
            }
        }
    }
}
</script>
