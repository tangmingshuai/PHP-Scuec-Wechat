<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// webhook
Route::post('webhook', 'WebhookController@handler');

Route::get('/test1', 'StudentsController@test');

Route::get('/students/create/{type}/{openid}', 'StudentsController@create')->name('students.create');
Route::post('/students', 'StudentsController@store')->name('students.store');

Route::any('/wechat', 'WeChatController@serve');

// 用于修复微信文章 subscene 参数的 bug
Route::get('/wechatArticleRedirect', 'WeChatController@redirectWechatArticle');

// 大物实验
Route::group(['prefix' => '/phy_exp'], function () {
    // 绑定账号
    Route::get('/bind_account', 'Api\PhysicalExperimentController@bindAccountView')
        ->name('phy_exp.bind');
});

Route::get('getUrl', function () {
    return  Wechat::oauth()->scopes(['snsapi_base'])->setRedirectUrl(route('phy_exp.bind'))
        ->redirect()->getTargetUrl();
});

Route::group(['prefix' => 'bye'], function () {
    Route::get('wish_wall', 'ByeController@wishWall');
});