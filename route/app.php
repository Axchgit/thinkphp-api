<?php
/*
 * @Author: xch
 * @Date: 2020-08-15 11:15:58
 * @LastEditTime: 2020-12-13 16:47:25
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
/*******人员 */
Route::group('index', function () {
    Route::get('getProfile', 'getProfile')->middleware('checkrequest', 9)->allowCrossDomain();
    Route::get('getCountUnreadBulletin', 'getCountUnreadBulletin')->middleware('checkrequest', 9)->allowCrossDomain();
    Route::get('sendEmailCode', 'sendEmailCode')->middleware('checkrequest', 9)->allowCrossDomain();

    


    

    
    // Route::post('selectGoods', 'selectGoods')->middleware('checkrequest', 1)->allowCrossDomain();
})->completeMatch()->prefix('Index/');

/*******管理员 */
Route::group('admin', function () {
    
    Route::post('importExcel', 'importExcel')->middleware('checkrequest', 3)->allowCrossDomain();
    Route::post('importMaterialExcel', 'importMaterialExcel')->middleware('checkrequest', 4)->allowCrossDomain();
    Route::get('viewMaterial', 'viewMaterial')->middleware('checkrequest', 4)->allowCrossDomain();      
    Route::post('editMaterial', 'editMaterial')->middleware('checkrequest', 3)->allowCrossDomain();      
    
    Route::get('selectPersonAccount', 'selectPersonAccount')->middleware('checkrequest', 4)->allowCrossDomain();
    Route::get('updatePersonAccount', 'updatePersonAccount')->middleware('checkrequest', 4)->allowCrossDomain();
    Route::get('deletePersonAccount', 'deletePersonAccount')->middleware('checkrequest', 4)->allowCrossDomain();
    Route::get('updatePerson', 'updatePerson')->middleware('checkrequest', 4)->allowCrossDomain();
    Route::get('deletePerson', 'deletePerson')->middleware('checkrequest', 4)->allowCrossDomain();
    //一二级管理员
    Route::get('viewAllPerson', 'viewAllPerson')->middleware('checkrequest', 4)->allowCrossDomain();  
    Route::get('viewApply', 'viewApply')->middleware('checkrequest', 4)->allowCrossDomain();  
    Route::post('reviewApply', 'reviewApply')->middleware('checkrequest', 4)->allowCrossDomain();  
    Route::get('viewRecruit', 'viewRecruit')->middleware('checkrequest', 4)->allowCrossDomain();
    Route::rule('deleteRecruit', 'deleteRecruit')->middleware('checkrequest', 4)->allowCrossDomain();


    
    Route::get('viewAllPersonAccount', 'viewAllPersonAccount')->middleware('checkrequest', 4)->allowCrossDomain();
    // Route::post('selectGoods', 'selectGoods')->middleware('checkrequest', 1)->allowCrossDomain();
    //首页charts数据
    Route::get('getCount', 'getCount')->middleware('checkrequest', 4)->allowCrossDomain();
    Route::get('getLineCharts', 'getLineCharts')->middleware('checkrequest', 4)->allowCrossDomain();
    Route::get('getCountPersonSex', 'getCountPersonSex')->middleware('checkrequest', 4)->allowCrossDomain();
    Route::get('getCountPersonNation', 'getCountPersonNation')->middleware('checkrequest', 4)->allowCrossDomain();
    //发展党员大数据
    Route::get('getCountRecruitNation', 'getCountRecruitNation')->middleware('checkrequest', 4)->allowCrossDomain();
    Route::get('getCountRecruitSex', 'getCountRecruitSex')->middleware('checkrequest', 4)->allowCrossDomain();
    Route::get('getCountRecruitFaculty', 'getCountRecruitFaculty')->middleware('checkrequest', 4)->allowCrossDomain();
    Route::get('getCountRecruitPost', 'getCountRecruitPost')->middleware('checkrequest', 4)->allowCrossDomain();
    Route::get('getCountRecruitStage', 'getCountRecruitStage')->middleware('checkrequest', 4)->allowCrossDomain();
    Route::get('getCountRecruitPoliticalStatus', 'getCountRecruitPoliticalStatus')->middleware('checkrequest', 4)->allowCrossDomain();
    Route::get('getCountRecruitGrowthStage', 'getCountRecruitGrowthStage')->middleware('checkrequest', 4)->allowCrossDomain();


    
    Route::get('viewTransferApply', 'viewTransferApply')->middleware('checkrequest', 4)->allowCrossDomain();
    Route::post('reviewTransferApply', 'reviewTransferApply')->middleware('checkrequest', 4)->allowCrossDomain();

    Route::rule('deleteTransfer', 'deleteTransfer')->middleware('checkrequest', 4)->allowCrossDomain();

    //发送通告
    Route::post('sendBulletin', 'sendBulletin')->middleware('checkrequest', 4)->allowCrossDomain();
    //获取文件
    Route::post('viewAllFile', 'viewAllFile')->middleware('checkrequest', 4)->allowCrossDomain();
    Route::post('uploadPublicFile', 'uploadPublicFile')->middleware('checkrequest', 4)->allowCrossDomain();
    Route::get('deleteFile', 'deleteFile')->middleware('checkrequest', 4)->allowCrossDomain();

    
    
    
    
})->completeMatch()->prefix('Admin/');


/*******人员 */
Route::group('person', function () {
    Route::post('updatePasswordByEmailCode', 'updatePasswordByEmailCode')->middleware('checkrequest', 9)->allowCrossDomain();
    Route::post('changeProfileByToken', 'changeProfileByToken')->middleware('checkrequest', 9)->allowCrossDomain();


    
    Route::post('submitApplicatioin', 'submitApplicatioin')->middleware('checkrequest', 8)->allowCrossDomain();
    Route::get('getPartyBranch', 'getPartyBranch')->middleware('checkrequest', 8)->allowCrossDomain();
    Route::get('getIsOneStep', 'getIsOneStep')->middleware('checkrequest', 8)->allowCrossDomain();
    Route::get('getApplyStep', 'getApplyStep')->middleware('checkrequest', 8)->allowCrossDomain();
    
    Route::post('submitTransferApply', 'submitTransferApply')->middleware('checkrequest', 8)->allowCrossDomain();
    Route::get('getTransferApplyStep', 'getTransferApplyStep')->middleware('checkrequest', 8)->allowCrossDomain();
    Route::get('viewHistoryTransferApply', 'viewHistoryTransferApply')->middleware('checkrequest', 8)->allowCrossDomain();


    
    Route::get('getProfile', 'getProfile')->middleware('checkrequest', 9)->allowCrossDomain();
    Route::get('getMyInfo', 'getMyInfo')->middleware('checkrequest', 9)->allowCrossDomain();


    Route::get('viewBulletin', 'viewBulletin')->middleware('checkrequest', 9)->allowCrossDomain();
    Route::get('readBulletin', 'readBulletin')->middleware('checkrequest', 9)->allowCrossDomain();



    

    
    // Route::post('selectGoods', 'selectGoods')->middleware('checkrequest', 1)->allowCrossDomain();
})->completeMatch()->prefix('Person/');



/*******数据库备份 */
Route::group('databack', function () {
    Route::post('viewBackupFile', 'viewBackupFile')->middleware('checkrequest', 3)->allowCrossDomain();
    Route::post('backupSqlApi', 'backupSqlApi')->middleware('checkrequest', 3)->allowCrossDomain();
    Route::post('restoreThisSqlByBackupFile', 'restoreThisSqlByBackupFile')->middleware('checkrequest', 3)->allowCrossDomain();
    Route::post('deleteBackupFile', 'deleteBackupFile')->middleware('checkrequest', 3)->allowCrossDomain();

    // Route::post('changeProfileByToken', 'changeProfileByToken')->middleware('checkrequest', 9)->allowCrossDomain();

    
    // Route::post('selectGoods', 'selectGoods')->middleware('checkrequest', 1)->allowCrossDomain();
})->completeMatch()->prefix('DataBack/');





