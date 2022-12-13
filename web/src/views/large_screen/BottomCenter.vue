<template>
    <dv-charts :option="option" class="BottomLeft" />
</template>

<script>
import moment from 'moment'
export default {
    name: 'BottomLeft',
    data() {
        return {
            attackArry: [],
            attackNum: [],
            visitNum: [],
            visitArry: [],
            option: ''
        }
    },
    mounted() {
        this.getList()
    },
    methods: {
        getList() {
            //
            // now = moment().format('YYYY-MM-DD');//2020-07-06

            this.$api.post('api/index/visitAttackOverView', {
                date: moment().format('YYYY-MM-DD')
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
                        let attackArry = []
                        let attackNum = []
                        arry.forEach(item => {
                            attackArry.push(item.date),

                            attackNum.push(item.num)
                        })
                        arry1.forEach(item => {
                            this.attackArry.push(item.date),

                            this.visitNum.push(item.num)
                        })
                        let optiosInit = {
                            legend: {
                                data: [
                                    {
                                        name: '攻击次数',
                                        color: '#00baff'
                                    }
                                ],
                                textStyle: {
                                    fill: '#fff'
                                }
                            },
                            xAxis: {
                                data: [
                                    '10/01', '10/02', '10/03', '10/04', '10/05', '10/06', '10/07', '10/07', '10/07'
                                ],
                                axisLine: {
                                    style: {
                                        stroke: '#999'
                                    }
                                },
                                axisLabel: {
                                    style: {
                                        fill: '#999'
                                    }
                                },
                                axisTick: {
                                    show: false
                                }
                            },
                            yAxis: {
                                data: 'value',
                                splitLine: {
                                    show: false
                                },
                                axisLine: {
                                    style: {
                                        stroke: '#999'
                                    }
                                },
                                axisLabel: {
                                    style: {
                                        fill: '#999'
                                    }
                                },
                                axisTick: {
                                    show: false
                                },
                                min: 0
                            },
                            series: [
                                {
                                    name: '攻击次数',
                                    data: [
                                        2.5, 3.5, 6.5, 6.5, 7.5, 6.5, 2.5, 6.5, 2.5
                                    ],
                                    type: 'line',
                                    lineStyle: {
                                        stroke: '#ff5ca9'
                                    },
                                    linePoint: {
                                        radius: 4,
                                        style: {
                                            fill: '#ff5ca9',
                                            stroke: 'transparent'
                                        }
                                    }
                                }
                            ]
                        }
                        this.option = {...optiosInit}
                    }

                })

        }
    }
}
</script>

<style lang="less">
.BottomLeft {
    width: 100%;
    height: 100%;
    display: flex;
    justify-content: center;
    position: relative;
    top: -35px;
}
</style>
