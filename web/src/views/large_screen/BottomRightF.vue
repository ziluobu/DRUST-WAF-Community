<template>
    <div style="height: 150px; width: 100%;" class="content1">
        <div v-show="!attackTypeNoData" ref="attackType" style="width: 100%; height: 100%;" />
    </div>
</template>
<script>
const Echarts = require('echarts')
const screen_width = window.screen.width
const colors1 = ['#5affff', '#ff002a', '#15e8be', '#e19b12', '#1668be']
export default {
    props: {
        dataRank: {
            type: Object,
            default: () => {}
        }
    },
    data() {
        return {
            attackTypeInfo: [
                {
                    count: '',
                    key: ''
                }

            ], // 攻击类型分布
            attackTypeNoData: false,
            attackTypeOption: {}
        }
    },
    watch: {
        dataRank(old, ves) {
            //
            let arry = ves.typeRank
            arry.map(item => {
                this.$set(item, 'key', item.name)
                this.$set(item, 'count', item.value)
            })
            this.attackTypeInfo = arry
            if (this.attackTypeInfo.length > 0) {
                this.attackSrcNoData = false
                this.$nextTick(function() {
                    this.queryAttackTypeInfo()
                })
            }

        }
    },
    created() {
        let $this = this
        this.attackTypeOption = {
            color: colors1,
            tooltip: {
                trigger: 'item',
                formatter: function(params) {
                    let nameTemp = ''
                    let num = 0
                    if (params.name) {
                        nameTemp = params.name.split(':')[0]
                        num = params.name.split(':')[1]
                    }
                    return nameTemp + ': ' + parseInt(num).toLocaleString() + '次'
                }
            },
            legend: {
                icon: 'rect',
                orient: 'vertical',
                left: '55%',
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
                    return $this.subStringCommon(nameTemp, 15, true) + ': ' + parseInt(num).toLocaleString() + '次'
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
                },
                data: []
            },
            series: [
                {
                    name: '攻击类型分布',
                    type: 'pie',
                    radius: ['55%', '70%'],
                    center: ['30%', '50%'],
                    avoidLabelOverlap: false,
                    label: {
                        show: false,
                        position: 'center'
                    },
                    z: 3,
                    emphasis: {
                        label: {
                            show: true,
                            fontSize: '30',
                            fontWeight: 'bold'
                        }
                    },
                    labelLine: {
                        show: false
                    },
                    itemStyle: {
                        normal: {
                            label: {
                                show: false
                            },
                            labelLine: {
                                show: false
                            }
                        }
                    },
                    data: []
                },
                {
                    name: '',
                    type: 'pie',
                    center: ['30%', '50%'],
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
                    data: [{value: 100}]
                },
                {
                    name: '',
                    type: 'pie',
                    center: ['30%', '50%'],
                    radius: ['36%', '85%'],
                    avoidLabelOverlap: false,
                    hoverAnimation: false,
                    z: 1,
                    tooltip: {show: false },
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
                    data: [{value: 100}]
                }
            ]
        }
    },
    mounted() {

        if (this.dataRank.typeRank) {
            let arry = this.dataRank.typeRank
            arry.map(item => {
                this.$set(item, 'key', item.name)
                this.$set(item, 'count', item.value)
            })
            this.attackTypeInfo = arry
            if (this.attackTypeInfo.length > 0) {

                this.$nextTick(function() {
                    this.queryAttackTypeInfo()
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
        queryAttackTypeInfo() {
            let seriesData = []
            this.attackTypeInfo.forEach(obj => {
                seriesData.push({
                    name: obj.key + ':' + obj.value,
                    value: obj.value
                })
            })
            this.attackTypeInfo = seriesData
            this.drawAttackTypeEcharts()
        },
        drawAttackTypeEcharts() {
            const $this = this
            if ($this.attackTypeInfo.length > 0) {
                $this.attackTypeNoData = false
                let attackTypeEcharts = Echarts.init($this.$refs.attackType, null, { renderer: 'svg' })
                let option = $this.attackTypeOption
                option.series[0].data = $this.attackTypeInfo
                option.legend.data = $this.attackTypeInfo
                attackTypeEcharts.setOption(option)
                $this.$nextTick(function() {
                    attackTypeEcharts.resize()
                })

            } else {
                this.attackTypeNoData = true
            }
        }
    }
}
</script>
