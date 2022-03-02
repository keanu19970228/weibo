<div class="list-group-item">
    <img class="mr-3" src="{{ $user->gravatar() }}" alt="{{ $user->name }}" width=32>
    <a href="{{ route('users.show', $user) }}">
        {{ $user->name }}
    </a>

{{--    授权策略：@can Blade 命令，在 Blade 模板中做授权判断。--}}
    @can('destroy', $user)
        <form action="{{ route('users.destroy', $user) }}" onsubmit="return confirm('你确定要删除本条微博吗？')" method="post" class="float-end">
            {{ csrf_field() }}
            {{ method_field('DELETE') }}
            <button type="submit" class="btn btn-sm btn-danger delete-btn">删除</button>
        </form>
    @endcan
</div>
