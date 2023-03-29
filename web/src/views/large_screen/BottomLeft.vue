<template>
    <dv-charts :option="option" class="BottomLeft" />
</template>

<script>
export default {
    name: 'BottomLeft',
    data() {
        return {
            option: {
                title: {
                    text: ''
                },
                xAxis: {
                    name: '',
                    data: [
                        '10/01', '10/02', '10/03', '10/04', '10/05', '10/06', '10/07', '10/07', '10/07'
                    ]
                },
                yAxis: {
                    name: '',
                    data: 'value',
                    splitLine: {
                        show: false
                    }
                },
                series: [
                    {
                        data: [
                            2.5, 3.5, 6.5, 6.5, 7.5, 6.5, 2.5, 6.5, 2.5
                        ],
                        type: 'bar',
                        gradient: {
                            color: ['#37a2da', '#67e0e3']
                        }
                    }
                ]
            }

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
