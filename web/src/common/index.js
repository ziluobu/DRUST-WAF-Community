export function escape2Html(str) {                            // 转义字符转换普通字符的方法
    var arrEntities = {'lt': '<', 'gt': '>', 'nbsp': ' ', 'amp': '&', 'quot': '"'}
    return str.replace(/&(lt|gt|nbsp|amp|quot);/ig, function(all, t) { return arrEntities[t] })
}
export function replaceStr(value) {
    var result = ''
    if (value == '@contains') {
        result = '包含'
    } else if (value == '!@contains') {
        result = '不包含'
    } else if (value == 'containsWord') {
        result = '包含(词)'
    } else if (value == '!containsWord') {
        result = '不包含(词)'
    } else if (value == '@eq') {
        result = '等于'
    } else if (value == '!@eq') {
        result = '不等于'
    } else if (value == '@rx') {
        result = '正则'
    }
    return value.replace(value, result)
}
export default {
    escape2Html,
    replaceStr
}