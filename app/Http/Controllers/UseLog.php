<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

class UseLog extends Controller
{
    /**
     * @param $file_name 文件名
     * @param $line_number 行数
     * @param $content 内容
     * @param $level 日志等级
     * @param string $path 存储位置
     * @return int
     */
    public static function write($file_name, $line_number, $content, $level, $path = '')
    {
        if($file_name == NULL || $line_number == NULL || $content == NULL || $level == NULL)
        {
            Log::emergency('WriteLog日志参数不合法');
            return -1;
        }

        if(empty($path)){
            $path = storage_path('logs/debug-'.date('Y-m-d', time()).'.log');
        }else{
            $path = storage_path('logs/'.$path);
        }

        $sg_fp = fopen($path, "a");
        if($sg_fp === FALSE)
        {
            Log::emergency('WriteLog不能正常读取日志文件：'.$path);
            return -2;
        }

        $sg_date = date("Y-m-d H:i:s", time());

        $sg_ret = flock($sg_fp, LOCK_EX);
        if($sg_ret === TRUE)
        {
            fwrite($sg_fp, "[".$sg_date."][".$file_name
                .":".$line_number."][".$level."]".$content."\n");
            flock($sg_fp, LOCK_UN);
        }
        else
        {
            fclose($sg_fp);
            Log::emergency('WriteLog不能锁定文件：'.$path);
            return -3;
        }

        fclose($sg_fp);
        return 0;
    }
}
