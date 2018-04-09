@extends('layouts.index')
@section('content')

<form style="padding: 15px" class="form-signin">
    <label for="inputEmail" class="sr-only">Email address</label>
    <input type="email" name="email" id="inputEmail" class="form-control" placeholder="Email address" required autofocus>
    <label for="inputPassword" class="sr-only">Password</label>
    <input type="password" name="password" id="inputPassword" class="form-control" placeholder="Password" required>
    <button id="login" class="btn btn-primary btn-block" type="button">登 录</button>
</form>

<script>
    //注意：parent 是 JS 自带的全局对象，可用于操作父页面
    var index = parent.layer.getFrameIndex(window.name); //获取窗口索引
    $('#login').click(function () {
        $.ajax({
            type: 'post',
            url: "{{url('login')}}",
            data: {
                '_token': "{{csrf_token()}}",
                'email': $('#inputEmail').val(),
                'password': $('#inputPassword').val()
            },
            dataType: 'json',
            success: function (data) {
                if (data.status == 0) {
                    layer.msg(data.message, {icon: 6});
                    setTimeout(function(){
                        // 关闭本窗口
                        parent.layer.close(index);
                        // 刷新父级页面
                        parent.location.reload();
                    }, 1500);

                }else {
                    layer.msg(data.message, {icon: 5});
                }
            },
            error: function (XMLHttpRequest, textStatus, errorThrown) {

            }
        });
    });
</script>
@endsection