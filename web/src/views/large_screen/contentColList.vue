<template>
    <div style="height: 200px;">
        <div ref="attactSsetEcharts" style="width: 100%; height: 100%;" />
    </div>
</template>
<script>
const Echarts = require('echarts')
const screen_width = window.screen.width
const colors2 = ['rgba(90,255,255,0.2)', 'rgba(255,0,42,0.2)', 'rgba(21,232,190,0.2)', 'rgba(255,155,18,0.2)', 'rgba(22,104,190,0.2)']
export default {
    props: {
        dataRank: {
            type: Object,
            default: () => {}
        }
    },
    data() {
        return {
            attactDestAssetNoData: true,
            attackDestInfo: [], // 攻击目的端口分布/攻击协议分布
            attackDestPortActive: true, // 攻击目的端口是否选中
            attackDestDistributedNoData: false,
            attackDestPieOption: {},
            attactDestAssetInfo: [
            ]

        }
    },
    watch: {
        dataRank(old, ves) {
            //

            this.attactDestAssetInfo = ves.trappRank
            // arry.map(item => {
            //     this.attactDestAssetInfo.push({
            //         name: item.name,
            //         value: item.total
            //     })
            // })
            if (this.attactDestAssetInfo.length > 0) {

                this.$nextTick(function() {
                    this.drawattactSsetEcharts()
                })
            }

        }
    },
    created() {
        let $this = this
        $this.attactDestAssetInfo = []
        this.attackDestPieOption = {
            tooltip: {
                show: true,
                trigger: 'item',
                formatter: function(param) {
                    let value = param.data.value
                    let name = param.data.name.split(':')[0]
                    return name + ':' + parseInt(value).toLocaleString() + '次'
                }
            },
            legend: {  // 图例
                orient: 'vertical',
                left: '65%',
                y: 'center',
                itemGap: $this.itemGapSize(),
                itemWidth: $this.itemGapSize(),
                itemHeight: $this.itemGapSize(),
                type: 'scroll',
                pageButtonItemGap: 0,
                pageButtonGap: 0,
                formatter: function(name) {
                    let nameTemp = ''
                    let num = 0
                    if (name) {
                        nameTemp = name.split(':')[0]
                        num = name.split(':')[1]
                    }
                    return $this.subStringCommon(nameTemp, 15, true) + ':' + parseInt(num).toLocaleString() + '次'
                },
                textStyle: {
                    fontSize: $this.fontSize(),
                    color: '#eeeeee'
                },
                tooltip: {
                    show: true,
                    formatter: function(params) {
                        let nameTemp = ''
                        let num = 0
                        if (params.name) {
                            nameTemp = params.name.split(':')[0]
                            num = params.name.split(':')[1]
                        }
                        return nameTemp + ': ' + parseInt(num).toLocaleString() + '次'
                    }
                }
            },
            series: [
                {
                    name: '攻击目的端口分布',
                    type: 'pie', // 环形图的type和饼图相同
                    radius: ['55%', '70%'],
                    center: ['30%', '55%'],
                    avoidLabelOverlap: false,
                    color: colors2,
                    z: 3,
                    label: {
                        normal: {  // 正常的样式
                            show: false,
                            position: 'left',
                            formatter: '{d}%'
                        }
                    },  // 提示文字
                    labelLine: {
                        normal: {
                            show: false
                        }
                    },
                    data: ['50', '40', '30', '10']
                },
                {
                    name: '',
                    type: 'pie',
                    center: ['30%', '55%'],
                    radius: ['34%', '36%'],
                    avoidLabelOverlap: false,
                    hoverAnimation: false,
                    itemStyle: {
                        normal: {
                            color: '#35517E'
                        }
                    },
                    z: 2,
                    label: {
                        normal: {
                            show: false
                        },
                        emphasis: {
                            show: false
                        }
                    },
                    labelLine: {
                        normal: {
                            show: false
                        }
                    },
                    tooltip: {show: false},
                    data: [{value: 100}]
                },
                {
                    name: '',
                    type: 'pie',
                    center: ['30%', '55%'],
                    radius: ['36%', '85%'],
                    avoidLabelOverlap: false,
                    hoverAnimation: false,
                    z: 1,
                    itemStyle: {
                        normal: {
                            color: {
                                type: 'radial',
                                x: 0.5,
                                y: 0.5,
                                r: 0.5,
                                colorStops: [{
                                    offset: 0, color: 'rgba(12,26,89,0.2)' // 0% 处的颜色
                                }, {
                                    offset: 1, color: 'rgba(12,26,89,0.8)' // 100% 处的颜色
                                }],
                                global: false // 缺省为 false
                            }
                        }
                    },
                    label: {
                        normal: {
                            show: false
                        },
                        emphasis: {
                            show: false
                        }
                    },
                    labelLine: {
                        normal: {
                            show: false
                        }
                    },
                    tooltip: {show: false},
                    data: [{value: 100}]
                }
            ]
        },
        this.attactDestAssetOption = {
            textStyle: {
                fontSize: this.fontSize()
            },
            grid: {
                left: '10%',
                right: '0',
                bottom: '15%',
                top: '20%'
            },
            tooltip: {
                trigger: 'axis',
                position: function(point) {
                    return ['40%', point[1]]
                },
                formatter: function(params) {
                    return params[1].name + ':' + this.toThousands(parseInt(params[1].data.realValue)) + '次'
                }
            },
            toolbox: {
                show: false
            },
            legend: {
                show: false
            },
            xAxis: {
                data: [],
                triggerEvent: true,
                axisLabel: {
                    show: true,
                    textStyle: {
                        color: '#eeeeee',
                        fontSize: this.fontSize() - 3
                    },
                    interval: 0,
                    formatter: function(param) {
                        if (screen_width > 1600) {
                            return $this.subStringCommon(param, 10, true)
                        } else {
                            return $this.subStringCommon(param, 8, true)
                        }
                    }
                },
                axisLine: {
                    show: true,
                    lineStyle: {
                        color: '#eeeeee'
                    }
                },
                splitLine: {
                    show: false,
                    lineStyle: {
                        color: '#eeeeee',
                        type: 'solid'
                    }
                }

            },
            yAxis: {
                type: 'value',
                name: '次',
                axisLabel: {
                    show: true,
                    textStyle: {
                        color: '#eeeeee',
                        fontSize: this.fontSize() - 3
                    }
                },
                minInterval: 1,
                axisLine: {
                    show: true,
                    lineStyle: {
                        color: '#eeeeee'
                    }
                },
                splitLine: {
                    show: false,
                    lineStyle: {
                        color: '#eeeeee',
                        type: 'solid'
                    }
                }
            },
            series: [

                {
                    // 右侧背景
                    type: 'pictorialBar',
                    itemStyle: {
                        normal: {
                            color: '#eeeeee'
                        }
                    },
                    silent: true,
                    animationDuration: 0,
                    symbolRepeat: true,
                    symbol: 'rect',
                    symbolSize: this.symbolBigArr(),
                    symbolOffset: [0, 0],
                    symbolMargin: '40%',
                    data: [],
                    barGap: '-100%',
                    z: 1,
                    animationEasing: 'elasticOut',
                    animationDelay: function(dataIndex, params) {
                        return params.index * 30
                    }
                },

                { // 右侧方块
                    name: '攻击目的资产次数',
                    type: 'pictorialBar',
                    itemStyle: {
                        normal: {
                            color: '#5affff'
                        }
                    },
                    symbol: 'rect',
                    symbolOffset: [0, 0],
                    z: 2,
                    symbolRepeat: true,
                    symbolSize: this.symbolBigArr(),
                    symbolMargin: '40%',
                    data: [],
                    animationEasing: 'elasticOut',
                    animationDelay: function(dataIndex, params) {
                        return params.index * 30
                    }
                }
            ]
        }
    },
    mounted() {
        // this.drawattactSsetEcharts()
        if (this.dataRank.trappRank) {
            this.attactDestAssetInfo = this.dataRank.trappRank
            // arry.map(item => {
            //     this.attactDestAssetInfo.push({
            //         name: item.name,
            //         value: item.total
            //     })
            // })
            if (this.attactDestAssetInfo.length > 0) {

                this.$nextTick(function() {
                    this.drawattactSsetEcharts()
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
        // 绘制攻击目的资产排名图表
        drawattactSsetEcharts() {
            const $this = this
            if ($this.attactDestAssetInfo.length > 0) {
                let max = Math.max.apply(Math, $this.attactDestAssetInfo.map(function(o) { return o.value }))
                let unit = this.howMany(max).unit
                let dividend = this.howMany(max).value
                $this.attactDestAssetNoData = false
                let attackDestAssetEcharts = Echarts.init($this.$refs.attactSsetEcharts, null, { renderer: 'svg' })
                let option = $this.attactDestAssetOption
                let name = []
                let value = []
                let gapData = []
                for (let i = 0; i < $this.attactDestAssetInfo.length; i++) {
                    name.push($this.attactDestAssetInfo[i].name)
                    if (max > 1000) {
                        value.push({
                            unit: unit,
                            value: (parseInt($this.attactDestAssetInfo[i].value) / dividend),
                            realValue: $this.attactDestAssetInfo[i].value
                        })
                        gapData.push(max / dividend)
                    } else {
                        value.push({
                            unit: unit,
                            value: parseInt($this.attactDestAssetInfo[i].value),
                            realValue: $this.attactDestAssetInfo[i].value
                        })
                        gapData.push(max)
                    }
                }
                option.yAxis.name = unit
                option.xAxis.data = name
                option.series[0].data = gapData
                option.series[1].data = value
                attackDestAssetEcharts.setOption(option)
                $this.$nextTick(function() {
                    attackDestAssetEcharts.resize()
                })

            } else {
                $this.attactDestAssetNoData = true
            }
        }
    }
}
</script>
