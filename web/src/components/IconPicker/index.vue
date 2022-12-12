<template>
    <el-popover v-model="visible" placement="bottom" trigger="hover" width="250" @show="handlePopoverShow">
        <div slot="reference" class="picker-container">
            <svg-icon v-if="myValue != ''" :name="myValue" />
            <i v-else class="el-icon-plus" />
        </div>
        <el-tabs ref="tabs" v-model="activeTab">
            <el-tab-pane label="Element Icon" name="element">
                <el-input v-model="element.search" size="mini" placeholder="请输入搜索关键词" prefix-icon="el-icon-search" clearable />
                <div class="list-icon">
                    <span v-for="(item, index) in elementIconCurrentList" :key="index" class="list-icon-item" @click="choose(`el-icon-${item}`)">
                        <svg-icon :name="`el-icon-${item}`" />
                    </span>
                </div>
                <el-pagination small layout="prev, pager, next" :current-page.sync="element.currentPage" :page-size="element.pageSize" :total="elementIconList.length" :pager-count="5" />
            </el-tab-pane>
            <el-tab-pane label="Remix Icon" name="remix">
                <el-input v-model="remix.search" size="mini" placeholder="请输入搜索关键词" prefix-icon="el-icon-search" clearable />
                <div class="list-icon">
                    <span v-for="(item, index) in remixIconCurrentList" :key="index" class="list-icon-item" @click="choose(item.type == 'Editor' ? `ri-${item.name}` : `ri-${item.name}-${remix.style}`)">
                        <svg-icon :name="item.type == 'Editor' ? `ri-${item.name}` : `ri-${item.name}-${remix.style}`" />
                    </span>
                </div>
                <div class="style-choose">
                    <el-radio-group v-model="remix.style" size="mini">
                        <el-radio-button label="line">线条</el-radio-button>
                        <el-radio-button label="fill">填充</el-radio-button>
                    </el-radio-group>
                </div>
                <el-pagination small layout="prev, pager, next" :current-page.sync="remix.currentPage" :page-size="remix.pageSize" :total="remixIconList.length" :pager-count="5" />
            </el-tab-pane>
        </el-tabs>
    </el-popover>
</template>

<script>
import elementIcon from './element.json'
import remixIcon from './remix.json'

export default {
    name: 'IconPicker',
    props: {
        value: {
            type: String,
            default: ''
        }
    },
    data() {
        return {
            visible: false,
            activeTab: 'element',
            element: {
                search: '',
                icons: elementIcon,
                pageSize: 20,
                currentPage: 1
            },
            remix: {
                search: '',
                icons: [],
                style: 'line',
                pageSize: 20,
                currentPage: 1
            },
            myValue: ''
        }
    },
    computed: {
        elementIconList() {
            let list = this.element.icons
            if (this.element.search != '') {
                list = list.filter(item => {
                    return item.indexOf(this.element.search) >= 0
                })
            }
            return list
        },
        elementIconCurrentList() {
            return this.elementIconList.slice(
                (this.element.currentPage - 1) * this.element.pageSize,
                (this.element.currentPage - 1) * this.element.pageSize + this.element.pageSize
            )
        },
        remixIconList() {
            let list = this.remix.icons
            if (this.remix.search != '') {
                list = list.filter(item => {
                    return item.keyword.includes(this.remix.search) || item.name.indexOf(this.remix.search) >= 0
                })
            }
            return list
        },
        remixIconCurrentList() {
            return this.remixIconList.slice(
                (this.remix.currentPage - 1) * this.remix.pageSize,
                (this.remix.currentPage - 1) * this.remix.pageSize + this.remix.pageSize
            )
        }
    },
    watch: {
        value: {
            handler(newValue) {
                this.myValue = newValue
            },
            immediate: true
        },
        myValue(newValue) {
            this.$emit('input', newValue)
        }
    },
    mounted() {
        let remix = []
        for (var key in remixIcon) {
            for (var name in remixIcon[key]) {
                remix.push({
                    name: name,
                    type: key,
                    keyword: remixIcon[key][name]
                })
            }
        }
        this.remix.icons = remix
    },
    methods: {
        handlePopoverShow() {
            this.$refs.tabs.calcPaneInstances(true)
        },
        choose(val) {
            this.myValue = val
            this.visible = false
        }
    }
}
</script>

<style lang="scss" scoped>
.el-tabs {
    margin-top: -10px;
    ::v-deep .el-tabs__nav-scroll {
        text-align: center;
        .el-tabs__nav {
            display: inline-block;
            margin: 0 auto;
            float: none;
            .el-tabs__item {
                padding: 0 12px;
            }
        }
    }
}
.picker-container {
    width: 40px;
    height: 40px;
    line-height: 36px;
    text-align: center;
    border: 1px dashed #dcdfe6;
    border-radius: 5px;
    cursor: pointer;
    transition: 0.3s;
    &:hover {
        border-color: #909399;
        border-style: solid;
    }
    i {
        font-size: 24px;
        vertical-align: middle;
        color: #606266;
    }
}
.list-icon {
    .list-icon-item {
        display: inline-block;
        margin-right: 3px;
        margin-bottom: 3px;
        padding: 2px 8px 6px;
        border: 1px solid #dcdfe6;
        font-size: 24px;
        cursor: pointer;
        transition: 0.3s;
        &:nth-child(5n + 5) {
            margin-right: 0;
        }
        &:hover {
            border-color: #c5e1fe;
            background-color: #ecf5ff;
            color: #409eff;
        }
        i {
            vertical-align: middle;
        }
    }
}
.el-input {
    margin-bottom: 12px;
}
.style-choose {
    margin-top: 5px;
    text-align: center;
}
.el-pagination {
    margin-top: 5px;
    text-align: center;
}
</style>
