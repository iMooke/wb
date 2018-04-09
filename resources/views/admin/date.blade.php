@extends('layouts.index')
@section('content')
<div class="row">
    @foreach($directories as $val)
        <div class="col-xs-3 col-md-3">
            <div class="thumbnail">
                <img src="{{asset('static/img/list.png')}}" alt="...">
                <div class="caption">
                    <p>{{$val}}</p>
                    <p><a href="{{ url('image') . '/' . $val }}" class="btn btn-primary" role="button">查看图片</a></p>
                </div>
            </div>
        </div>
    @endforeach
</div>
@endsection