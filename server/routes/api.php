<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

use App\Http\Controllers\Api\PublicController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\AdminController;
use App\Http\Controllers\Api\ConfigController;
use App\Http\Controllers\Api\MenuController;
use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\Api\LoginlogController;
use App\Http\Controllers\Api\OpertlogController;
use App\Http\Controllers\Api\GroupController;
use App\Http\Controllers\Api\IpAllowController;
use App\Http\Controllers\Api\IpBlackController;
use App\Http\Controllers\Api\WebController;
use App\Http\Controllers\Api\AssetsController;
use App\Http\Controllers\Api\ProtectLogController;
use App\Http\Controllers\Api\RulesController;
use App\Http\Controllers\Api\RuleTypeController;
use App\Http\Controllers\Api\RulesGlobalController;
use App\Http\Controllers\Api\RuleSysController;
use App\Http\Controllers\Api\RulesWhiteController;
use App\Http\Controllers\Api\WordController;
use App\Http\Controllers\Api\IndexController;
use App\Http\Controllers\Api\ScreenController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\LocalApiController;

Route::post('log', [LocalApiController::class, 'index']);
Route::post('black', [LocalApiController::class, 'black']);
Route::put('rule', [LocalApiController::class, 'rule']);
Route::post('hfish', [LocalApiController::class, 'hfish']);
// Route::post('signTest', [LocalApiController::class, 'signTest']);

// Route::middleware(['api.sign', 'api.xss'])->group(function () {
Route::middleware(['api.xss', 'throttle:60,1'])->group(function () {
    // 验证码
    Route::post('captcha', [PublicController::class, 'captcha'])->name('captcha');
    // Route::any('word', [WordController::class, 'index'])->name('word');
    // Route::post('test', [WordController::class, 'test'])->name('test');
    // 登录
    Route::post('login', [AuthController::class, 'login'])->name('login');
    Route::post('getConfig', [PublicController::class, 'getConfig'])->name('getconfig');
    // Route::post('mail', [PublicController::class, 'mail'])->name('mail');
    /*Route::get('mail', function () {
        return new App\Mail\AlarmNotificationMail();
    });*/
    // 需要登录
    Route::get('report/reportZdyDown/{report}', [ReportController::class, 'reportZdyDown'])->name('report.reportzdydown');
    Route::get('report/reportMonthDown/{report}', [ReportController::class, 'reportMonthDown'])->name('report.reportmonthdown');

    Route::middleware('jwt')->group(function () {
        Route::post('home', [PublicController::class, 'home'])->name('home');

        Route::post('index/visitAttackOverView', [IndexController::class, 'visitAttackOverView'])->name('index.visitattackoverview');
        Route::post('index/wafWarnPolicyRank', [IndexController::class, 'wafWarnPolicyRank'])->name('index.wafwarnpolicyrank');


        Route::post('screen/visitAttackOverView', [ScreenController::class, 'visitAttackOverView'])->name('screen.visitattackoverview');
        Route::post('screen/realTimeAttackLog', [ScreenController::class, 'realTimeAttackLog'])->name('screen.realtimeattacklog');
        Route::post('screen/wafWarnPolicyRank', [ScreenController::class, 'wafWarnPolicyRank'])->name('screen.wafwarnpolicyrank');

        // 路由接口
        Route::post('getRouters', [PublicController::class, 'getRouters'])->name('getrouters');
        // 注销
        Route::post('logout', [AuthController::class, 'logout'])->name('logout');
        Route::post('updatePwd', [AuthController::class, 'updatePwd'])->name('updatepwd');
        // 刷新缓存
        Route::post('refreshCache', [PublicController::class, 'refreshCache'])->name('refreshcache');
        Route::post('userInfo', [PublicController::class, 'userInfo'])->name('userinfo');

        // 权限判断
        Route::middleware('permit')->group(function () {
            // 操作日志

            Route::middleware('api.operate')->group(function () {

                Route::get('loginlog', [LoginlogController::class, 'index'])->name('loginlog');
                // Route::post('loginlog/export', [LoginlogController::class, 'export'])->name('loginlog.export');

                Route::get('opertlog', [OpertlogController::class, 'index'])->name('opertlog');
                // Route::post('opertlog/export', [OpertlogController::class, 'export'])->name('opertlog.export');
                Route::get('web', [WebController::class, 'index'])->name('web');
                Route::get('web/{web}', [WebController::class, 'show'])->name('web.show');

                Route::get('group', [GroupController::class, 'index'])->name('group');
                Route::get('group/{group}', [GroupController::class, 'show'])->name('group.show');

                Route::get('report/getReportZdyTask', [ReportController::class, 'getReportZdyTask'])->name('report.getreportzdytask');
                Route::post('report/addReportZdyTask', [ReportController::class, 'addReportZdyTask'])->name('report.addreportzdytask');
                Route::get('report/contractList', [ReportController::class, 'contractList'])->name('report.contractlist');

                //防御日志
                Route::get('protectLog', [ProtectLogController::class, 'index'])->name('protectlog');
                Route::get('ruleType', [RuleTypeController::class, 'index'])->name('ruletype');
                Route::get('webRules', [RulesController::class, 'index'])->name('webrules');
                Route::get('webRules/{Rules}', [RulesController::class, 'show'])->name('webrules.show');
                Route::get('globalRules', [RulesGlobalController::class, 'index'])->name('globalrules');
                Route::get('globalRules/{Rules}', [RulesGlobalController::class, 'show'])->name('globalrules.show');
                Route::get('sysRules', [RuleSysController::class, 'index'])->name('sysrules');
                Route::get('sysRules/{Rules}', [RuleSysController::class, 'show'])->name('sysrules.show');

                Route::post('web/searchList', [WebController::class, 'searchList'])->name('web.searchlist');
                Route::post('sysRules/searchList', [RuleSysController::class, 'searchList'])->name('sysrules.searchlist');
                Route::post('ruleType/searchList', [RuleTypeController::class, 'searchList'])->name('ruletype.searchlist');
                Route::post('group/searchList', [GroupController::class, 'searchList'])->name('group.searchlist');
                Route::get('assets', [AssetsController::class, 'index'])->name('assets');
                Route::get('assets/{assets}', [AssetsController::class, 'show'])->name('assets.show');
                Route::get('whiteRules', [RulesWhiteController::class, 'index'])->name('whiterules');
                Route::get('whiteRules/{Rules}', [RulesWhiteController::class, 'show'])->name('whiterules.show');

                //普通用户不能操作
                Route::middleware('api.admin')->group(function () {
                    // 配置模块
                    Route::get('config', [ConfigController::class, 'index'])->name('config');
                    Route::get('config/{config}', [ConfigController::class, 'show'])->name('config.show');
                    Route::put('config/{config}', [ConfigController::class, 'update'])->name('config.update');
                    Route::post('config', [ConfigController::class, 'store'])->name('config.store');
                    Route::delete('config/{id}', [ConfigController::class, 'destroy'])->name('config.destroy');
                    Route::post('config/upload', [ConfigController::class, 'upload'])->name('config.upload');
                    // 超级管理员权限
                    Route::middleware('api.super')->group(function () {
                        // 管理员模块
                        Route::get('manage', [AdminController::class, 'index'])->name('manage');
                        Route::post('manage', [AdminController::class, 'store'])->name('manage.store');
                        Route::put('manage/changeStatus', [AdminController::class, 'changeStatus'])->name('manage.changestatus');
                        Route::put('manage/resetPwd', [AdminController::class, 'resetPwd'])->name('manage.resetpwd');
                        Route::get('manage/{manage}', [AdminController::class, 'show'])->name('manage.show');
                        Route::put('manage/{manage}', [AdminController::class, 'update'])->name('manage.update');
                        Route::delete('manage/{manage}', [AdminController::class, 'destroy'])->name('manage.destroy');
                        // 菜单模块
                        Route::get('menu', [MenuController::class, 'index'])->name('menu');
                        Route::post('menu', [MenuController::class, 'store'])->name('menu.store');
                        Route::post('menu/searchList', [MenuController::class, 'searchList'])->name('menu.searchlist');
                        Route::get('menu/{menu}', [MenuController::class, 'show'])->name('menu.show');
                        Route::put('menu/{menu}', [MenuController::class, 'update'])->name('menu.update');
                        Route::delete('menu/{menu}', [MenuController::class, 'destroy'])->name('menu.destroy');
                        // 角色模块
                        Route::get('role', [RoleController::class, 'index'])->name('role');
                        Route::post('role', [RoleController::class, 'store'])->name('role.store');
                        Route::post('role/searchList', [RoleController::class, 'searchList'])->name('role.searchlist');
                        Route::post('role/menuTree', [RoleController::class, 'menuTree'])->name('role.menutree');
                        Route::get('role/{role}', [RoleController::class, 'show'])->name('role.show');
                        Route::put('role/{role}', [RoleController::class, 'update'])->name('role.update');
                        Route::delete('role/{role}', [RoleController::class, 'destroy'])->name('role.destroy');
                        // 日志模块
                        Route::delete('loginlog/trash', [LoginlogController::class, 'trash'])->name('loginlog.trash');
                        Route::delete('loginlog/{id}', [LoginlogController::class, 'destroy'])->name('loginlog.destroy');

                        Route::delete('opertlog/trash', [OpertlogController::class, 'trash'])->name('opertlog.trash');
                        Route::delete('opertlog/{id}', [OpertlogController::class, 'destroy'])->name('opertlog.destroy');

                        Route::delete('report/zdydestroy/{zdyReport}', [ReportController::class, 'zdydestroy'])->name('report.zdydestroy');
                        Route::delete('report/monthdestroy/{monthReport}', [ReportController::class, 'monthdestroy'])->name('report.monthdestroy');

                    });

                    // 黑名单模块
                    Route::get('ipblack', [IpBlackController::class, 'index'])->name('ipblack');
                    Route::delete('ipblack/{id}', [IpBlackController::class, 'destroy'])->name('ipblack.destroy');
                    Route::get('ipblack/{ipblack}', [IpBlackController::class, 'show'])->name('ipblack.show');
                    Route::put('ipblack/{ipblack}', [IpBlackController::class, 'update'])->name('ipblack.update');
                    Route::post('ipblack', [IpBlackController::class, 'store'])->name('ipblack.store');
                    Route::post('ipblack/import', [IpBlackController::class, 'import'])->name('ipblack.import');
                    // Route::post('ipblack/syncConfig', [IpBlackController::class, 'syncConfig'])->name('ipblack.syncconfig');
                    // 白名单
                    Route::get('ipallow', [IpAllowController::class, 'index'])->name('ipallow');
                    Route::post('ipallow', [IpAllowController::class, 'store'])->name('ipallow.store');
                    // Route::post('ipallow/syncConfig', [IpAllowController::class, 'syncConfig'])->name('ipallow.syncconfig');
                    Route::get('ipallow/{ipallow}', [IpAllowController::class, 'show'])->name('ipallow.show');
                    Route::delete('ipallow/{ipallow}', [IpAllowController::class, 'destroy'])->name('ipallow.destroy');
                    Route::put('ipallow/{ipallow}', [IpAllowController::class, 'update'])->name('ipallow.update');
                    //网站管理
                    Route::post('web', [WebController::class, 'store'])->name('web.store');
                    Route::delete('web/{web}', [WebController::class, 'destroy'])->name('web.destroy');
                    Route::put('web/{web}', [WebController::class, 'update'])->name('web.update');
                    Route::post('web/upload', [WebController::class, 'upload'])->name('web.upload');
                    Route::post('web/syncConfig', [WebController::class, 'syncConfig'])->name('web.syncconfig');
                    //单位管理
                    Route::delete('group/{group}', [GroupController::class, 'destroy'])->name('group.destroy');
                    Route::put('group/{group}', [GroupController::class, 'update'])->name('group.update');
                    Route::post('group', [GroupController::class, 'store'])->name('group.store');
                    //资产管理
                    Route::delete('assets/{assets}', [AssetsController::class, 'destroy'])->name('assets.destroy');
                    Route::put('assets/{assets}', [AssetsController::class, 'update'])->name('assets.update');
                    Route::post('assets', [AssetsController::class, 'store'])->name('assets.store');

                    // Route::get('protectLog/{Logs}', [ProtectLogController::class, 'show'])->name('protectlog.show');
                    //规则分类
                    Route::post('ruleType', [RuleTypeController::class, 'store'])->name('ruletype.store');
                    Route::delete('ruleType/{id}', [RuleTypeController::class, 'destroy'])->name('ruletype.destroy');
                    Route::put('ruleType/{ruleType}', [RuleTypeController::class, 'update'])->name('ruletype.update');
                    //网站规则管理
                    Route::put('webRules/changeStatus', [RulesController::class, 'changeStatus'])->name('webrules.changestatus');
                    Route::post('webRules', [RulesController::class, 'store'])->name('webrules.store');
                    Route::post('webRules/syncConfig', [RulesController::class, 'syncConfig'])->name('webrules.syncconfig');
                    Route::put('webRules/{Rules}', [RulesController::class, 'update'])->name('webrules.update');
                    Route::delete('webRules/{id}', [RulesController::class, 'destroy'])->name('webrules.destroy');

                    //全局规则管理
                    Route::put('globalRules/changeStatus', [RulesGlobalController::class, 'changeStatus'])->name('globalrules.changestatus');
                    Route::post('globalRules', [RulesGlobalController::class, 'store'])->name('globalrules.store');
                    Route::post('globalRules/syncConfig', [RulesGlobalController::class, 'syncConfig'])->name('globalrules.syncconfig');
                    Route::put('globalRules/{Rules}', [RulesGlobalController::class, 'update'])->name('globalrules.update');
                    Route::delete('globalRules/{id}', [RulesGlobalController::class, 'destroy'])->name('globalrules.destroy');

                    //系统规则管理
                    Route::put('sysRules/{Rules}', [RuleSysController::class, 'update'])->name('sysrules.update');
                    Route::post('sysRules/syncConfig', [RuleSysController::class, 'syncConfig'])->name('sysrules.syncconfig');
                    //系统白名单管理

                    Route::put('whiteRules/{Rules}', [RulesWhiteController::class, 'update'])->name('whiterules.update');
                    Route::post('whiteRules', [RulesWhiteController::class, 'store'])->name('whiterules.store');
                    Route::post('whiteRules/syncConfig', [RulesWhiteController::class, 'syncConfig'])->name('whiterules.syncconfig');
                    Route::delete('whiteRules/{id}', [RulesWhiteController::class, 'destroy'])->name('whiterules.destroy');

                });
            });

        });
    });
});

