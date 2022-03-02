<?php

namespace App\Http\Controllers;

use App\Models\Status;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StatusesController extends Controller
{
    public function __construct()
    {
        // 需要登录才能操作！
        $this->middleware('auth');
    }

    // 发布微博
    public function store(Request $request)
    {
        $this->validate($request,[
            'content' => 'required|max:140',
        ]);

        // 这个用户(当前用户)->发布了微博->内容是。
        Auth::user()->statuses()->create([
            'content' => $request['content'],
        ]);

        session()->flash('success','发布成功！');
        return redirect()->back();
    }

    // 删除微博
    public function destroy(Status $status)
    {
//        授权策略
        $this->authorize('destroy',$status);
        $status->delete();
        session()->flash('success','微博已被成功删除！');
        return redirect()->back();
    }
}
