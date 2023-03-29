<template>
    <div />
</template>

<script>
import watermark from 'watermark-dom'

export default {
    watch: {
        '$store.state.settings.enableWatermark': {
            handler(val) {
                if (val) {
                    // 水印更多设置请查看 https://github.com/saucxs/watermark-dom
                    this.$api.post('api/userInfo')
                        .then(res => {
                            // console.log(res.data)
                            if (res.data) {
                                watermark.init({
                                    // watermark_txt: `Fantastic-admin 水印测试 ${this.$store.state.user.account}`,
                                    watermark_txt: res.data.group_name,
                                    watermark_width: 150,
                                    watermark_x: 0,
                                    watermark_y: 0,
                                    watermark_x_space: 100,
                                    watermark_y_space: 100,
                                    watermark_alpha: 0.1
                                })

                            }

                        })

                } else {
                    // watermark.remove 有 bug，改用设置空字符串水印来达到移除效果
                    watermark.init({
                        watermark_txt: ' '
                    })
                }
            },
            immediate: true
        }
    }
}
</script>
