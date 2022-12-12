<template>
    <iframe :src="$route.meta.link" frameborder="0" />
</template>

<script>
export default {
    name: 'IframeLayout',
    props: {},
    data() {
        return {}
    },
    created() {},
    mounted() {},
    methods: {}
}
</script>

<style lang="scss" scoped>
iframe {
    position: absolute;
    z-index: 1;
    width: 100%;
    height: calc(100% - #{$g-topbar-height});
}
</style>
