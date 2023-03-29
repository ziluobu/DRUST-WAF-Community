<template>
    <div ref="chart" style="width: 100%; height: 100%;" />
</template>
<script>
import china from '@/util/中华人民共和国.json'

export default {
    data() {
        return {
            mapData: undefined
        }
    },
    created() {
        this.$nextTick(() => {
            this.init()
        })
    },
    methods: {
        init() {
            const coord = china.features.map(val => {
                return {
                    name: val.properties.name,
                    value: val.properties.center
                }
            })
            let lines_coord = []
            coord.forEach((v, index) => {
                if (v.value && !v.name.match(RegExp(/河/))) {
                    index > 0 && lines_coord.push({
                        coords: [v.value, coord[0].value]
                    })
                }
            })
            china.features.forEach(v => {
                v.properties.name = v.properties.name.indexOf('版纳') > -1 ? v.properties.name.substr(0, 4) : v.properties.name.substr(0, 2)
            })
            this.$echarts.registerMap('china', china)
            this.$echarts.init(this.$refs.chart).setOption({
                geo: {
                    map: 'china',
                    zlevel: 10,
                    show: true,
                    layoutCenter: ['50%', '50%'],
                    layoutSize: '90%',
                    roam: true,
                    zoom: 1.5,
                    label: {
                        normal: {
                            show: true,
                            textStyle: {
                                fontSize: 12,
                                color: '#43D0D6'
                            }
                        }
                    },
                    itemStyle: {
                        normal: {
                            color: '#062031',
                            borderWidth: 1.1,
                            borderColor: '#43D0D6'
                        }
                    },
                    emphasis: {
                        areaColor: '#FFB800',
                        label: {
                            show: false
                        }
                    }
                },
                series: [
                    {
                        type: 'effectScatter',
                        coordinateSystem: 'geo',
                        zlevel: 15,
                        symbolSize: 8,
                        rippleEffect: {
                            period: 4, brushType: 'stroke', scale: 4
                        },
                        itemStyle: {
                            color: '#FFB800',
                            opacity: 1
                        },
                        data: coord.slice(0, 10)
                    },
                    {
                        type: 'effectScatter',
                        coordinateSystem: 'geo',
                        zlevel: 15,
                        symbolSize: 12,
                        rippleEffect: {
                            period: 6, brushType: 'stroke', scale: 8
                        },
                        itemStyle: {
                            color: '#FF5722',
                            opacity: 1
                        },
                        data: coord.slice(0, 1)
                    },
                    {
                        type: 'lines',
                        coordinateSystem: 'geo',
                        zlevel: 15,

                        effect: {
                            show: true, period: 5, trailLength: 0, symbol: 'arrow', color: '#01AAED', symbolSize: 8
                        },
                        lineStyle: {
                            normal: {width: 1.2, opacity: 0.6, curveness: 0.2, color: '#FFB800'}
                        },
                        data: lines_coord.slice(0, 10)
                    }
                ]
            })
        }
    }

}
</script>
