@extends('layouts.index')
@section('content')
<a href="#" onClick="javascript:history.back(-1);"><span class="glyphicon glyphicon-arrow-left" aria-hidden="true"></span></a>
<div class="row">
    @foreach($files as $val)
        <div class="col-xs-3 col-md-3">
            <div class="thumbnail">
                <img src="{{$val}}" alt="...">
                <div class="caption">
                    <button class="btn btn-default" data-clipboard-text="{{$val}}" type="button">复制链接</button>
                </div>
            </div>
        </div>
    @endforeach
</div>
<script>
    $(document).ready(function(){
        var clipboard = new Clipboard('.btn');
        clipboard.on('success', function(e) {
            layer.msg('复制成功！');
        });
        clipboard.on('error', function(e) {
            layer.msg('复制失败！');
        });
    })
</script>
@endsection