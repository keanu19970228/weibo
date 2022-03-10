<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FollowersController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    //  关注动作
    public function store(User $user)
    {
        $this->authorize('follow', $user);

        if ( ! Auth::user()->isFollowing($user->id)) {
            Auth::user()->follow($user->id);
        }
        session()->flash('success','已关注'.$user->name);
        return redirect()->route('users.show', $user->id);
    }

    //  取消关注动作
    public function destroy(User $user)
    {
        $this->authorize('follow', $user);

        if (Auth::user()->isFollowing($user->id)) {
            Auth::user()->unfollow($user->id);
        }

        session()->flash('danger','已取消关注'.$user->name);
        return redirect()->route('users.show', $user->id);
    }
}
