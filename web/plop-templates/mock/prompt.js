const path = require('path')
const fs = require('fs')

function getFolder(path) {
    let components = []
    const files = fs.readdirSync(path)
    files.forEach(function(item) {
        let stat = fs.lstatSync(path + '/' + item)
        if (stat.isDirectory() === true && item != 'components') {
            components.push(path + '/' + item)
            components.push.apply(components, getFolder(path + '/' + item))
        }
    })
    return components
}

module.exports = {
    description: '创建标准模块 Mock',
    prompts: [
        {
            type: 'list',
            name: 'path',
            message: '请选择模块目录',
            choices: getFolder('src/views')
        },
        {
            type: 'checkbox',
            name: 'mode',
            message: 'Mock 模式',
            choices: ['mockjs', 'mock-server'],
            validate: v => {
                if (v.length == 0) {
                    return '最少选择一个模式'
                } else {
                    return true
                }
            }
        }
    ],
    actions: data => {
        let pathArr = path.relative('src/views', data.path).split('\\')
        let moduleName = pathArr.pop()
        let relativePath = pathArr.join('/')
        const actions = []
        if (data.mode.includes('mockjs')) {
            actions.push({
                type: 'add',
                path: pathArr.length == 0 ? 'src/mock/modules/{{moduleName}}.js' : `src/mock/modules/${pathArr.join('.')}.{{moduleName}}.js`,
                templateFile: 'plop-templates/mock/mock.hbs',
                data: {
                    relativePath,
                    moduleName
                }
            })
        }
        if (data.mode.includes('mock-server')) {
            actions.push({
                type: 'add',
                path: pathArr.length == 0 ? 'src/mock/server-modules/{{moduleName}}.js' : `src/mock/server-modules/${pathArr.join('.')}.{{moduleName}}.js`,
                templateFile: 'plop-templates/mock/mock-server.hbs',
                data: {
                    relativePath,
                    moduleName
                }
            })
        }
        return actions
    }
}
