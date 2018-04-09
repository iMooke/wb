@extends('layouts.index')
@section('content')
<div class="wb_top">

</div>
<nav class="navbar navbar-static-top">
    <div class="container-fluid">
        <!-- Brand and toggle get grouped for better mobile display -->
        <div class="navbar-header">
            <a class="navbar-brand" href="{{url('/')}}"><img class="logo" src="{{asset('static/img/logo.png')}}"></a>
        </div>

        <!-- Collect the nav links, forms, and other content for toggling -->
        <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
            <form class="navbar-form navbar-left" method="get" action="{{url('/')}}">
                <div class="form-group">
                    <input type="text" name="search" class="form-control" placeholder="{{$search}}">
                </div>
                <button type="submit" class="btn btn-success">搜 索</button>
            </form>
            <div class="nav navbar-nav navbar-right">
                <a id="essay" href="#"><span class="glyphicon glyphicon-pencil" aria-hidden="true"></span></a>
            </div>
        </div><!-- /.navbar-collapse -->
    </div><!-- /.container-fluid -->
</nav>

<div class="row">

    <div class="col-sm-8 blog-main">
        @if($data)
        @foreach($data as $val)
            <div class="blog-post">
                <h2 class="blog-post-title"><a style="text-decoration-skip: objects" href="{{url('show').'/'.$val->wb_a_id}}">{{$val->wb_a_title}}</a></h2>
                <p class="blog-post-meta">{{$val->wb_a_create}}, by <a href="#">{{$val->wb_u_name}}</a></p>
                <p>{{$val->wb_a_describe}}</p>
            </div><!-- /.blog-post -->
        @endforeach
        @endif
        <nav>
            <div class="page_list">
                {!! $data->appends(['search' => $where])->render() !!}
            </div>
        </nav>

    </div><!-- /.blog-main -->

    <div class="col-sm-3 col-sm-offset-1 blog-sidebar">
        <div class="sidebar-module sidebar-module-inset">
            <h4>随 笔</h4>
            <p>千里之行 始于足下</p>
        </div>
        <div class="sidebar-module">
            <h4>热 门</h4>
            <ol class="list-unstyled">
                @foreach($class as $val)
                    <li>
                        <a href="{{url('/?search='.$val->wb_c_id)}}">{{$val->wb_c_name}}</a>
                        <span class="badge">{{$val->count}}</span>
                    </li>
                @endforeach
            </ol>
        </div>
        <div class="sidebar-module">
            <h4>推 荐</h4>
            <ol class="list-unstyled">
                <li><a href="#">暂 无</a></li>
                <li><a href="#">暂 无</a></li>
                <li><a href="#">暂 无</a></li>
            </ol>
        </div>
    </div><!-- /.blog-sidebar -->

</div>
<div class="footer">

</div>
<script>
    $('#essay').click(function () {
        if("{{session('user.id')}}"){
            //写文章
            layer.open({
                type: 2,
                area: ['750px', '550px'],
                fixed: false, //不固定
                maxmin: true,
                content: "{{url('add')}}"
            });
        }else{
            //登录
            layer.open({
                type: 2,
                area: ['300px', '180px'],
                fixed: false, //不固定
                maxmin: true,
                content: "{{url('login')}}"
            });
        }

    });

    $('.glyphicon-pencil').mouseenter(function(){
        layer.tips('发表文章', '.glyphicon-pencil', {
            tips: 4
        });
    });
</script>
@endsection