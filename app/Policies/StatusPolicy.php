<?php

namespace App\Policies;

use App\Models\Status;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class StatusPolicy
{
    use HandlesAuthorization;

    // 因为之前我们已经在  AuthServiceProvider 中设置了「授权策略自动注册」，所以这里不需要做任何处理 StatusPolicy 将会被自动识别。
    public function destroy(User $user, Status $status)
    {
        return $user->id === $status->user_id;
    }
}
