<template>
    <dv-scroll-board :config="config" class="LeftChart1" />
</template>

<script>
export default {
    name: 'LeftChart2',
    data() {
        return {
            config: {
                rowNum: 5,
                align: ['center'],
                data: [
                    ['行1列1', '行1列3'],
                    ['行2列1', '行2列3'],
                    ['行3列1', '行3列3'],
                    ['行4列1', '行4列3'],
                    ['行5列1', '行5列3'],
                    ['行6列1', '行6列3'],
                    ['行7列1', '行7列3'],
                    ['行8列1', '行8列3'],
                    ['行9列1', '行9列3'],
                    ['行10列1', '行10列3']
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
