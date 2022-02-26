<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class UsersController extends Controller
{
    public function __construct()
    {
//        $this->middleware('auth',[
//            // except:以下方法不使用中间件
//            'except' => ['show','create','store'],
//        ]);
//
//        $this->middleware('guest',[
//            'only' => ['create'],
//        ]);

        // 8.x 可以这样写 https://learnku.com/docs/laravel/8.x/controllers/9368#c66e88
        // 以下方法不使用 auth 中间件
        $this->middleware('auth')->except(['show','create','store','index']);
        // 只有以下方法使用 guest 中间件
        $this->middleware('guest')->only('create');
    }

    // 注册
    public function create()
    {
        return view('users.create');
    }

    // 显示个人页面
    // 可以看每个人的主页
    public function show(User $user)
    {
        return view('users.show',compact('user'));
    }

    //用户注册的 post 请求
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
    }

    // 编辑用户的页面
    public function edit(User $user)
    {
        // 5.7/5.8
        // $this->authorize('update',$user);

        // https://learnku.com/docs/laravel/8.x/authorization/9382#8b90e1
        $respone = Gate::inspect('update',$user);
        return $respone->allowed()
            ? view('users.edit',compact('user'))
            : Gate::authorize('update',$user);

    }

    // 更新用户的 post 请求
    public function update(User $user,Request $request)
    {
        $this->authorize('update',$user);
        $this->validate($request,[
            'name' => 'required|max:50',
            'password' => 'nullable|confirmed|min:6',
        ]);

        $data = [];
        $data['name'] = $request->name;
        if($request->password){
            $data['password'] = bcrypt($request->password);
        }
        $user->update($data);

        session()->flash('success','个人资料更新成功！');
        return redirect()->route('users.show',$user->id);
    }

    // 显示用户列表
    public function index(User $user)
    {
//        $users = User::all();
        $users = User::paginate(6);
        return view('users.index',compact('users'));
    }
}
