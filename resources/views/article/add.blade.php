@extends('layouts.index')
@section('content')

<form style="margin: 2% 2%;">
    <input type="text" id="title" class="form-control" placeholder="标题"><br>
    <div class="input-group">
        <select id="class" class="form-control">
            <option>选择分类</option>
            @if($class)
            @foreach($class as $val)
                <option class="cid" value="{{$val->wb_c_id}}">{{$val->wb_c_name}}</option>
            @endforeach
            @endif
        </select>
        <div class="input-group-addon"><a id="class_add" href="javascript:void(0)"><span class="glyphicon glyphicon-plus" aria-hidden="true"></span></a></div>
        <input type="text" class="form-control" id="class_name" placeholder="新增分类">
    </div>
    <br>
    <textarea class="form-control" id="describe" placeholder="描述" rows="3"></textarea>
    <div class="input-group-addon">
        <a id="image" href="#"><span class="glyphicon glyphicon-picture" aria-hidden="true"></span></a>
    </div>
    <div id="editor">

    </div>
    <button id="add" class="btn btn-primary btn-block" type="button">保 存</button>
</form>

<script>
    var E = window.wangEditor;
    var editor = new E('#editor');
    editor.customConfig.customAlert = function (info) {
        layer.msg(info, {icon: 5});
    };
    // 将图片大小限制为 1M
    editor.customConfig.uploadImgMaxSize = 1 * 1024 * 1024;
    //自定义图片上传
    editor.customConfig.customUploadImg = function (files, insert) {
        var fd = new FormData();
        fd.append("_token", "{{csrf_token()}}");
        fd.append("name", files[0]);
        $.ajax({
            url: "{{url('upload')}}",
            type: "POST",
            processData: false,
            contentType: false,
            data: fd,
            dataType: 'json',
            success: function(data) {
                insert(data.url);
            }
        });
    };

    editor.create();

    //注意：parent 是 JS 自带的全局对象，可用于操作父页面
    var index = parent.layer.getFrameIndex(window.name); //获取窗口索引
    $('#add').click(function () {
        $.ajax({
            type: 'post',
            url: "{{url('add')}}",
            data: {
                '_token': "{{csrf_token()}}",
                'title': $('#title').val(),
                'class': $('.cid:selected').val(),
                'describe': $('#describe').val(),
                'content': editor.txt.html(),
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

    $('#class_add').click(function () {
        layer.msg('确定添加？', {
            time: 0 //不自动关闭
            ,btn: ['确定', '取消']
            ,yes: function(index){
                $.ajax({
                    type: 'post',
                    url: "{{url('class/add')}}",
                    data: {
                        '_token': "{{csrf_token()}}",
                        'name': $('#class_name').val(),
                    },
                    dataType: 'json',
                    success: function (data) {
                        if (data.status == 0) {
                            $('#class').append('<option class="cid" value="'+data.new.val+'" selected>'+data.new.name+'</option>');
                            layer.msg(data.message, {icon: 6});
                        }else {
                            layer.msg(data.message, {icon: 5});
                        }
                    },
                    error: function (XMLHttpRequest, textStatus, errorThrown) {

                    }
                });
            }
        });

    });

    $('#image').click(function () {
        layer.open({
            type: 2,
            area: ['80%', '80%'],
            fixed: false, //不固定
            maxmin: true,
            content: "{{url('image')}}"
        });
    });
</script>
@endsection