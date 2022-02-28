<?php

use Illuminate\Support\Facades\Route;

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

//Route::get('/', function () {
//    return view('welcome');
//});

//基础页面
Route::get('/','StaticPagesController@home')->name('home');
Route::get('/help','StaticPagesController@help')->name('help');
Route::get('/about','StaticPagesController@about')->name('about');

//注册
Route::get('signup','UsersController@create')->name('signup');

Route::resource('users','UsersController');

//会话
Route::get('login','SessionsController@create')->name('login')->middleware('guest');
Route::post('login','SessionsController@store')->name('login');
Route::delete('logout','SessionsController@destroy')->name('logout');

//邮箱激活显示页面
Route::get('signup/confirm/{token}','UsersController@confirmEmail')->name('confirm_email');

//重置密码

//重置密码显示页面（填写 Email 的表单）
Route::get('password/reset','PasswordController@showLinkRequestForm')->name('password.request');
//提交重置密码的邮箱信息（ 处理表单提交，成功的话就发送邮件，附带 Token 的链接）
Route::post('password/email','PasswordController@sendResetLinkEmail')->name('password.email');
//用户邮箱中 点击重置密码显示的页面（显示更新密码的表单，包含 token）
Route::get('password/reset/{token}','PasswordController@showResetForm')->name('password.reset');
//重置密码提交信息操作（ 对提交过来的 token 和 email 数据进行配对，正确的话更新密码）
Route::post('password/reset','PasswordController@reset')->name('password.update');

