<?php
/*
 * @Author: xch
 * @Date: 2020-08-15 11:34:38
 * @LastEditTime: 2020-11-27 00:02:21
 * @LastEditors: 罗曼
 * @Description: 
 * @FilePath: \testd:\wamp64\www\thinkphp-api\app\controller\Login.php
 */


declare(strict_types=1);

namespace app\controller;

// use think\facade\Request;
use think\Request;
use app\model\Admin as AdminModel;
use app\model\EmployeeLogin as EmpModel;
use app\model\Person as PersonModel;

use app\model\TempCode as TempCodeModel;

use app\model\PersonAccount as PersonAccountModel;


use think\facade\Db;


class Login extends Base
{
    /**
     * 返回管理员信息
     *
     * @return \think\Response
     */
    public function selectAdminInfo()
    {
        //从请求头啊获取token
        $token =  request()->header('Authorization');
        // return $token;       
        //检查token合法性    
        $res = $this->tokenCheck($token);
        // return gettype($res);
        if ($res['code'] == 2) {
            //知识点:php连接字符串用 . 
            return $this->create('', 'token出错:' . $res['msg'], 304);
        }
        // return 123;
        //知识点:php访问对象属性:$res['data']->uuid
        $admin_info = AdminModel::where('uuid', $res['data']->uuid)->find();
        return $this->create($admin_info);
    }
    /**
     * @description: 发送登录码
     * @param {type} 
     * @return {type} 
     */
    public function sendAdminCode()
    {
        $post =  request()->param();
        //知识点:PHP获得随机数
        $code = rand(111111, 999999);
        //知识点:PHP获取时间戳
        $time = time();
        //拼接时间戳与登录码
        $log_code = $code + $time * 1000000;
        $admin_model = new AdminModel();
        //删除之前的登录码
        $admin_model->deleteLogcode($post['username']);
        //保存登录码信息到临时表
        $res =  $admin_model->saveLogCode($post['username'], $log_code);
        //知识点:PHP类型转换
        $string_code = (string)$log_code;
        //     //知识点:字符串截取指定片段
        //TODO:优化逻辑:$code重复
        $code = substr($string_code, 10, 6);
        //查询账户对应email
        $admin_email = $admin_model->selectMail($post['username']);
        $title = '登录码';
        $content = '你好, <b>' . $post['username'] . '管理员</b>! <br/>这是一封来自河池学院党支部的邮件！<br/><span>你正在登录的管理员账户,你的验证码是:' . (string)$code;

        // $content = '你好, <b>朋友</b>! <br/><br/><span>你的验证码是:' . (string)$code;
        if ($res) {
            if (sendMail($admin_email, $title, $content)) {
                $code = 200;
                $msg = '发送成功';
            } else {
                $code = 204;
                $msg = '发送失败';
            }
        } else {
            $code = 204;
            $msg = '找不到收件人';
        }
        return $this->create('', $msg, $code);
    }
    /**
     * @description: 登录验证
     * @param {type} 
     * @return {type} 
     */
    public function checkAdminLogin()
    {
        //获取请求信息
        $post =  request()->param();
        //实例化模型
        $admin_model = new AdminModel();
        //获取管理员信息
        $admin_info = $admin_model->findAdmin($post['username'], $post['password']);
        //检查是否为空
        if (!empty($admin_info)) {
            //根据管理员uuid查找登录码
            $code_info = Db::table('temp_code')->where('uuid', $admin_info['uuid'])->find();
            if (empty($code_info)) {
                return $this->create('', '验证码错误', 204);
            }
            //截取登录码
            $string_code = (string)$code_info['code'];
            $code = substr($string_code, 10, 6);
            //获取当前时间戳
            $now = time();
            //获取登录码时间戳
            $time = substr($string_code, 0, 10);
            //从数据库删除此登录码
            $delete_res = Db::table('temp_code')->where('uuid', $admin_info['uuid'])->delete();
            //判断登录码是否过期
            if ($time + config("login.code_timeout") <= $now) {
                // return $time + 60;
                return $this->create('', '验证码超时', 201);
            }
            //判断验证码是否一致
            if ($code == $post['logcode']) {
                $token = signToken($admin_info['uuid'], $admin_info['role']);
                $data = [
                    'token' => $token,
                    'uuid' => $admin_info['uuid'],
                    'role' => $admin_info['role']
                ];
                if ($delete_res) {
                    //插入登录记录
                    $records = [
                        'uuid' => $admin_info['uuid'],
                        'login_time' => time(),
                        'login_ip' => request()->host()
                    ];
                    Db::table('login_record')->insert($records);
                    //成功返回token及uuid
                    return $this->create($data, '登录成功');
                } else {
                    return $this->create('', '服务器出现了一个错误', 204);
                }
            } else {
                return $this->create('', '验证码错误', 204);
            }
        } else {
            return $this->create('', '账户或密码错误', 204);
        }
    }

    public function checkPerosnLogin()
    {
        //获取请求信息
        $post =  request()->param();
        //实例化模型
        $pa_model = new PersonAccountModel();
        //验证获取信息
        $person_info = $pa_model->findPersonAccount($post['number'], $post['password']);
        // return json($person_info);

        //根据学号从person表里查询数据
        $person_role = $pa_model->getInfoByNumber($person_info['number'], 'role');
        // $person_name = $pa_model->getInfoByNumber($person_info['number'],'name');

        //检查是否为空
        if (!empty($person_info) && !empty($person_role)) {
            $token = signToken($person_info['number'], $person_role);
            $data = [
                'token' => $token,
                // 'name' =>$person_name,
                'number' => $person_info['number'],
                'role' => $person_role
            ];
            //添加登录记录
            $records = [
                'uuid' => $person_info['number'],
                'login_time' => time(),
                'login_ip' => request()->host()
            ];
            if (Db::table('login_record')->insert($records)) {
                //成功返回token及uuid
                return $this->create($data, '登录成功');
            } else {
                return $this->create('', '未知错误', 204);
            }
        } else {
            return $this->create('', '账户或密码错误', 204);
        }
    }

    public function selectPersonInfo(Request $request)
    {
        $person_model = new PersonModel();
        $res = $request->data;
        // return $this->create($res);
        // return $res;
        // $emp_info = $emp_model->getAcInfo($res['data']->uuid);
        $person_info = $person_model->where('number', $res['data']->uuid)->find();
        $person_info['id_photo'] = Db::table('person_account')->where('number', $res['data']->uuid)->value('id_photo');
        $person_info['profile'] = Db::table('person_account')->where('number', $res['data']->uuid)->value('profile');

        return $this->create($person_info);
    }




    /**
     * @description: 发送登录码
     * @param {type} 
     * @return {type} 
     */
    public function sendHighRolePersonCode()
    {
        $post =  request()->param();
        //PHP获得随机数-验证码
        $v_code = rand(111111, 999999);
        //PHP获取时间戳
        $time = time();
        //拼接时间戳与登录码
        $log_code = (string)$time . (string)$v_code;
        $person_model = new PersonModel();
        $code_model = new TempCodeModel();

        //删除之前的登录码
        $code_model->deleteCode($post['number']);
        //保存登录码信息到临时表
        $res =  $code_model->saveCode($post['number'], $log_code);
        //字符串截取指定片段
        $v_code = substr($log_code, 10, 6);
        //查询账户对应email
        $person_info = $person_model->getAllInfoByNumber($post['number']);

        // $person_email = $person_model->getInfoByNumber($post['number'], 'email');
        $title = '验证码';
        // $data = json_decode($string, true);
        $content=emailHtmlModel($person_info['name'],$v_code,'登录' );
        // return $this->create($content);

        // $content = '你好, <b>' . $person_info['name'] . '</b>管理员! <br/>这是一封来自河池学院党支部的邮件！<br/><span>你正在登录管理员账户,你的验证码是:' . (string)$v_code;

        // $content = '你好, <b>朋友</b>! <br/><br/><span>你的验证码是:' . (string)$code;
        if ($res === true) {
            if (sendMail($person_info['email'], $title, $content)) {
                $code = 200;
                $msg = '发送成功';
            } else {
                $code = 204;
                $msg = '发送失败';
            }
        } else {
            $code = 204;
            $msg = $res;
        }
        return $this->create('', $msg, $code);
    }

    //检查验证码是否正确
    public function checkHighRolePersonCode()
    {
        $post =  request()->param();
        $person_model = new PersonModel();
        $code_model = new TempCodeModel();
        $log_code = $code_model->getCode($post['number']);
        // return json($log_code);
        $v_code = substr($log_code, 10, 6);
        $time = substr($log_code, 0, 10);
        $now = time();
        if ($time + config("login.code_timeout") <= $now) {
            // return $time + 60;
            return $this->create('', '验证码超时', 201);
        }
        $person_info = $person_model->getAllInfoByNumber($post['number']);

        if ($v_code === $post['emailCode']) {
            $token = signToken($person_info['number'], $person_info['role']);
            $data = [
                'token' => $token,
                // 'name' =>$person_name,
                'number' => $person_info['number'],
                'role' => $person_info['role']
            ];
            //添加登录记录
            $records = [
                'uuid' => $person_info['number'],
                'login_time' => time(),
                'login_ip' => request()->host()
            ];
            Db::table('login_record')->insert($records);
            //成功返回token及uuid
            return $this->create($data, '登录成功');
        } else {
            return $this->create('', '验证码错误', 204);
        }
    }


    //over
}
