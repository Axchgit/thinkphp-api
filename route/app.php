<?php
/*
 * @Author: xch
 * @Date: 2020-08-15 11:15:58
 * @LastEditTime: 2020-11-09 19:40:48
 * @LastEditors: 罗曼
 * @Description: 
 * @FilePath: \testd:\wamp64\www\thinkphp-api\route\app.php
 */
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2018 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
use think\facade\Route;

Route::get('think', function () {
    return 'hello,ThinkPHP6!';
});

Route::get('hello/:name', 'index/hello');

/****测试*****/
Route::get('test/testthree', 'Test/testThree')->middleware('checkrequest', 6)->allowCrossDomain();
Route::get('test/testone', 'Test/testOne')->middleware('checkrequest', 6)->allowCrossDomain();

Route::get('test/testfive', 'Test/testFive')->middleware('checkrequest', 6)->allowCrossDomain();

/****登录模块*****/
Route::group('login', function () {
    //发送验证码请求
    Route::get('/', 'sendAdminCode')->allowCrossDomain();
    //验证登录请求
    Route::rule('checkadminlogin', 'checkAdminLogin')->allowCrossDomain();
    //获取管理员信息请求
    Route::rule('selectadmininfo', 'selectAdminInfo')->middleware('checkrequest', 1)->allowCrossDomain();
    //员工登录
    Route::rule('checkPersonLogin', 'checkPersonLogin')->allowCrossDomain();
    Route::rule('selectPersonInfo', 'selectPersonInfo')->middleware('checkrequest', 8)->allowCrossDomain();
})->completeMatch()->prefix('Login/');
/****员工*****/
// Route::resource('employee','Employee');
/*******管理员 */
Route::group('admin', function () {
    
    Route::post('importExcel', 'importExcel')->middleware('checkrequest', 3)->allowCrossDomain();
    Route::get('selectPersonAccount', 'selectPersonAccount')->middleware('checkrequest', 1)->allowCrossDomain();
    Route::get('updatePersonAccount', 'updatePersonAccount')->middleware('checkrequest', 1)->allowCrossDomain();
    Route::get('deletePersonAccount', 'deletePersonAccount')->middleware('checkrequest', 1)->allowCrossDomain();
    //一二级管理员
    Route::get('viewAllPerson', 'viewAllPerson')->middleware('checkrequest', 4)->allowCrossDomain();  
    Route::get('viewApply', 'viewApply')->middleware('checkrequest', 4)->allowCrossDomain();  
    Route::post('reviewApply', 'reviewApply')->middleware('checkrequest', 4)->allowCrossDomain();  
    Route::get('viewRecruit', 'viewRecruit')->middleware('checkrequest', 4)->allowCrossDomain();
    Route::get('viewAllPersonAccount', 'viewAllPersonAccount')->middleware('checkrequest', 4)->allowCrossDomain();
    // Route::post('selectGoods', 'selectGoods')->middleware('checkrequest', 1)->allowCrossDomain();
})->completeMatch()->prefix('Admin/');


/*******人员 */
Route::group('person', function () {
    Route::post('submitApplicatioin', 'submitApplicatioin')->middleware('checkrequest', 8)->allowCrossDomain();
    Route::get('getPartyBranch', 'getPartyBranch')->middleware('checkrequest', 8)->allowCrossDomain();
    Route::get('getIsOneStep', 'getIsOneStep')->middleware('checkrequest', 8)->allowCrossDomain();
    Route::get('getApplyStep', 'getApplyStep')->middleware('checkrequest', 8)->allowCrossDomain();
    
    
    // Route::post('selectGoods', 'selectGoods')->middleware('checkrequest', 1)->allowCrossDomain();
})->completeMatch()->prefix('Person/');




Route::group('employee', function () {
    //获取员工所有信息
    Route::get('selectall', 'selectAll')->middleware('checkrequest', 1)->allowCrossDomain();
    //根据工号/姓名获取信息,工号为8位数字
    Route::get('selectByInfo', 'selectByInfo')->middleware('checkrequest', 1)->allowCrossDomain();
    //根据权限获取信息
    Route::get('selectByRole', 'selectByRole')->middleware('checkrequest', 1)->allowCrossDomain();
    Route::get('selectAcAll', 'selectAcAll')->middleware('checkrequest', 1)->allowCrossDomain();
    Route::post('sendRecoverCode', 'sendRecoverCode')->allowCrossDomain();
    Route::post('sendActivateCode', 'sendActivateCode')->allowCrossDomain();
    Route::post('checkRecover', 'checkRecover')->allowCrossDomain();
    Route::post('updateAcPW', 'updateAcPW')->allowCrossDomain();
    Route::post('createEmpAc', 'createEmpAc')->allowCrossDomain();

    /***********员工业绩******/
    Route::post('submitPerformanc', 'submitPerformanc')->middleware('checkrequest', 5)->allowCrossDomain();
    Route::get('selectPerformanceByUuid', 'selectPerformanceByUuid')->middleware('checkrequest', 5)->allowCrossDomain();
    Route::get('selectPerformanceGoodsByUuid', 'selectPerformanceGoodsByUuid')->middleware('checkrequest', 5)->allowCrossDomain();
    Route::post('deletePerformanceByUuuid', 'deletePerformanceByUuuid')->middleware('checkrequest', 5)->allowCrossDomain();
})->completeMatch()->prefix('Employee/');


/**
 * 商品信息
 */

Route::group('goods', function () {
    Route::post('uploadExcel', 'uploadExcel')->middleware('checkrequest', 1)->allowCrossDomain();
    Route::post('selectGoods', 'selectGoods')->middleware('checkrequest', 1)->allowCrossDomain();
})->completeMatch()->prefix('Goods/');


