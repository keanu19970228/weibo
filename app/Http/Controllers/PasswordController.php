<?php
/**
 * @Name 充值、忘记密码
 * @Description
 * @Auther LoCarlu
 * @Date 2022/2/28 23:23
 */

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;


class PasswordController extends Controller
{
    // 重置密码页面
    public function showLinkRequestForm()
    {
        return view('passwords.email');
    }

    // 提交重置密码的邮箱信息（ 处理表单提交，成功的话就发送邮件，附带 Token 的链接）
    public function sendResetLinkEmail(Request $request)
    {
        // 1.验证邮箱
        $request->validate(['email' => 'required|email']);
        $email = $request->email;

        // 2.获取对应用户
        $user = User::where('email',$email)->first();

        // 3.如果用户不存在
        if(is_null($user)){
            session()->flash('danger','邮箱未注册');
            return redirect()->back()->withInput();
        }

        // 4.生成 Token, 会在视图 emails.reset_link 里拼接链接
        $token = hash_hmac('sha256',Str::random(40),config('app.key'));

        // 5.入库，使用updateOrInsert 来保持 Email 唯一
        DB::table('password_resets')->updateOrInsert(['email'=>$email],[
            'email' => $email,
            'token' => Hash::make($token),
            'created_at' => new Carbon,
        ]);

        // 6.将 Token 链接发送给用户
        Mail::send('email.reset_link',compact('token'),function($message) use ($email) {
            $message->to($email)->subject('忘记密码');
        });

        session()->flash('success','重置邮件发送成功，请查收');
        return redirect()->back();
    }
}
