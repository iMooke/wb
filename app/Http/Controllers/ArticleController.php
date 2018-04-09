<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ArticleController extends Controller
{
    public function index(Request $request)
    {
        //初始化搜索条件
        $where = '';
        $cls = '';
        if ($request->search){
            $where = $request->search;
            $cls = DB::table('wb_class')->where('wb_c_id', $request->search)->first();
            if ($cls) {
                $where = $cls->wb_c_name;
            }
        }

        //根据搜索条件取出文章列表
        $data = DB::table('wb_article')
            ->join('wb_user', 'wb_article.wb_u_id', '=', 'wb_user.wb_u_id')
            ->select('wb_article.*', 'wb_user.wb_u_name')
            ->orWhere('wb_a_title', 'LIKE', '%'.$where.'%')
            ->orWhere('wb_a_describe', 'LIKE', '%'.$where.'%')
            ->orWhere('wb_c_id', $cls?$cls->wb_c_id:0)
//            ->where('wb_a_status', 0)
            ->orderBy('wb_a_id', 'DESC')
            ->paginate(6);
        $class = DB::table('wb_class')->orderBy('wb_c_id', 'DESC')->limit(9)->get();
        foreach ($class as $key => $val) {
            $count = DB::table('wb_article')
                ->where('wb_c_id', $val->wb_c_id)
                ->count();
            $class[$key]->count = $count;
        }
        return view('index')->with([
            'data' => $data,
            'where' => $cls?$cls->wb_c_id:$where,
            'search' => $where,//翻页时传递此搜索参数
            'class' => $class
        ]);
    }

    public function show(Request $request)
    {
        #如果为left 取比id小的最近的一个 为right取比id大的最近的一个  如果取不到则取最小或最大的一个（或者提示没有了）
        $id = $request->id;
        $symbol = '=';
        $order = 'DESC';
        if ($request->left) {
            $symbol = '<';
            $order = 'DESC';
        }
        if ($request->right) {
            $symbol = '>';
            $order = 'ASC';
        }
        $article = DB::table('wb_article')
            ->where('wb_a_id', $symbol, $id)
            ->orderBy('wb_a_id',$order)
            ->first();
        //如果没有取出文章 则从所有文章内随机选一篇
        if (!$article) {
            $article = DB::table('wb_article')
                ->orderBy('wb_a_id',$order)
                ->first();
        }
        $content = DB::table('wb_content')
            ->where('wb_c_id', $article->wb_a_id)
            ->first();
        $like = DB::table('wb_article')
            ->where('wb_c_id',$article->wb_c_id)
            ->limit(15)
            ->get();
        return view('article.details')->with([
            'article' => $article,
            'content' => $content,
            'like' => $like
        ]);
    }

    public function add(Request $request)
    {
        if ($request->isMethod('get')) {
            if (!session('user.id')){
                return redirect('/');
            }
            $class = DB::table('wb_class')->get();
            return view('article.add')->with('class', $class);
        }
        if (!session('user.id')){
            return json_encode([
                'status' => -1,
                'message' => '登录过期，请重新登录！'
            ]);
        }
        //检验数据合法性
        $input = Input::except('_token');
        UseLog::write(__FILE__, __LINE__, '添加文章', 'ERROR', 'article_add_'.date('Y-m-d', time()).'.log');
        $rules = [
            'title' => 'required|max:64',
            'class' => 'required',
            'describe' => 'required|max:255',
        ];
        $messages = [
            'title.required' => '请填写标题！',
            'class.required' => '请选择分类！',
            'title.max' => '标题最多64个字，您已超'.(mb_strlen($input['title'],'utf-8')-64).'个字符',
            'describe.required' => '请填写简介！',
            'describe.max' => '简介最多255个字符，您已超'.(mb_strlen($input['describe'],'utf-8')-255).'个字符'
        ];

        $validator = Validator::make($input, $rules, $messages);
        if(!$validator->passes())
        {
            //把第一条错误信息传给前端
            return json_encode([
                'status' => -1,
                'message' => $validator->errors()->all()[0]
            ]);
        }
        $class = DB::table('wb_class')->where('wb_c_id', $input['class'])->first();
        if (!$class) {
            UseLog::write(__FILE__, __LINE__, '分类不正确：'.$input['class'], 'ERROR', 'article_add_'.date('Y-m-d', time()).'.log');
            return json_encode([
                'status' => -1,
                'message' => '保存失败，请重试！'
            ]);
        }
        //启用事务 保持文章和内容一致性
        try {
            DB::transaction(function () use($input, $class) {
                $articleId = DB::table('wb_article')
                    ->insertGetId([
                        'wb_a_title' => $input['title'],
                        'wb_c_id' => $class->wb_c_id,
                        'wb_a_describe' => $input['describe'],
                        'wb_u_id' => session('user.id'),
                        'wb_a_create' => date('Y-m-d H:i:s'),
                    ]);
                DB::table('wb_content')
                    ->insert([
                        'wb_c_id' => $articleId,
                        'wb_c_content' => $input['content'],
                        'wb_c_create' => date('Y-m-d H:i:s'),
                    ]);
            });
        }catch (\Exception $e) {
            UseLog::write(__FILE__, __LINE__, '分类不正确：'.$e->getMessage(), 'ERROR', 'article_add_'.date('Y-m-d', time()).'.log');

            //写错误日志
            return json_encode([
                'status' => -1,
                'message' => '服务器错误！'
            ]);
        }

        return json_encode([
            'status' => 0,
            'message' => '保存成功！'
        ]);
    }

    public function edit(Request $request)
    {

        if ($request->isMethod('get')) {

            $data = array();
            $article = DB::table('wb_article')
                ->where('wb_a_id', $request->id)
                ->first();
            //
            if (!$article) {
                return view('errors.503');
            }
            if (!session('user.id')){
                return redirect('show/'.$article->wb_a_id);
            }
            $content = DB::table('wb_content')
                ->where('wb_c_id', $article->wb_a_id)
                ->first();
            if ($content) {
                $data = [
                    'id' => $article->wb_a_id,
                    'title' => $article->wb_a_title,
                    'class' => $article->wb_c_id,
                    'describe' => $article->wb_a_describe,
                    'content' => $content->wb_c_content,
                ];
            }
            $class = DB::table('wb_class')->get();
            return view('article.edit')->with([
                'data' => $data,
                'class' => $class
            ]);
        }
        if (!session('user.id')){
            return json_encode([
                'status' => -1,
                'message' => '登录过期，请重新登录！'
            ]);
        }
        //检验数据合法性
        $input = Input::except('_token');

        if (!$input['id']) {
            UseLog::write(__FILE__, __LINE__, '未检测到文章ID'.session('user.id'), 'ERROR', 'article_edit_'.date('Y-m-d', time()).'.log');

            return json_encode([
                'status' => -1,
                'message' => '服务器错误！'
            ]);
        }
        $rules = [
            'title' => 'required|max:64',
            'class' => 'required',
            'describe' => 'required|max:255',
        ];
        $messages = [
            'title.required' => '请填写标题！',
            'class.required' => '请选择分类！',
            'title.max' => '标题最多64个字，您已超'.(mb_strlen($input['title'],'utf-8')-64).'个字符',
            'describe.required' => '请填写简介！',
            'describe.max' => '简介最多255个字符，您已超'.(mb_strlen($input['describe'],'utf-8')-255).'个字符'
        ];

        $validator = Validator::make($input, $rules, $messages);
        if(!$validator->passes())
        {
            //把第一条错误信息传给前端
            return json_encode([
                'status' => -1,
                'message' => $validator->errors()->all()[0]
            ]);
        }
        //更新数据
        //启用事务 保持文章和内容一致性
        try {
            DB::transaction(function () use($input) {
                DB::table('wb_article')
                    ->where('wb_a_id', $input['id'])
                    ->update([
                        'wb_a_title' => $input['title'],
                        'wb_c_id' => $input['class'],
                        'wb_a_describe' => $input['describe'],
                    ]);

                DB::table('wb_content')
                    ->where('wb_c_id', $input['id'])
                    ->update([
                        'wb_c_content' => $input['content'],
                    ]);
            });
        }catch (\Exception $e) {
            UseLog::write(__FILE__, __LINE__, '更新数据库错误：'.$e->getMessage(), 'ERROR', 'article_edit_'.date('Y-m-d', time()).'.log');
            //写错误日志
            return json_encode([
                'status' => -1,
                'message' => '服务器错误！'
            ]);
        }

        return json_encode([
            'status' => 0,
            'message' => '修改成功！'
        ]);
    }

    public function classAdd()
    {
        if (!session('user.id')){
            return json_encode([
                'status' => -1,
                'message' => '登录过期，请重新登录！'
            ]);
        }
        $input = Input::except('_token');

        $rules = [
            'name' => 'required|max:8|unique:wb_class,wb_c_name',
        ];
        $messages = [
            'name.required' => '不能添加空的分类！',
            'name.max' => '分类最多8个字，您已超'.(mb_strlen($input['name'])-8).'个字！',
            'name.unique' => '分类已经存在！',
        ];

        $validator = Validator::make($input, $rules, $messages);
        if(!$validator->passes())
        {
            //把第一条错误信息传给前端
            return json_encode([
                'status' => -1,
                'message' => $validator->errors()->all()[0]
            ]);
        }

        try {
            $id = DB::table('wb_class')
                ->insertGetId([
                    'wb_c_name' => $input['name'],
                    'wb_c_create' => date('Y-m-d H:i:s'),
                ]);
        }catch (\Exception $e) {
            return json_encode([
                'status' => -1,
                'message' => '添加失败，稍后再试！'
            ]);
        }

        return json_encode([
            'status' => 0,
            'message' => '添加成功！',
            'new' => [
                'val' => $id,
                'name' => $input['name']
            ]
        ]);

    }

    public function remove(Request $request)
    {
        return json_encode([
            'status' => -1,
            'message' => '暂未开放！'
        ]);
        if (!$request->id){
            return json_encode([
                'status' => -1,
                'message' => '删除失败，稍后再试！'
            ]);
        }
        if (!session('user.id')){
            return json_encode([
                'status' => -1,
                'message' => '登录过期，请重新登录！'
            ]);
        }

        DB::table('wb_article')
            ->where('wb_a_id',$request->id)
            ->update([
                'wb_a_status' => 1
            ]);

        return json_encode([
            'status' => 0,
            'message' => '删除成功！'
        ]);
    }

    public function like(Request $request)
    {
        if (!$request->id){
            return json_encode([
                'status' => -1,
                'message' => '点赞失败，稍后再试！'
            ]);
        }
        if (!session('user.id')){
            return json_encode([
                'status' => -1,
                'message' => '登录过期，请重新登录！'
            ]);
        }

        DB::table('wb_article')
            ->where('wb_a_id',$request->id)
            ->increment('wb_a_like', 1);

        return json_encode([
            'status' => 0,
            'message' => '感谢点赞！'
        ]);
    }

    public function upload(Request $request)
    {
        $file = $request->file('name');
        //判断文件是否上传成功
        if(!$file->isValid()){
            return json_encode([
                'errno' => -1,
                'message' => '上传失败！'
            ]);
        }
        //获取原文件名
        $originalName = $file->getClientOriginalName();
        //扩展名
        $ext = $file->getClientOriginalExtension();
        //文件类型
        $type = $file->getClientMimeType();
        //临时绝对路径
        $realPath = $file->getRealPath();
        //上传目录
        $filename = 'uploads/article/'.date('Y-m-d').'/'.uniqid().time().'.'.$ext;
        //把内容写入文件
        $bool = Storage::put($filename, file_get_contents($realPath));
        //删除临时文件
        unlink($realPath);
        if (!$bool) {
            return json_encode([
                'errno' => -2,
                'message' => '上传失败！'
            ]);
        }

        return json_encode([
            'errno' => 0,
            'url' => url($filename)
        ]);
    }
}
