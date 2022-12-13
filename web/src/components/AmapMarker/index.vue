<template>
    <div class="map">
        <el-autocomplete v-model="search" class="search" :fetch-suggestions="onSearch" :trigger-on-focus="false" clearable prefix-icon="el-icon-location-information" placeholder="请输入地址关键字" @select="onSelect">
            <template slot-scope="{ item }">
                <div class="search-name">{{ item.name }}</div>
                <span class="search-address" :title="item.district + item.address">{{ item.district + item.address }}</span>
            </template>
        </el-autocomplete>
        <div id="amap" :style="`height:${realHeight};`" />
    </div>
</template>

<script>
export default {
    name: 'AmapMarker',
    props: {
        v: {
            type: String,
            default: '1.4.15'
        },
        appkey: {
            type: String,
            default: ''
        },
        height: {
            type: [Number, String],
            default: 500
        },
        lnglat: {
            type: Array,
            default: () => []
        }
    },
    data() {
        return {
            search: '',
            searchOption: {
                citylimit: true
            },
            map: '',
            marker: '',
            autoComplete: '',
            placeSearch: ''
        }
    },
    computed: {
        realHeight() {
            return typeof this.height == 'string' ? this.height : `${this.height}px`
        }
    },
    created() {},
    mounted() {
        if (typeof AMap === 'undefined') {
            var script = document.createElement('script')
            script.charset = 'utf-8'
            script.src = `https://webapi.amap.com/maps?v=${this.v}&key=${this.appkey}&plugin=AMap.Autocomplete`
            document.head.appendChild(script)
            script.onload = () => {
                this.init()
            }
        } else {
            this.$nextTick(() => {
                this.init()
            })
        }
    },
    methods: {
        init() {
            this.map = new AMap.Map('amap', {
                zoom: 12
            })
            AMap.plugin('AMap.ToolBar', () => {
                var toolbar = new AMap.ToolBar()
                this.map.addControl(toolbar)
            })
            this.marker = new AMap.Marker({
                draggable: true,
                cursor: 'move'
            })
            this.marker.on('dragend', e => {
                this.$emit('update:lnglat', [e.lnglat.lng, e.lnglat.lat])
            })
            this.autoComplete = new AMap.Autocomplete()
            this.map.on('click', e => {
                this.addMarket(e.lnglat.getLng(), e.lnglat.getLat())
            })
            if (this.lnglat.length == 2) {
                this.addMarket(this.lnglat[0], this.lnglat[1])
            }
        },
        addMarket(lng, lat) {
            this.map.remove(this.marker)
            this.marker.setPosition([lng, lat])
            this.map.add(this.marker)
            this.map.setFitView()
            this.$emit('update:lnglat', [lng, lat])
        },
        onSearch(queryString, cb) {
            this.autoComplete.search(queryString, (status, result) => {
                cb(result.tips)
            })
        },
        onSelect(item) {
            this.search = item.name
            item.location ? this.addMarket(item.location.lng, item.location.lat) : this.map.setCity(item.adcode)
        }
    }
}
</script>

<style lang="scss" scoped>
.search-name {
    padding-top: 10px;
    line-height: normal;
    @include text-overflow;
}
.search-address {
    font-size: 12px;
    color: #b4b4b4;
    line-height: normal;
}
.map {
    position: relative;
    .search {
        position: absolute;
        z-index: 1;
        left: 72px;
        top: 15px;
        ::v-deep .el-input input {
            box-shadow: 0 0 5px #999;
        }
    }
}
</style>
