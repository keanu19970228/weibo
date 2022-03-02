<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Auth;

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
        $this->middleware('auth')->except(['show','create','store','index','confirmEmail']);
        // 只有以下方法使用 guest 中间件
        $this->middleware('guest')->only('create');
        // 注册限流：一个小时内只能提交 10 次注册请求
        $this->middleware('throttle:12,60')->only('store');
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
        $statuses = $user->statuses()
            ->orderBy('created_at','desc')
            ->paginate(10);
        return view('users.show',compact('user','statuses'));
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

        $this->sendEmailConfirmationTo($user);
        session()->flash('success','验证邮箱已发送到你的注册邮箱上，请注意查收。');
        return redirect('/');
//        //用户注册成功后自动登录。
//        Auth::login($user);
//
//        session()->flash('success','欢迎，您将在这里开启一段新的旅程~');
//        return redirect()->route('users.show',[$user->id]);
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
          // 简单分页
//        $users = User::simplePaginate(6);
        $respone = Gate::inspect('destroy',$user);
        if($respone->allowed()){
            $users = User::paginate(6);
            return view('users.index',compact('users'));
        }
        session()->flash('danger','权限不足！');
        return back();
    }

    // 删除用户动作
    public function destroy(User $user)
    {
        // 5.7/5.8
        // $this->authorize('destroy',$user);
        $respone = Gate::inspect('destroy',$user);
        if($respone->allowed()){
            $user->delete();
            session()->flash('success','成功删除用户！');
        }else{
            session()->flash('danger','权限不足！');
        }
        return back();
    }

    // 注册邮箱 api
    public function sendEmailConfirmationTo($user)
    {
        // 线下 log 测试
//        $view = 'emails.confirm';
//        $data = compact('user');
//        $from = 'summer@example.com';
//        $name = 'Summer';
//        $to = $user->email;
//        $subject = '感谢注册 Weibo 应用！请确认你的邮箱。';
//
//        Mail::send($view,$data,function ($message) use ($from,$name,$to,$subject) {
//            $message->from($from,$name)->to($to)->subject($subject);
//        });

        // 真实发送邮件
        $view = 'emails.confirm';
        $data = compact('user');
        $to = $user->email;
        $subject = "感谢注册 Weibo 应用！请确认你的邮箱。";

        Mail::send($view, $data, function ($message) use ($to, $subject){
            $message->to($to)->subject($subject);
        });
    }

    // 验证邮箱页面
    public function confirmEmail($token)
    {
        // 我们需要使用 firstOrFail 方法来取出第一个用户，在查询不到指定用户时将返回一个 404 响应。
        $user = User::where('activation_token',$token)->firstOrFail();

        $user->activated = true;
        $user->activation_token = null;
        $user->save();

        Auth::login($user);
        session()->flash('success','恭喜你，激活成功！');
        return redirect()->route('users.show',[$user->id]);
    }

}
