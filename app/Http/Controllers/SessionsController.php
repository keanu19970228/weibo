<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
//Auth 默认对应的是 App\Models\User::class，它的配置文件位于 config/auth.php
//use Auth;
use Illuminate\Support\Facades\Auth;

class SessionsController extends Controller
{
    public function __construct()
    {
        $this->middleware('guest',[
            'only' => ['create'],
        ]);
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
            session()->flash('success','欢迎回来！');
            $fallback = route('users.show',Auth::user());
//            return redirect()->route('users.show',[Auth::user()]);
            return redirect()->intended($fallback);
        }else{
            session()->flash('danger', '很抱歉，您的邮箱和密码不匹配');
            return redirect()->back()->withInput();
        }
    }

    // 用户推出登录的 delete
    public function destroy()
    {
        Auth::logout();
        session()->flash('success','您已成功退出！');
        return redirect()->route('login');
    }
}
