<template>
    <dv-scroll-ranking-board :config="config" class="LeftChart1" />
</template>

<script>
export default {
    name: 'LeftChart3',
    data() {
        return {
            config: {
                rowNum: 5,
                unit: '次',
                data: [
                    {
                        name: '周口',
                        value: 55
                    },
                    {
                        name: '南阳',
                        value: 120
                    },
                    {
                        name: '西峡',
                        value: 78
                    },
                    {
                        name: '驻马店',
                        value: 66
                    },
                    {
                        name: '新乡',
                        value: 80
                    },
                    {
                        name: '信阳',
                        value: 45
                    },
                    {
                        name: '漯河',
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
