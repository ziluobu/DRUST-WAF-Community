module.exports = function(plop) {
    plop.setWelcomeMessage('请选择需要创建的模式：')
    plop.setGenerator('module', require('./plop-templates/module/prompt'))
    plop.setGenerator('page', require('./plop-templates/page/prompt'))
    plop.setGenerator('component', require('./plop-templates/component/prompt'))
    plop.setGenerator('store', require('./plop-templates/store/prompt'))
    plop.setGenerator('mock', require('./plop-templates/mock/prompt'))
}
