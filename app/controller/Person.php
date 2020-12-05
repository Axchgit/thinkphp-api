<?php
/*
 * @Description: 
 * @Author: 罗曼
 * @Date: 2020-09-12 02:32:00
 * @FilePath: \testd:\wamp64\www\thinkphp-api\app\controller\Person.php
 * @LastEditTime: 2020-12-05 15:26:29
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
use app\model\Transfer as TransferModel;
use app\model\Bulletin as BullteinModel;
use app\model\BulletinRead as BullteinReadModel;

use app\model\TempCode as TempCodeModel;








use think\facade\Db;

class Person extends Base
{

    public function getProfile(Request $request)
    {
        //获取token中的学号
        $tooken_res = $request->data;
        $number = $tooken_res['data']->uuid;

        $person_model = new PersonModel();
        $data = $person_model->getAllInfoByNumber($number);
        if (!empty($data)) {
            $list['转出人姓名'] = $data['name'];
            $list['转出人身份证号码'] = $data['id_card'];
            $list['转出人手机号'] = $data['phone_number'];
            $list['转出人学院'] = $person_model->getJsonData('options.json', $data['faculty']);
            $list['转出人专业'] = $data['major'];
            $list['转出人班级'] = $data['class'];
            $list['转出人性别'] = $data['sex'] === 1 ? '男' : '女';
            if ($data['party_branch'] == 0) {
                $list['转出团支部'] = '未选择';
            } else {
                $list['转出党支部'] = $person_model->getJsonData('options.json', $data['faculty'], $data['party_branch'], true);
            }
            $list['转出人职务'] = $data['post'];
            $list['转出人学历'] = $data['education'];
            $list['转出人党支部管理员'] = $person_model->getInfoBySelectPost(['role' => 4], ['faculty' => $data['faculty']])['name'];
            $list['管理员联系方式'] = $person_model->getInfoBySelectPost(['role' => 4], ['faculty' => $data['faculty']])['phone_number'];
            return $this->create($list, '获取成功', 200);
        }

        return $this->create('', '获取信息失败', 204);
    }

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
        $content = emailHtmlModel($person_name, (string)$code, '激活账号', '同志');

        // $content = '你好, <b>' . $person_name . '同志</b>! <br/>这是一封来自河池学院党支部的邮件！<br/><span>你正在激活你的入党申请账户,你的验证码是:' . (string)$code;
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
        $content = emailHtmlModel($person_name, (string)$code, '激活账号', '同志');
        // $content = '你好, <b>' . $person_name . '同志</b>! <br/>这是一封来自河池学院党支部的邮件！<br/><span>你正在找回你的入党申请账户密码,你的验证码是:' . (string)$code;
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
    /*****账户信息 */
    public function updatePasswordByEmailCode(Request $request)
    {
        $post = request()->param();
        $tooken_res = $request->data;
        $number = $tooken_res['data']->uuid;
        // $number = PersonModel::where('number', $post['work_num'])->value('uuid');
        $tc_model = new TempCodeModel();
        $pa_model = new PersonAccountModel();

        $temp_code = $tc_model->getCodeByNumber($number);
        if ($temp_code === false) {
            return $this->create('', '系统错误', 204);
        }

        $string_code = (string)$temp_code;
        $code = substr($string_code, 10, 6);
        //获取当前时间戳
        $now = time();
        //获取登录码时间戳
        $time = substr($string_code, 0, 10);
        if ($code == $post['email_code']) {
            if ($time + config("login.code_timeout") >= $now) {
                $res = $pa_model->updatePassword($number, $post['password']);
                if ($res) {
                    return $this->create('', '成功');
                } else {
                    return $this->create('', '修改失败', 204);
                }
                // return $this->create(['number' => $post['number']], '成功', 200);
            } else {
                return $this->create('', '验证码超时', 201);
            }
        } else {
            return $this->create('', '验证码错误', 204);
        }
    }
    //修改个人简介
    public function changeProfileByToken(Request $request)
    {
        $post = request()->param();
        $tooken_res = $request->data;
        $number = $tooken_res['data']->uuid;

        $pa_model = new PersonAccountModel();

        $res = $pa_model->updateByNumber($number, ['profile' => $post['profile']]);
        if ($res === true) {
            return $this->create('', '修改成功');
        } else {
            return $this->create('', '修改失败', 204);
        }
    }
    /************ */

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
                return $this->create(['isExceed' => true], '请注意申请时间', 200);
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
    /*********组织关系转接 */
    //组织关系转接申请
    public function submitTransferApply(Request $request)
    {

        $post = request()->param();
        $tooken_res = $request->data;
        $number = $tooken_res['data']->uuid;
        $transfer_model = new TransferModel();
        $person_model = new PersonModel();
        $person_info = $person_model->getAllInfoByNumber($number);

        // $post['number']=$number;
        // $post['receive_faculty'] = substr($post['receive_organization'],0,2);
        $add_data = [
            'number' => $number,
            'receive_faculty' => substr($post['receive_organization'], 0, 2),
            'leave_faculty' => $person_info['faculty'],
            'leave_major' => $person_info['major'],
            'leave_organization' => $person_info['party_branch']
        ];
        $post = array_merge($post, $add_data);
        $res = $transfer_model->createApply($post);
        if ($res) {
            return $this->create(['code' => 1], '提交成功', 200);
        } else {
            return $this->create(['code' => 0], '提交失败', 204);
        }
    }
    //获取申请进度
    public function getTransferApplyStep(Request $request)
    {
        $tooken_res = $request->data;
        $number = $tooken_res['data']->uuid;
        $transfer_model = new TransferModel();
        $data = $transfer_model->selectApplyStep($number);

        if (empty($data)) {
            // return $data;

            return $this->create(['code' => 1, 'review_steps' => 0, 'review_status' => 1], '查询成功', 200);
        }
        return $data;
    }
    //个人浏览历史转接信息
    public function viewHistoryTransferApply(Request $request)
    {
        $tooken_res = $request->data;
        $number = $tooken_res['data']->uuid;
        $transfer_model = new TransferModel();
        $list = $transfer_model->getHistroyByNumber($number);
        return $this->create($list, '查询成功');
    }

    /********* */


    /************通告 */

    //获取通告
    public function viewBulletin(Request $request)
    {
        $post = request()->param();
        $tooken_res = $request->data;
        $number = $tooken_res['data']->uuid;
        $person_model = new PersonModel();
        $bulletin_model = new BullteinModel();

        $info_post = $person_model->getInfoByNumber($number, 'post');
        $list_rows = !empty($post['list_rows']) ? $post['list_rows'] : '';
        $list = $bulletin_model->getBulletin($list_rows, ['query' => $post], $info_post, $number);
        return $list;
    }

    //阅读公告
    public function readBulletin(Request $request)
    {
        $post = request()->param();

        $tooken_res = $request->data;
        $number = $tooken_res['data']->uuid;
        $br_model = new BullteinReadModel();

        $post['target_number'] = $number;

        $res = $br_model->createBulletinRead($post);
        if ($res === true) {
            return $this->create('', '阅读成功');
        }
        return $this->create($res, '阅读失败');
    }


    /************* */






    //结束
}
