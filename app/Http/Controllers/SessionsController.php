<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
//Auth 默认对应的是 App\Models\User::class，它的配置文件位于 config/auth.php
//use Auth;
use Illuminate\Support\Facades\Auth;

class SessionsController extends Controller
{
//    // 分配到路由中间件  https://learnku.com/docs/laravel/8.x/middleware/9366#901403
    public function __construct()
    {
        // 未登录用户中间件
//        $this->middleware('guest',[
//            'only' => ['create'],
//        ]);

        // 登录限流：10 分钟内只能尝试 10 次 登录操作
        $this->middleware('throttle:8,10')->only('store');
    }

    // 显示用户登录页面
    public function create()
    {
        return view('sessions.create');
    }

    // 用户注册提交的 post 地址
    public function store(Request $request)
    {
        $credentials = $this->validate($request,[
            'email' => 'required|email|max:255',
            'password' => 'required'
        ]);

        if(Auth::attempt($credentials,$request->has('remeber'))){
            if(Auth::user()->activated){
                session()->flash('success','欢迎回来！');
                $fallback = route('users.show',Auth::user());
                return redirect()->intended($fallback);
            }else{
                Auth::logout();
                session()->flash('warning','你的账号未激活，请检查邮箱中的注册邮件进行激活。');
                return redirect('/');
            }
        }else{
            session()->flash('danger', '很抱歉，您的邮箱和密码不匹配');
            return redirect()->back()->withInput();
        }
    }

    // 用户退出登录的 delete
    public function destroy()
    {
        Auth::logout();
        session()->flash('success','您已成功退出！');
        return redirect()->route('login');
    }
}
