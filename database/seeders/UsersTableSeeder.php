<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 用于生成 用户 假数据
     * @return void
     */
    public function run()
    {
        // User::factory() 返回的是一个 UserFactory 对象。 count() 是由工厂类提供的 API，接受一个参数用于指定要创建的模型数量，create() 方法来将生成假用户列表数据插入到数据库中。
        User::factory()->count(50)->create();

        $user = User::find(1);
        $user->name = "Summer";
        $user->email = "summer@example.com";
        $user->save();
    }
}
