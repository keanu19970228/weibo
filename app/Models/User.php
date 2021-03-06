<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
//加载 HasFactory trait 可用于生成假数据
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Str;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    // Eloquent 模型中借助对 table 属性的定义，来指明要进行数据库交互的数据库表名称，
    // 看过上面的示例，你可能留意到了我们没有为 Eloquent 指明 Flight 模型要使用哪张数据表。除非明确指定使用其它数据表，否则将按照约定，使用类的复数形式「蛇形命名」来作为表名。因此，在这种情况下，Eloquent 将认为 Flight 模型存储的是 flights 表中的数据
    // https://learnku.com/docs/laravel/8.5/eloquent/10409#bd9cb1
    protected $table = 'users';

    /**
     * The attributes that are mass assignable.
     * fillable:过滤用户提交的字段，只有包含在该属性中的字段才能够被正常更新：
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function gravatar($size = '100')
    {
        $hash = md5(strtolower(trim($this->attributes['email'])));
        return "http://www.gravatar.com/avatar/$hash?s=$size";
    }

    // boot 方法会在用户模型类完成初始化之后进行加载，因此我们对事件的监听需要放在该方法中。
    public static function boot()
    {
        parent::boot();

        // creating 用于监听模型被创建之前的事件，当新用户被创建时生成 activation_token 的值
        static::creating(function ($user) {
            $user->activation_token = Str::random(10);
        });
    }

    // 一个用户拥有多条微博。
    public function statuses()
    {
//        return $this->hasOne(Status::class); 也可以？？？
        return $this->hasMany(Status::class);
    }

    // 获取当前用户关注的人发布过的所有微博动态
    public function feed()
    {
        $user_ids = $this->followings->pluck('id')->toArray();
        array_push($user_ids, $this->id);
        return Status::whereIn('user_id', $user_ids)
            ->with('user')
            ->orderBy('created_at', 'desc');
    }

    //  我的粉丝
    public function followers()
    {
        return $this->belongsToMany(User::class,'followers','user_id','follower_id');
    }

    //  我关注的人
    public function followings()
    {
        return $this->belongsToMany(User::class,'followers','follower_id','user_id');
    }

    //  关注动作
    public function follow($user_ids)
    {
        if ( ! is_array($user_ids)) {
            $user_ids = compact('user_ids');
        }
        $this->followings()->sync($user_ids, false);
    }

    //  取消关注动作
    public function unfollow($user_ids)
    {
        if ( ! is_array($user_ids)) {
            $user_ids = compact('user_ids');
        }
        $this->followings()->detach($user_ids);
    }

    //  判断是否关注某人
    public function isFollowing($user_id)
    {
        return $this->followings->contains($user_id);
    }

}
