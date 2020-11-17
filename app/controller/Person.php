<?php
/*
 * @Description: 
 * @Author: 罗曼
 * @Date: 2020-09-12 02:32:00
 * @FilePath: \testd:\wamp64\www\thinkphp-api\app\controller\Person.php
 * @LastEditTime: 2020-11-18 00:02:00
 * @LastEditors: 罗曼
 */


declare(strict_types=1);

namespace app\controller;

use think\Request;

use app\model\Person as PersonModel;
use app\model\PersonAccount as PersonAccountModel;
use app\model\Material as MaterialModel;
use app\model\RecruitPartyMember as RecruitPartyMemberModel;
use app\model\JoinApply as JoinApplyModel;



use app\model\Employee as EmployeeModel;
use app\model\EmployeeLogin as EmpLoginModel;
use app\model\Performance as PerformanceModel;


use think\facade\Db;

class Person extends Base
{

    /************激活账号****** */
    //激活账号验证码
    public function sendPersonActivateCode()
    {
        $post = request()->param();
        $person_model = new PersonModel();
        $person_model->deletePersonCode($post['number']);
        //知识点:判断账户是否存在且未激活
        $person_name = $person_model->where('number', $post['number'])->where('active_state', 0)->where('email', $post['email'])->value('name');
        $code = rand(111111, 999999);
        $time = time();
        $time_code = (string)$time . (string)$code;
        //邮箱信息
        $title = '验证码';
        $content = '你好, <b>' . $person_name . '同志</b>! <br/>这是一封来自河池学院党支部的邮件！<br/><span>你正在激活你的入党申请账户,你的验证码是:' . (string)$code;
        if (!empty($person_name)) {
            $res = $person_model->savePersonCode($post['number'], $time_code, $title);
            if ($res) {
                if (sendMail($post['email'], $title, $content)) {
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
            return $this->create($code, $msg, $code);
        } else {
            return $this->create('', '个人信息有误或账号已激活', 204);
        }
    }
    //激活账号
    public function createPersonAccount()
    {
        $post = request()->param();
        $pa_login = new PersonAccountModel();
        //验证码
        $code_info = Db::table('temp_code')->where('uuid', $post['number'])->find();
        $string_code = (string)$code_info['code'];
        $code = substr($string_code, 10, 6);
        //获取当前时间戳
        $now = time();
        //获取登录码时间戳
        $time = substr($string_code, 0, 10);
        if ($code == $post['code']) {
            if ($time + config("login.code_timeout") >= $now) {
                $res = $pa_login->insertPersonAccount($post);
                $update_res = PersonModel::where('number', $post['number'])->save(['active_state' => 1]);
                if ($res && $update_res) {
                    return $this->create('', '激活成功', 200);
                } else {
                    return $this->create('', '激活失败,账户已存在或服务器错误', 204);
                }
            } else {
                return $this->create('', '验证码超时', 201);
            }
        } else {
            return $this->create('', '验证码错误', 201);
        }
    }
    /****************************** */

    /************找回密码*********** */
    //忘记密码-发送验证码
    public function sendRecoverCode()
    {
        $post = request()->param();
        $person_model = new PersonModel();
        $person_model->deletePersonCode($post['number']);
        $person_name = $person_model->where('number', $post['number'])->where('email', $post['email'])->value('name');
        //验证码
        $code = rand(111111, 999999);
        $time = time();
        $time_code = (string)$time . (string)$code;
        //邮箱内容
        $title = '验证码';
        $content = '你好, <b>' . $person_name . '同志</b>! <br/>这是一封来自河池学院党支部的邮件！<br/><span>你正在找回你的入党申请账户密码,你的验证码是:' . (string)$code;
        $res = $person_model->where('number', $post['number'])->where('email', $post['email'])->find();
        if (!empty($res)) {
            $res = $person_model->savePersonCode($post['number'], $time_code, $title);
            if ($res) {
                if (sendMail($post['email'], $title, $content)) {
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
        } else {
            return $this->create('', '用户信息有误', 204);
        }
    }

    //忘记密码-检查信息
    public function checkRecover()
    {
        $post = request()->param();
        // $number = PersonModel::where('number', $post['work_num'])->value('uuid');
        $code_info = Db::table('temp_code')->where('uuid', $post['number'])->find();

        $string_code = (string)$code_info['code'];
        $code = substr($string_code, 10, 6);
        //获取当前时间戳
        $now = time();
        //获取登录码时间戳
        $time = substr($string_code, 0, 10);
        if ($code == $post['code']) {
            if ($time + config("login.code_timeout") >= $now) {
                return $this->create(['number' => $post['number']], '成功', 200);
            } else {
                return $this->create('', '验证码超时', 201);
            }
        } else {
            return $this->create('', '验证码错误', 204);
        }
    }
    //忘记密码-修改
    public function updatePassword()
    {
        $post = request()->param();
        $pa_model = new PersonAccountModel();
        $res = $pa_model->updatePassword($post['number'], $post['password']);
        if ($res) {
            return $this->create('', '成功', 200);
        } else {
            return $this->create('', '修改失败', 204);
        }
    }
    /**************************** */

    /**************************** 文件上传*/
    public function submitApplicatioin(Request $request)
    {
        $post = request()->param();
        // return $this->create('', $post, 204);
        $file = request()->file('file');
        $branch_value = request()->header('partyBranch');
        $tooken_res = $request->data;
        $number = $tooken_res['data']->uuid;
        $person_model = new PersonModel();
        $material_model = new MaterialModel();
        $ja_model = new JoinApplyModel();
        $rpm_model = new RecruitPartyMemberModel();
        //TODO:用户提交申请验证合法性
        if (!empty($post['step'])) {
            switch ($post['step']) {
                case 2:
                    // if ($rpm_model->getIsExceedNow($number)) {
                    //     return $this->create('', '请注意申请时间', 204);
                    // }
                    break;

                default:
                    # code...
                    break;
            }
            if ($rpm_model->getIsExceedNow($number)) {
                return $this->create(['isExceed'=>true], '请注意申请时间', 200);
            }
            $res_ja = $ja_model->addApply($number, $post['step']);
            $res_update = true;
            $res_mat = true;
        } else {
            try {
                validate(['file' => ['filesize:512000', 'fileExt:doc,docx']])
                    ->check(['file' => $file]);
                $savename = \think\facade\Filesystem::disk('public')->putFileAs('application', $file, (string)$number . '-入党申请书' . '.' . $file->getOriginalExtension());
                //添加申请书路径到数据库
                $res_mat = $material_model->addMaterial($number, 4, '', '', $savename);
                //修改人员党支部数据
                $res_update = $person_model->updateByNumber($number, ['party_branch' => $branch_value]);
                //添加申请信息
                $res_ja = $ja_model->addApply($number, 1);
                // return $this->create($savename, '上传成功', 200);
            } catch (\think\exception\ValidateException $e) {
                return $this->create('', $e->getMessage(), 204);
            }
        }

        if ($res_mat === true && $res_update === true && $res_ja === true) {
            return $this->create('', '申请成功', 200);
        } else {
            return $this->create('', [$res_mat, $res_update, $res_ja], 204);
        }
    }
    /**************************** */
    //获取所在学院党支部列表
    public function getPartyBranch(Request $request)
    {
        //获取token中的学号
        $tooken_res = $request->data;
        $number = $tooken_res['data']->uuid;

        $person_model = new PersonModel();
        //根据学号获取学院代码
        $faculty = $person_model->getInfoByNumber($number, 'faculty');
        $fileName = config('app.json_path') . '/options.json';
        $string = file_get_contents($fileName);
        $data = json_decode($string, true);
        // 知识点:PHP数组查询
        $found_arr = array_column($data, 'value');
        // var_dump($found_arr)
        $found_key = array_search($faculty, $found_arr);
        // $found_key = 0; 返回键名
        return $this->create($data[$found_key], '', 200);
        // return $data[$found_key];
    }
    //判断申请书是否提交
    // public function getIsOneStep(Request $request)
    // {
    //     $tooken_res = $request->data;
    //     $number = $tooken_res['data']->uuid;

    //     $material_model = new MaterialModel();
    //     $res = $material_model->selectInfoByNumber($number, 4);
    //     if ($res !== null) {
    //         return $this->create(['code' => 1], '', 200);
    //     } else {
    //         return $this->create(['code' => 2], '', 200);
    //     }
    // }
    //查询申请进程
    public function getApplyStep(Request $request)
    {
        $tooken_res = $request->data;
        $number = $tooken_res['data']->uuid;
        $ja_model = new JoinApplyModel();
        $res = $ja_model->selectApplyStep($number);
        if ($res !== null) {
            return $this->create(['step' => $res->step, 'review_status' => $res->review_status], '', 200);
        } else {
            return $this->create(['step' => 0, 'review_status' => 1], '', 200);
        }
    }






    //结束
}
