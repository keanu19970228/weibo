<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class UsersController extends Controller
{
    public function create()
    {
        return view('users.create');
    }

    public function show(User $user)
    {
//        var_dump($user);die;
        return view('users.show',compact('user'));
    }

    /**
     * 用于处理表单数据
     */
    public function store(Request $request)
    {
        $this->validate($request,[
            'name' => 'required|unique:users|max:50',
            'email' => 'required|email|unique:users|max:255',
            'password' => 'required|confirmed|min:6',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
        ]);

        //用户注册成功后自动登录。
        Auth::login($user);
        
        session()->flash('success','欢迎，您将在这里开启一段新的旅程~');
        return redirect()->route('users.show',[$user->id]);
        return;
    }
}
