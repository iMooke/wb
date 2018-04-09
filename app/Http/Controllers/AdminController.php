<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class AdminController extends Controller
{
    public function login(Request $request)
    {
        if ($request->isMethod('get')){
            return view('admin.login');
        }
        if (!$request->email || !$request->password) {
            return json_encode([
                'status' => -1,
                'message' => '邮箱或密码不能为空！'
            ]);
        }
        $user = DB::table('wb_user')
            ->where('wb_u_email', $request->email)
            ->first();
        if (!$user || $user->wb_u_password != md5($request->password.'wb')) {
            return json_encode([
                'status' => -1,
                'message' => '账号或密码不正确！'
            ]);
        }
        session([
            'user.id' => $user->wb_u_id,
            'user.name' => $user->wb_u_name,
            'user.image' => $user->wb_u_image,
        ]);
        return json_encode([
            'status' => 0,
            'message' => '登录成功！'
        ]);

    }

    public function image(Request $request)
    {
        if ($request->date) {
            $files = Storage::files('uploads/article/'.$request->date);
            foreach ($files as $key => $val) {
                $files[$key] = url('/').'/'.$val;
            }
            return view('admin.image')->with('files', $files);
        }
        $directories = Storage::directories('uploads/article');
        foreach ($directories as $key => $val) {
            $directories[$key] = str_replace('uploads/article/', '', $val);
        }
        $directories = array_reverse($directories);
        return view('admin.date')->with('directories', $directories);
    }
}
