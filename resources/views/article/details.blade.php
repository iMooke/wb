@extends('layouts.index')
@section('content')
<div class="wb_top">

</div>
<div class="row">
    <div class="col-xs-12 col-sm-6 col-md-9">
        <div class="navbar-header">
            <a href="{{url('/')}}"><span class="glyphicon glyphicon-leaf" aria-hidden="true"></span></a>
        </div>
        <div class="blog-post">
            <div class="article_title">
                <h1 class="blog-title">{{$article->wb_a_title}}</h1>
                <p style="font-size: 15px" class="lead blog-description">{{$article->wb_a_describe}}</p>
            </div>
            <hr>
            {!! $content->wb_c_content !!}
        </div><!-- /.blog-post -->
        <div class="btn-group">
            <a href="{{url('show').'/'.$article->wb_a_id.'?left=1'}}"><span class="glyphicon glyphicon-menu-left"></span></a>
            @if(session('user.id') == 1)
            <a id="edit" href="#"><span class="glyphicon glyphicon-pencil"></span></a>
            <a id="remove" href="#"><span class="glyphicon glyphicon-trash"></span></a>
            @endif
            <a id="like" href="#"><span class="glyphicon glyphicon-heart"></span></a>
            <a href="{{url('show').'/'.$article->wb_a_id.'?right=1'}}"><span class="glyphicon glyphicon-menu-right"></span></a>
        </div>
    </div>

    <div class="col-xs-6 col-md-3">
        <hr>
        <div class="sidebar-module sidebar-module-inset">
            <h4>博 客</h4>
            <p>.........</p>
        </div>
        <hr>
        <div class="sidebar-module">
            <h4>推 荐</h4>
            <ol class="list-unstyled">
                @foreach($like as $val)
                    <li><a href="{{url('show').'/'.$val->wb_a_id}}">{{$val->wb_a_title}}</a></li>
                @endforeach
            </ol>
        </div>
        <div class="sidebar-module">
            <h4>友 链</h4>
            <ol class="list-unstyled">
                <li><a href="#">暂 无</a></li>
                <li><a href="#">暂 无</a></li>
                <li><a href="#">暂 无</a></li>
            </ol>
        </div>
    </div><!-- /.blog-sidebar -->

</div>
<div style="text-align: center;line-height:5rem " class="footer">
    <a href="{{url('/')}}"><span class="glyphicon glyphicon-leaf" aria-hidden="true"></span></a>
</div>
<script>
    $('.glyphicon-menu-left').mouseenter(function(){
        layer.tips('阅读上一篇', '.glyphicon-menu-left', {
            tips: 1
        });
    });
    $('.glyphicon-pencil').mouseenter(function(){
        layer.tips('编辑此文章', '.glyphicon-pencil', {
            tips: 1
        });
    });
    $('.glyphicon-trash').mouseenter(function(){
        layer.tips('删除此文章', '.glyphicon-trash', {
            tips: 1
        });
    });
    $('.glyphicon-heart').mouseenter(function(){
        layer.tips('喜欢此文章', '.glyphicon-heart', {
            tips: 1
        });
    });
    $('.glyphicon-menu-right').mouseenter(function(){
        layer.tips('阅读下一篇', '.glyphicon-menu-right', {
            tips: 1
        });
    });
    $('.glyphicon-leaf').mouseenter(function(){
        layer.tips('返回首页', '.glyphicon-leaf', {
            tips: 2
        });
    });
    $('#edit').click(function () {
        //修改文章
        layer.open({
            type: 2,
            area: ['750px', '550px'],
            fixed: true, //不固定
            maxmin: true,
            content: "{{url('edit').'/'.$article->wb_a_id}}"
        });
    });

    $('#remove').click(function () {
        //删除文章
        $.ajax({
            type: 'post',
            url: "{{url('remove')}}",
            data: {
                '_token': "{{csrf_token()}}",
                'id': "{{$article->wb_a_id}}"
            },
            dataType: 'json',
            success: function (data) {
                if (data.status == 0) {
                    layer.msg(data.message, {icon: 6});
                    setTimeout(function(){
                        history.back();
                    }, 1000);
                }else {
                    layer.msg(data.message, {icon: 5});
                }
            },
            error: function (XMLHttpRequest, textStatus, errorThrown) {

            }
        });
    });

    $('#like').click(function () {
        layer.msg('感谢点赞！', {icon: 6});
    });
</script>
@endsection