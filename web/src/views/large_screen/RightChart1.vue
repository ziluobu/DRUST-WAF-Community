<template>
    <dv-scroll-ranking-board :config="config" class="LeftChart1" />
</template>

<script>
export default {
    name: 'RightChart1',
    data() {
        return {
            config: {
                rowNum: 5,
                unit: '次',
                data: [
                    {
                        name: 'www.baidu.com',
                        value: 55
                    },
                    {
                        name: '185.26.24.6',
                        value: 120
                    },
                    {
                        name: '185.24.3.9',
                        value: 78
                    },
                    {
                        name: '1.2.5.36',
                        value: 66
                    },
                    {
                        name: '2.5.5.8',
                        value: 80
                    },
                    {
                        name: '2.5.45.5',
                        value: 45
                    },
                    {
                        name: '2.5.5.8',
                        value: 29
                    }
                ]
            }
        }
    }
}
</script>

<style lang="scss" scoped>
.LeftChart1 {
    width: 100%;
    height: 80%;
    padding: 5px 2px;
}
</style>
