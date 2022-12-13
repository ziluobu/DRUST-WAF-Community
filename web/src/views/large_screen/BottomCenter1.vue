<template>
    <div class="center-top">
        <div class="center-top-top">
            <div>
                <div class="radar_img">
                    <img src="../../assets/images/aa.gif">
                    <!-- <img src="../../assets/images/radar.png"> -->
                </div>
                <div class="radar_info">
                    <div>
                        <span v-text="total" />
                    </div>
                    <div class="tet-info">总攻击数</div>
                </div>
            </div>
            <div>
                <div class="radar_img">
                    <img src="../../assets/images/aa.gif">
                    <!-- <img src="../../assets/images/radar.png"> -->
                </div>
                <div class="radar_info">
                    <div>
                        <span v-text="loopholeAttackCount" />
                    </div>
                    <div class="tet-info">总访问数</div>
                </div>
            </div>
            <div>
                <div class="radar_img">
                    <img src="../../assets/images/aa.gif">
                    <!-- <img src="../../assets/images/radar.png"> -->
                </div>
                <div class="radar_info">
                    <div>
                        <span v-text="virusAttackCount" />
                    </div>
                    <div class="tet-info">攻击IP数</div>
                </div>
            </div>
            <div>
                <div class="radar_img">
                    <img src="../../assets/images/aa.gif">
                </div>
                <div class="radar_info">
                    <div>
                        <span v-text="passedAttackCount" />
                    </div>
                    <div class="tet-info">诱捕次数</div>
                </div>
            </div>
            <!-- <div>
                <div class="radar_img">
                    <img src="../../assets/images/aa.gif">

                </div>
                <div class="radar_info">
                    <div>
                        <span @click="drillDown('&action=未拦截&attackType=漏洞利用')" v-text="passedLoopHoleAttackCount" />
                    </div>
                    <div class="tet-info" @click="drillDown('&action=未拦截&attackType=漏洞利用')">攻击告警</div>
                </div>
            </div> -->
            <!-- <div>
                <div class="radar_img">
                    <img src="../../assets/images/aa.gif">
                </div>
                <div class="radar_info">
                    <div>
                        <span @click="drillDown('&action=未拦截&attackType=恶意文件')" v-text="passedVirusAttackCount" />
                    </div>
                    <div class="tet-info" @click="drillDown('&action=未拦截&attackType=恶意文件')">被攻击域名</div>
                </div>
            </div> -->
        </div>
        <div class="center-top-bottom">
            <div id="mapEcharts" ref="mapEcharts" style="width: 100%; height: 100%;" />
        </div>
    </div>
</template>
<script>
const sevenContinents = ['非洲', '北美洲', '欧洲', '亚洲', '南美洲', '大洋洲', '南极洲']
const provinces = ['shanghai', 'hebei', 'shanxi', 'neimenggu', 'liaoning', 'jilin', 'heilongjiang', 'jiangsu', 'zhejiang', 'anhui', 'fujian', 'jiangxi', 'shandong', 'henan', 'hubei', 'hunan', 'guangdong', 'guangxi', 'hainan', 'sichuan', 'guizhou', 'yunnan', 'xizang', 'shanxi1', 'gansu', 'qinghai', 'ningxia', 'xinjiang', 'beijing', 'tianjin', 'chongqing', 'xianggang', 'aomen', 'taiwan', 'neimenggu', 'xizang', 'ningxia', 'xinjiang', 'guangxi', 'xianggang', 'aomen']
const provincesText = ['上海', '河北', '山西', '内蒙古自治区', '辽宁', '吉林', '黑龙江',  '江苏', '浙江', '安徽', '福建', '江西', '山东', '河南', '湖北', '湖南', '广东', '广西壮族自治区', '海南', '四川', '贵州', '云南', '西藏自治区', '陕西', '甘肃', '青海', '宁夏回族自治区', '新疆维吾尔自治区', '北京', '天津', '重庆', '香港特别行政区', '澳门特别行政区', '台湾', '内蒙古', '西藏', '宁夏', '新疆', '广西', '香港', '澳门']
// const colors1 = ['#5affff', '#ff002a', '#15e8be', '#e19b12', '#1668be']
// const colors2 = ['rgba(90,255,255,0.2)', 'rgba(255,0,42,0.2)', 'rgba(21,232,190,0.2)', 'rgba(255,155,18,0.2)', 'rgba(22,104,190,0.2)']
import geoCoordList from '@/util/geoCoordList'
import countryMapList from '@/util/countryMapList'
import countryArrList from '@/util/countryArrList'
import worldMapJsonL from '@/util/worldMapJsonL'
import geoJsonList from '@/util/中华人民共和国.json'
const Echarts = require('echarts')
// let screen_width = window.screen.width;
// let foreign = "欧洲";
export default {
    props: {
        dataInfo: {
            type: Object,
            default: () => {}
        },
        dataAttack: {
            type: Array,
            default: () => {}
        }
    },
    data() {
        return {
            geoCoord: geoCoordList,
            countryMap: countryMapList,
            countryArr: countryArrList,
            worldMapJson: worldMapJsonL,
            timeSelected: '',
            screen_width: '',
            foreign: '欧洲',
            chart_length: 16,
            maxAttackCount: 0,
            attackSrcIpInfo: [],
            attackSrcNoData: false,
            titleText: '国内攻击源IP排名Top5',
            region: '', // 地球信息
            roamValue: false, // 禁止缩放
            attackList: [], // 攻击列表
            attackListNoData: false, // 攻击列表数据为空
            mapEcharts: null,
            total: 0, // 总攻击数
            loopholeAttackCount: 0, // 总访问次数,
            virusAttackCount: 0, // 攻击IP数
            passedAttackCount: 0, // 诱捕次数
            passedLoopHoleAttackCount: 20, // 未拦截漏洞利用
            passedVirusAttackCount: 50, // 未拦截僵木蠕,
            attackMapOption: {

            },
            attackProvinceInfo: [], // 攻击城市信息
            dataAttackBox: []

        }
    },
    watch: {
        dataInfo(odl, ves) {
            this.total = ves.attackTotal
            this.loopholeAttackCount = ves.visitTotal
            this.virusAttackCount = ves.attackIpCount
            this.passedAttackCount = ves.trappCount

        },
        dataAttack(odlVal, valNew) {
            this.dataAttackBox = valNew
            this.queryCustomInfo()
            this.drawChinaMap()
        }
    },

    created() {
        let _this = this
        _this.$nextTick(function() {
            _this.attackMapOption = {
                tooltip: {
                    trigger: 'item',
                    formatter: '{c}',
                    show: false
                },
                geo: {
                    map: 'china',
                    label: {
                        normal: {
                            show: true,
                            textStyle: {
                                color: '#fff',
                                fontSize: _this.fontSize()
                            }
                        },
                        emphasis: {
                            textStyle: {
                                color: '#5affff',
                                fontSize: _this.fontSize()
                            }
                        }
                    },
                    left: '34%',
                    itemStyle: {
                        normal: {
                            areaColor: '#2c5fde',
                            borderColor: '#031a56'
                        },
                        emphasis: {
                            areaColor: '#19b1ff'
                        }
                    },
                    roam: false, // 设置是否缩放
                    zoom: 1.2,
                    scaleLimit: {
                        min: 1.2
                    }// 设置最小缩放比例
                },
                series: [
                    {
                        name: '地名：',
                        type: 'map',
                        geoIndex: 0,
                        data: []
                    },
                    {
                        name: '',
                        type: 'lines',
                        zlevel: 2,
                        effect: {
                            show: true,
                            period: 3, // 箭头指向速度，值越小速度越快
                            trailLength: 1, // 特效尾迹长度[0,1]值越大，尾迹越长重
                            symbol: 'arrow', // 箭头图标
                            color: '#ffde00',
                            symbolSize: _this.symbolBig()
                            // symbol: 'circle' // 箭头图标
                        },
                        lineStyle: {
                            normal: {
                                color: '#ffde00',
                                width: 0.5,
                                curveness: 0.1
                            }
                        },
                        data: []
                    },
                    {
                        name: '',
                        type: 'scatter',
                        coordinateSystem: 'geo',
                        zlevel: 2,
                        rippleEffect: {
                            brushType: 'stroke',
                            scale: 5
                        },
                        label: {
                            normal: {
                                show: true,
                                fontSize: _this.fontSize(),
                                formatter: function(param) {
                                    return param.value[2] + '次'
                                },
                                textStyle: {
                                    fontSize: this.fontSize(),
                                    fontFamily: 'Microsoft YaHei',
                                    color: '#fff',
                                    fontStyle: 'normal'
                                }
                            },
                            emphasis: {
                                show: true,
                                fontSize: _this.fontSize(),
                                formatter: function(param) {
                                    return param.name + ':' + param.value[2] + '次'
                                },
                                textStyle: {
                                    fontSize: this.fontSize(),
                                    fontFamily: 'Microsoft YaHei',
                                    color: '#fff',
                                    fontStyle: 'normal'
                                }

                            }
                        },
                        symbol: 'pin',
                        symbolSize: function(value) {
                            let minSize = 60
                            let maxSize = 80
                            return value[2] * (maxSize - minSize) / this.maxAttackCount + minSize
                        },
                        itemStyle: {
                            normal: {
                                color: '#ebe814',
                                opacity: 0.8
                            }
                        },
                        data: []
                    },
                    {
                        name: '',
                        type: 'scatter',
                        cursor: 'default',
                        coordinateSystem: 'geo',
                        zlevel: 2,
                        rippleEffect: {
                            brushType: 'stroke',
                            scale: 5
                        },
                        label: {
                            normal: {
                                show: true,
                                fontSize: _this.fontSize(),
                                position: 'right',
                                textStyle: {
                                    fontSize: _this.fontSize(),
                                    fontFamily: 'Microsoft YaHei',
                                    color: '#fff',
                                    fontStyle: 'normal'
                                },
                                formatter: function(param) {
                                    if (param.data.srcCountry !== '中国') {
                                        return param.data.srcCountry
                                    }
                                    return param.name
                                }
                            }
                        },
                        symbolSize: this.bubbleSize(),
                        itemStyle: {
                            normal: {
                                color: 'rgba(90,255,255,1.0)'
                            }
                        },
                        data: []
                    },
                    {
                        name: '',
                        type: 'scatter',
                        coordinateSystem: 'geo',
                        zlevel: 2,
                        rippleEffect: {
                            brushType: 'stroke',
                            scale: 10
                        },
                        label: {
                            normal: {
                                show: true,
                                fontSize: _this.fontSize(),
                                position: 'right',
                                formatter: function(param) {
                                    return param.value[3]
                                },
                                textStyle: {
                                    fontSize: _this.fontSize(),
                                    fontFamily: 'Microsoft YaHei',
                                    color: '#fff',
                                    fontStyle: 'normal'
                                }
                            }
                        },
                        symbolSize: _this.bubbleSize(),
                        itemStyle: {
                            normal: {
                                color: '#5affff'
                            }
                        },
                        data: []
                    },
                    {
                        name: '',
                        type: 'scatter',
                        coordinateSystem: 'geo',
                        zlevel: 1,
                        symbol: 'image://' + require('@/assets/images/01.png'),
                        symbolSize: this.imageSize(),
                        label: {
                            normal: {
                                show: true,
                                fontSize: _this.fontSize(),
                                position: 'right',
                                textStyle: {
                                    fontSize: _this.fontSize(),
                                    fontFamily: 'Microsoft YaHei',
                                    color: '#fff',
                                    fontStyle: 'normal'
                                },
                                formatter: function() {
                                    //
                                    return '非洲'
                                }
                            }
                        },
                        data: [{name: '非洲', value: [49, 50]}]
                    },
                    {
                        name: '',
                        type: 'scatter',
                        coordinateSystem: 'geo',
                        zlevel: 1,
                        symbol: 'image://' + require('@/assets/images/02.png'),
                        symbolSize: _this.imageSize(),
                        label: {
                            normal: {
                                show: true,
                                fontSize: _this.fontSize(),
                                position: 'right',
                                textStyle: {
                                    fontSize: _this.fontSize(),
                                    fontFamily: 'Microsoft YaHei',
                                    color: '#fff',
                                    fontStyle: 'normal'
                                },
                                formatter: function() {
                                    //
                                    return '北美洲'
                                }
                            }
                        },
                        data: [{name: '北美洲', value: [49, 44]}]
                    },
                    {
                        name: '',
                        type: 'scatter',
                        coordinateSystem: 'geo',
                        zlevel: 1,
                        symbol: 'image://' + require('@/assets/images/03.png'),
                        symbolSize: _this.imageSize(),
                        label: {
                            normal: {
                                show: true,
                                fontSize: _this.fontSize(),
                                position: 'right',
                                textStyle: {
                                    fontSize: _this.fontSize(),
                                    fontFamily: 'Microsoft YaHei',
                                    color: '#fff',
                                    fontStyle: 'normal'
                                },

                                formatter: function() {
                                    //
                                    return '欧洲'
                                }
                            }
                        },
                        data: [{name: '欧洲', value: [49, 38]}]
                    },
                    {
                        name: '',
                        type: 'scatter',
                        coordinateSystem: 'geo',
                        zlevel: 1,
                        symbol: 'image://' + require('@/assets/images/04.png'),
                        symbolSize: _this.imageSize(),
                        label: {
                            normal: {
                                show: true,
                                fontSize: _this.fontSize(),
                                position: 'right',
                                textStyle: {
                                    fontSize: _this.fontSize(),
                                    fontFamily: 'Microsoft YaHei',
                                    color: '#fff',
                                    fontStyle: 'normal'
                                },
                                formatter: function() {
                                    //
                                    return '亚洲'
                                }
                            }
                        },
                        data: [{name: '亚洲', value: [49, 32]}]
                    },
                    {
                        name: '',
                        type: 'scatter',
                        coordinateSystem: 'geo',
                        zlevel: 1,
                        symbol: 'image://' + require('@/assets/images/05.png'),
                        // symbol: 'image://' + context + '/static/images/extranetAttack/05.png',
                        symbolSize: _this.imageSize(),
                        label: {
                            normal: {
                                show: true,
                                fontSize: _this.fontSize(),
                                position: 'right',
                                textStyle: {
                                    fontSize: _this.fontSize(),
                                    fontFamily: 'Microsoft YaHei',
                                    color: '#fff',
                                    fontStyle: 'normal'
                                },
                                formatter: function() {
                                    //
                                    return '南美洲'
                                }
                            }
                        },
                        data: [{name: '南美洲', value: [49, 26]}]
                    },
                    {
                        name: '',
                        type: 'scatter',
                        coordinateSystem: 'geo',
                        zlevel: 1,
                        symbol: 'image://' + require('@/assets/images/06.png'),
                        symbolSize: _this.imageSize(),
                        label: {
                            normal: {
                                show: true,
                                fontSize: _this.fontSize(),
                                position: 'right',
                                textStyle: {
                                    fontSize: _this.fontSize(),
                                    fontFamily: 'Microsoft YaHei',
                                    color: '#fff',
                                    fontStyle: 'normal'
                                },
                                formatter: function() {
                                    //
                                    return '大洋洲'
                                }
                            }
                        },
                        data: [{name: '大洋洲', value: [49, 20]}]
                    }
                ]
            }
            _this.screen_width = window.screen.width
            _this.queryCustomInfo()
            _this.drawChinaMap()

        })

    },
    mounted() {
        // this.queryCustomInfo()
        // this.drawChinaMap()

        setTimeout(() => {

            if (this.dataInfo) {
                this.total = this.dataInfo.attackTotal
                this.loopholeAttackCount = this.dataInfo.visitTotal
                this.virusAttackCount = this.dataInfo.attackIpCount
                this.passedAttackCount = this.dataInfo.trappCount

            }
            if (this.dataAttack) {
                this.dataAttackBox = this.dataAttack
                this.queryCustomInfo()
                this.drawChinaMap()
            }
        }, 1000)

    },
    methods: {
        init(arry) {

            if (arry) {
                this.dataAttackBox = arry
                this.queryCustomInfo()
                this.drawChinaMap()
            }
        },
        imageSize() {
            if (this.screen_width < 2000) {
                return 40
            } else if ((2000 < this.screen_width) && (this.screen_width < 3000)) {
                return 75

            } else if ((3000 < this.screen_width) && (this.screen_width < 4000)) {
                return 100
            } else {
                return 125
            }
        },
        bubbleSize() {
            if (this.screen_width < 2000) {
                return 6
            } else if ((2000 < this.screen_width) && (this.screen_width < 3000)) {
                return 8

            } else if ((3000 < this.screen_width) && (this.screen_width < 4000)) {
                return 14
            } else {
                return 20
            }
        },
        fontSize() {
            if (this.screen_width < 2000) {
                return 12
            }
            if (2000 < this.screen_width && this.screen_width < 3000) {
                return 18
            }
            if (3000 < this.screen_width && this.screen_width < 4000) {
                return 24
            }
            if (4000 < this.screen_width && this.screen_width < 5000) {
                return 30
            }
            if (5000 < this.screen_width && this.screen_width < 6000) {
                return 36
            } else return 12
        },
        symbolBig() {
            if (this.screen_width < 2000) {
                return 10
            } else if (2000 < this.screen_width && this.screen_width < 3000) {
                return 18
            } else if (3000 < this.screen_width && this.screen_width < 4000) {
                return 24
            } else {
                return 30
            }
        },
        // 获取地球信息
        queryCustomInfo() {

            const $this = this
            // let arry = []
            let geoJson = geoJsonList
            let obj = {
                companyName: 'ss',
                region: 'china'
            }
            $this.region = obj.region
            if ($this.region != 'china') {
                let nameNum = geoJson.indexOf($this.region, provincesText)
                let name = provinces[nameNum]
                // $.get(context + '/static/component/scripts/echartsMap/provinceCity/' + name + '.json', function(geoJson) { /* 地图Json文件获取 */
                Echarts.registerMap(name, geoJson)
                /* 地图注册 */
                $this.attackMapOption.geo.map = name
                if ($this.region == '云南') {
                    $this.roamValue = true// 云南省地图允许缩放
                    $this.attackMapOption.geo.roam = $this.roamValue
                }
                // })
            } else {
                $this.roamValue = false// 中国地图不允许缩放
            }
        },
        computedPosition() {
            let continentPosition = []
            // let height = $('#mapEcharts').height()
            let height = this.$refs.mapEcharts.offsetHeight
            if (this.screen_width < 1601) {
                if (height < 400) {
                    continentPosition = [
                        {name: '非洲', value: [44, 50]},
                        {name: '北美洲', value: [44, 44]},
                        {name: '欧洲', value: [44, 38]},
                        {name: '亚洲', value: [44, 32]},
                        {name: '南美洲', value: [44, 26]},
                        {name: '大洋洲', value: [44, 20]}
                    ]
                } else if (height < 450) {
                    continentPosition = [
                        {name: '非洲', value: [45, 50]},
                        {name: '北美洲', value: [45, 44]},
                        {name: '欧洲', value: [45, 38]},
                        {name: '亚洲', value: [45, 32]},
                        {name: '南美洲', value: [45, 26]},
                        {name: '大洋洲', value: [45, 20]}
                    ]
                } else {
                    continentPosition = [
                        {name: '非洲', value: [49, 50]},
                        {name: '北美洲', value: [49, 44]},
                        {name: '欧洲', value: [49, 38]},
                        {name: '亚洲', value: [49, 32]},
                        {name: '南美洲', value: [49, 26]},
                        {name: '大洋洲', value: [49, 20]}
                    ]
                }
            } else if (this.screen_width >= 1601 && this.screen_width < 2000) {
                if (height < 520) {
                    continentPosition = [
                        {name: '非洲', value: [45.5, 50]},
                        {name: '北美洲', value: [45.5, 44]},
                        {name: '欧洲', value: [45.5, 38]},
                        {name: '亚洲', value: [45.5, 32]},
                        {name: '南美洲', value: [45.5, 26]},
                        {name: '大洋洲', value: [45.5, 20]}
                    ]
                } else if (height < 550) {
                    continentPosition = [
                        {name: '非洲', value: [47, 50]},
                        {name: '北美洲', value: [47, 44]},
                        {name: '欧洲', value: [47, 38]},
                        {name: '亚洲', value: [47, 32]},
                        {name: '南美洲', value: [47, 26]},
                        {name: '大洋洲', value: [47, 20]}
                    ]
                } else {
                    continentPosition = [
                        {name: '非洲', value: [49, 50]},
                        {name: '北美洲', value: [49, 44]},
                        {name: '欧洲', value: [49, 38]},
                        {name: '亚洲', value: [49, 32]},
                        {name: '南美洲', value: [49, 26]},
                        {name: '大洋洲', value: [49, 20]}
                    ]
                }
            } else {
                continentPosition = [
                    {name: '非洲', value: [49, 50]},
                    {name: '北美洲', value: [49, 44]},
                    {name: '欧洲', value: [49, 38]},
                    {name: '亚洲', value: [49, 32]},
                    {name: '南美洲', value: [49, 26]},
                    {name: '大洋洲', value: [49, 20]}
                ]
            }
            return continentPosition
        },
        queryAttackList() {

            const $this = this
            let arryBox = [
                {
                    action: '允许',
                    attackName: '漏洞',
                    criticalLevel: '高',
                    destAssetArea: '委电视台',
                    destArea: '郑州',
                    destCountry: '中国',
                    destIp: '222',
                    destIpIsOutIn: 0,
                    srcIpIsOutIn: 1,
                    destProvince: '河南',
                    occur_time: '2022-03-04',
                    srcAssetArea: '',
                    srcCity: '莱利斯塔德',
                    srcCountry: '美国',
                    scrIp: '193.142.146.299',
                    srcProvince: '弗莱福兰省'
                }
            ]
            let attackList = arryBox
            for (let i = 0; i < attackList.length; i++) {
                if (!attackList[i].occur_time) {
                    attackList[i].attcakTime = ''
                } else {
                    attackList[i].attcakTime = attackList[i].occur_time
                }
                let srcArea = ''
                // 源区域按照从小到大的顺序取值
                if (attackList[i].srcCountry == '中国') {
                    if (attackList[i].srcCity != '' && attackList[i].srcCity != undefined && attackList[i].srcCity != null) {
                        srcArea = attackList[i].srcCity
                    } else if (attackList[i].srcProvince != '' && attackList[i].srcProvince != undefined && attackList[i].srcProvince != null) {
                        srcArea = attackList[i].srcProvince
                    } else {
                        srcArea = '中国'
                    }
                }
                if (attackList[i].srcIpIsOutIn == 1 && attackList[i].srcCountry == '中国') {
                    attackList[i].srcArea = srcArea
                } else if (attackList[i].srcIpIsOutIn == 1 && attackList[i].srcCountry != '中国') {
                    attackList[i].srcArea = attackList[i].srcCountry
                } else {
                    attackList[i].srcArea = attackList[i].srcAssetArea
                }
                if (attackList[i].destIpIsOutIn == 1 && attackList[i].destCountry == '中国') {
                    attackList[i].destArea = attackList[i].destCity
                } else if (attackList[i].destIpIsOutIn == 1 && attackList[i].destCountry != '中国') {
                    attackList[i].destArea = attackList[i].destCountry
                } else {
                    attackList[i].destArea = attackList[i].destAssetArea
                }

                if (attackList[i].criticalLevel == '严重') {
                    attackList[i].levelColor = '#F73737'
                } else if (attackList[i].criticalLevel == '高' || attackList[i].criticalLevel == '高危') {
                    attackList[i].levelColor = '#D98629'
                } else if (attackList[i].criticalLevel == '中' || attackList[i].criticalLevel == '中危') {
                    attackList[i].levelColor = '#ffc601'
                } else if (attackList[i].criticalLevel == '低' || attackList[i].criticalLevel == '低危') {
                    attackList[i].levelColor = '#1dbdef'
                } else {
                    attackList[i].levelColor = '#8fc25b'
                }
            }
            $this.attackList = attackList
            $this.drawChinaMap()
        },
        drawChinaMap() {
            const $this = this
            $this.mapEcharts = Echarts.init(this.$refs.mapEcharts, null, { renderer: 'svg' })

            let option = $this.attackMapOption
            // let arryBox = [
            //     {
            //         action: '允许',
            //         attackName: '漏洞',
            //         criticalLevel: '高',
            //         destAssetArea: '委电视台',
            //         destArea: '',
            //         destCountry: '',
            //         destIp: '222',
            //         destIpIsOutIn: 0,
            //         srcIpIsOutIn: 1,
            //         destProvince: '河南',
            //         occur_time: '2022-03-04',
            //         srcAssetArea: '',
            //         srcCity: '郑州',
            //         srcCountry: '河南',
            //         scrIp: '193.142.146.299',
            //         srcProvince: '郑州'
            //     }
            // ]
            $this.attackList = $this.dataAttackBox
            // $this.attackList = arryBox
            let chinaPointLabel = {
                normal: {
                    show: false,
                    fontSize: this.fontSize(),
                    position: 'right',
                    formatter: function(param) {
                        if (sevenContinents.indexOf(param.name) > 0) {
                            return ''
                        } else {
                            return param.name
                        }
                    },
                    textStyle: {
                        fontSize: this.fontSize(),
                        fontFamily: 'Microsoft YaHei',
                        color: '#fff',
                        fontStyle: 'normal'
                    }
                }
            }
            let continentPosition = $this.computedPosition()
            for (let i = 0, len = continentPosition.length; i < len ; i++) {
                $this.geoCoord[continentPosition[i].name] = continentPosition[i].value
            }
            if ($this.region != 'china') {
                let mapData = []
                let values = []
                for (let i = 0; i < $this.attackProvinceInfo.length; i++) {
                    let key = $this.attackProvinceInfo[i].key
                    let value = $this.attackProvinceInfo[i].count
                    if (key != '' && typeof key != 'undefined' && key != null) {
                        $this.geoCoord[key] = [$this.geoCoord[key][0], $this.geoCoord[key][1]]
                        $this.geoCoord[key].push(value)
                        mapData.push({
                            name: key,
                            value: $this.geoCoord[key]
                        })
                        values.push(value)
                    }
                }
                $this.maxAttackCount = Math.max.apply(null, values)
                option.series[2].data = mapData
                $this.mapEcharts.on('georoam', function() {
                    //
                    if ($this.roamValue == true) {
                        let zoom = $this.mapEcharts.getOption().geo[0].zoom
                        option.geo.zoom = zoom
                    }
                })
                $this.mapEcharts.setOption(option)
            } else {

                let attack_dest_cities = []
                let attack_src_cities = []
                let attack_line_data = []
                let worldCoord = $this.worldMapJson
                let geo = $this.geoCoord
                // 修改地图源区域和目的区域位置获取
                for (let i = 0; i < $this.attackList.length; i++) {
                    let fromName = ''
                    let toName = ''
                    if (this.attackList[i].srcIpIsOutIn == 1 && this.attackList[i].srcCountry == '中国') {
                        if (this.attackList[i].srcCity != '' && this.attackList[i].srcCity != undefined && this.attackList[i].srcCity != null) {
                            fromName = this.attackList[i].srcCity
                        } else if (this.attackList[i].srcProvince != '' && this.attackList[i].srcProvince != undefined && this.attackList[i].srcProvince != null) {
                            fromName = this.attackList[i].srcProvince
                        }
                    } else if (this.attackList[i].srcIpIsOutIn == 1 && this.attackList[i].srcCountry != '中国') {
                        fromName = this.attackList[i].srcCountry
                    } else {
                        fromName = this.attackList[i].srcCity || this.attackList[i].srcProvince || this.attackList[i].srcAssetArea
                    }
                    if (this.attackList[i].destIpIsOutIn == 1 && this.attackList[i].destCountry == '中国') {
                        toName = this.attackList[i].destCity
                    } else if (this.attackList[i].destIpIsOutIn == 1 && this.attackList[i].destCountry != '中国') {
                        toName = this.attackList[i].destCountry
                    } else {
                        toName = this.attackList[i].destCity || this.attackList[i].destProvince || this.attackList[i].destAssetArea
                    }
                    let fromCoord = geo[fromName]
                    let toCoord = geo[toName]
                    if (this.region == 'china' && worldCoord[fromName] != undefined) {
                        fromName = this.countryMap[fromName] || this.foreign
                        fromCoord = geo[fromName]
                    }
                    if (this.region == 'china' && worldCoord[toName] != undefined) { // && worldCoord[fromName] != undefined---lyy
                        toName = this.countryMap[toName] || this.foreign
                        toCoord = geo[toName]
                    }
                    if (fromCoord != undefined && toCoord != undefined) {
                        let src_value = [fromCoord[0], fromCoord[1], $this.symbolBig()]
                        let dest_value = [toCoord[0], toCoord[1], $this.symbolBig()]
                        let attack_src_data = {name: fromName, value: src_value, srcCountry: fromName}
                        let attack_dest_data = {name: toName, value: dest_value}
                        let line = {
                            fromName: fromName,
                            toName: toName,
                            coords: [fromCoord, toCoord]
                        }
                        attack_src_cities.push(attack_src_data)
                        attack_dest_cities.push(attack_dest_data)
                        attack_line_data.push(line)
                    }
                }

                // if (attack_line_data.length === 0) {
                //     let fromCoord1 = [45.5, 32]
                //     let toCoord1 = [113.626659, 34.755449]
                //     let line1 = {
                //         fromName: '河南',
                //         toName: '亚洲',
                //         coords: [fromCoord1, toCoord1]
                //     }
                //     attack_line_data.push(line1)
                // }
                // //
                option.series[1].data = attack_line_data
                option.series[2].type = 'effectScatter'
                option.series[2].symbol = 'circle'
                option.series[2].label = chinaPointLabel
                option.series[2].symbolSize = $this.bubbleSize()
                option.series[2].data = attack_dest_cities
                option.series[3].type = 'effectScatter'
                option.series[3].symbol = 'circle'
                option.series[3].label = chinaPointLabel
                option.series[3].symbolSize = $this.bubbleSize()
                option.series[3].data = attack_src_cities
                option.series[5].data = [continentPosition[0]]
                option.series[6].data = [continentPosition[1]]
                option.series[7].data = [continentPosition[2]]
                option.series[8].data = [continentPosition[3]]
                option.series[9].data = [continentPosition[4]]
                option.series[10].data = [continentPosition[5]]
                $this.mapEcharts.setOption(option)
            }
        }
    }
}
</script>
<style >
@import "../../assets/styles/screen.css";
</style>
