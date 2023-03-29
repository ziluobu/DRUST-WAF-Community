const path = require('path')
const fs = require('fs')

function getFolder(path) {
    let components = [path]
    const files = fs.readdirSync(path)
    files.forEach(function(item) {
        let stat = fs.lstatSync(path + '/' + item)
        if (stat.isDirectory() === true && item != 'components') {
            components.push.apply(components, getFolder(path + '/' + item))
        }
    })
    return components
}

module.exports = {
    description: '创建标准模块（包含列表页&详情页）',
    prompts: [
        {
            type: 'list',
            name: 'path',
            message: '请选择模块创建目录',
            choices: getFolder('src/views')
        },
        {
            type: 'input',
            name: 'name',
            message: '请输入模块名',
            validate: v => {
                if (!v || v.trim === '') {
                    return '模块名不能为空'
                } else {
                    return true
                }
            }
        },
        {
            type: 'input',
            name: 'cname',
            message: '请输入模块中文名称',
            default: '默认模块'
        },
        {
            type: 'confirm',
            name: 'isMock',
            message: '是否生成 Mock'
        },
        {
            type: 'checkbox',
            name: 'mockMode',
            message: 'Mock 模式',
            choices: ['mockjs', 'mock-server'],
            when: answer => answer.isMock,
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
        let relativePath = path.relative('src/views', data.path)
        const actions = [
            {
                type: 'add',
                path: `${data.path}/{{snakeCase name}}/list.vue`,
                templateFile: 'plop-templates/module/list.hbs',
                data: {
                    relativePath,
                    componentName: `${relativePath} ${data.name} list`,
                    moduleName: data.name
                }
            },
            {
                type: 'add',
                path: `${data.path}/{{snakeCase name}}/detail.vue`,
                templateFile: 'plop-templates/module/detail.hbs',
                data: {
                    componentName: `${relativePath} ${data.name} detail`
                }
            },
            {
                type: 'add',
                path: `${data.path}/{{snakeCase name}}/components/DetailForm/index.vue`,
                templateFile: 'plop-templates/module/form.hbs',
                data: {
                    relativePath,
                    moduleName: data.name
                }
            },
            {
                type: 'add',
                path: `${data.path}/{{snakeCase name}}/components/FormDialog/index.vue`,
                templateFile: 'plop-templates/module/dialog.hbs'
            }
        ]
        if (data.isMock) {
            let pathArr = path.relative('src/views', data.path).split('\\')
            if (data.mockMode.includes('mockjs')) {
                actions.push({
                    type: 'add',
                    path: pathArr.join('/') == '' ? 'src/mock/modules/{{snakeCase name}}.js' : `src/mock/modules/${pathArr.join('.')}.{{snakeCase name}}.js`,
                    templateFile: 'plop-templates/module/mock.hbs',
                    data: {
                        relativePath,
                        moduleName: data.name
                    }
                })
            }
            if (data.mockMode.includes('mock-server')) {
                actions.push({
                    type: 'add',
                    path: pathArr.join('/') == '' ? 'src/mock/server-modules/{{snakeCase name}}.js' : `src/mock/server-modules/${pathArr.join('.')}.{{snakeCase name}}.js`,
                    templateFile: 'plop-templates/module/mock-server.hbs',
                    data: {
                        relativePath,
                        moduleName: data.name
                    }
                })
            }
        }
        return actions
    }
}
