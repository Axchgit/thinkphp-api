<?php
/*
 * @Author: 罗曼
 * @Date: 2020-08-17 22:03:01
 * @LastEditTime: 2020-11-15 02:03:22
 * @LastEditors: 罗曼
 * @FilePath: \testd:\wamp64\www\thinkphp-api\app\controller\Admin.php
 * @Description: 
 */

declare(strict_types=1);

namespace app\controller;

use think\Request;

use app\model\Employee as EmployeeModel;
use app\model\EmployeeLogin as EmpLoginModel;
use app\model\Performance as PerformanceModel;

use app\model\Person as PersonModel;
use app\model\PersonAccount as PersonAccountModel;
use app\model\JoinApply as JoinApplyModel;
use app\model\RecruitPartyMember as RecruitPartyMemberModel;


use think\facade\Db;

class Admin extends Base
{
    public function importExcel()
    {
        $post =  request()->param();
        $person_model = new PersonModel();
        // return $this->create($post, '90', 204);

        $res = $person_model->insertPerson($post);
        if ($res === true) {
            return $this->create('成功', '添加成功', 200);
        } else {
            return $this->create('', $res, 200);
        }
    }
    /***********人员信息 */
    //查询人员信息
    public function selectPerson()
    {
        $post =  request()->param();
        // $res = $request->data;
        $person_model = new PersonModel();
        $val = !empty($post['number']) || !empty($post['name']);
        $key = '';
        $value = '';
        // return $val;
        if ($val) {
            $key = !empty($post['number']) ? 'number' : 'name';
            $value = urldecode($post[$key]);
            // return $key;
        }
        // $key = !empty($post['key']) ? $post['key'] : '';
        // $value = !empty($post['value']) ? $post['value'] : '';
        $list_rows = !empty($post['list_rows']) ? $post['list_rows'] : '';
        $data = $person_model->selectPerson($key, $value, $list_rows, false, ['query' => $post]);
        if ($data) {
            return $this->create($data, '查询成功');
        } else {
            return $this->create($data, '暂无数据', 204);
        }
    }

    //修改人员信息
    public function updatePerson()
    {
        $post =  request()->param();
        $person_model = new PersonModel();
        $res = $person_model->updatePerson($post);
        if ($res === true) {
            return $this->create('', '修改成功', 200);
        } else {
            return $this->create('', $res, 204);
        }
    }

    //修改人员信息
    public function deletePerson()
    {
        $post =  request()->param();
        $person_model = new PersonModel();
        $res = $person_model->deletePerson($post['id']);
        if ($res === true) {
            return $this->create('', '人员信息删除成功', 200);
        } else {
            return $this->create('', $res, 204);
        }
    }

    /***********人员账户信息 */
    //查询人员账户
    public function selectPersonAccount()
    {
        $post =  request()->param();
        // $res = $request->data;
        $person_model = new PersonModel();
        $pa_model = new PersonAccountModel();
        $val = !empty($post['number']) || !empty($post['name']);
        $key = '';
        $value = '';
        // return $val;
        if ($val) {
            $key = !empty($post['number']) ? 'number' : 'name';
            $value = urldecode($post[$key]);
            // return $key;
        }
        // $key = !empty($post['key']) ? $post['key'] : '';
        // $value = !empty($post['value']) ? $post['value'] : '';
        $list_rows = !empty($post['list_rows']) ? $post['list_rows'] : '';
        $resArr = $pa_model->selectPersonAccount($key, $value, $list_rows, false, ['query' => $post]);
        foreach ($resArr as $k => $v) {
            $person_info = $person_model->getAllInfoByNumber($v['number']);
            $resArr[$k]['name'] = $person_info['name'];
            $resArr[$k]['role'] = $person_info['role'];
            $resArr[$k]['post'] = $person_info['post'];
            $resArr[$k]['phone_number'] = $person_info['phone_number'];
            $resArr[$k]['comment'] = $person_info['comment'];
        }
        if ($resArr) {
            return $this->create($resArr, '查询成功');
        } else {
            return $this->create('', '暂无数据', 204);
        }
    }

    //修改人员账户
    public function updatePersonAccount()
    {
        $post =  request()->param();
        $pa_model = new PersonAccountModel();
        $res = $pa_model->updatePersonAccount($post);
        if ($res === true) {
            return $this->create('', '修改成功', 200);
        } else {
            return $this->create('', $res, 204);
        }
    }

    //删除人员账户
    public function deletePersonAccount()
    {
        $post =  request()->param();
        $pa_model = new PersonAccountModel();
        $res = $pa_model->deletePersonAccount($post['id']);
        if ($res === true) {
            return $this->create('', '人员信息删除成功', 200);
        } else {
            return $this->create('', $res, 204);
        }
    }
    /*************** */

    //一二级管理员浏览人员信息
    public function viewAllPerson(Request $request)
    {
        $post = request()->param();

        // return json($post);
        $tooken_res = $request->data;
        $number = $tooken_res['data']->uuid;
        $role = $tooken_res['data']->role;
        $person_model = new PersonModel();
        //权限为4需要加查询条件
        $faculty = '';
        if ($role === 4) {
            $faculty = $person_model->getInfoByNumber($number, 'faculty');
        }
        if (!empty($post['faculty'])) {
            $post['faculty'] = strlen($post['faculty']) > 1 ? (string)$post['faculty'] : '0' . (string)$post['faculty'];
        }
        $list_rows = !empty($post['list_rows']) ? $post['list_rows'] : '';
        $list = $person_model->getAllPerson($list_rows, ['query' => $post], $faculty, $post);
        // if ($list) {
        return $this->create($list, '查询成功');
        // } else {
        //     return $this->create($list, '暂无数据');
        // }
    }

    //一二级管理员浏览人员账户信息
    public function viewAllPersonAccount(Request $request)
    {
        $post = request()->param();

        // return json($post);
        $tooken_res = $request->data;
        $number = $tooken_res['data']->uuid;
        $role = $tooken_res['data']->role;

        $person_model = new PersonModel();
        $pa_model = new PersonAccountModel();
        //权限为4需要加查询条件
        $faculty = '';
        if ($role === 4) {
            $faculty = $person_model->getInfoByNumber($number, 'faculty');
        }
        $list_rows = !empty($post['list_rows']) ? $post['list_rows'] : '';
        $list = $pa_model->getAllPersonAccount($list_rows,  ['query' => $post], $faculty, $post);
        // if ($list) {
        return $this->create($list, '查询成功');
        // } else {
        //     return $this->create($list, '暂无数据');
        // }
    }

    //浏览查询申请列表
    public function viewApply(Request $request)
    {
        $post = request()->param();

        // return json($post);
        $tooken_res = $request->data;
        $number = $tooken_res['data']->uuid;
        $role = $tooken_res['data']->role;
        $person_model = new PersonModel();
        $ja_model = new JoinApplyModel();
        //权限为4需要加查询条件
        $faculty = '';
        if ($role === 4) {
            $faculty = $person_model->getInfoByNumber($number, 'faculty');
        }
        if (!empty($post['faculty'])) {
            $post['faculty'] = strlen($post['faculty']) > 1 ? (string)$post['faculty'] : '0' . (string)$post['faculty'];
        }
        $list_rows = !empty($post['list_rows']) ? $post['list_rows'] : '';
        $list = $ja_model->getAllApply($list_rows, ['query' => $post], $faculty, $post, $role);
        return $this->create($list, '查询成功');
    }

    //审核申请
    public function reviewApply(Request $request)
    {
        $tooken_res = $request->data;
        $number = $tooken_res['data']->uuid;
        $post = request()->param();
        // $person_model = new PersonModel();
        $ja_model = new JoinApplyModel();
        $rpm_model = new RecruitPartyMemberModel();


        $post['reviewer'] = $number;
        $post['remarks'] = '';
        $ja_res = $ja_model->updateJoinApply($post);
        // $post['stage'] = $post['step'] == 1 ? 1 : $post['step'] + 2;

        switch ($post['step']) {
            case 1:
                $post['stage'] = 1;
                $rpm_res = $rpm_model->createRecruit($post);
                if ($rpm_res === true) {
                    $post['stage'] = 2;
                    $post['stage_time'] = date("Y-m-d H:i:s", strtotime("+3 month"));
                    $rpm_res = $rpm_model->createRecruit($post);
                }
                break;
            case 2:
                $post['stage'] = 4;
                $rpm_res = $rpm_model->createRecruit($post);
                if ($rpm_res === true) {
                    $post = array_diff_key($post, ["contacts" => 0, "introducer" => 0]);
                    $post['stage'] = 3;
                    $post['stage_time'] = date("Y-m-d H:i:s", strtotime("-15 day"));
                    $rpm_res = $rpm_model->createRecruit($post);
                }
                break;
            case 3:
                $post['stage'] = 5;
                $rpm_res = $rpm_model->createRecruit($post);
                break;
            case 4:
                $post['stage'] = 6;
                $rpm_res = $rpm_model->createRecruit($post);
                if ($rpm_res === true) {
                    $post['stage'] = 7;
                    $post['stage_time'] = date("Y-m-d H:i:s", strtotime("+7 day"));
                    $rpm_res = $rpm_model->createRecruit($post);
                }
                break;
            case 5:
                $post['stage'] = 8;
                $rpm_res = $rpm_model->createRecruit($post);
                if ($rpm_res === true) {
                    $post['stage'] = 9;
                    $post['stage_time'] = date("Y-m-d H:i:s", strtotime("+7 day"));
                    $rpm_res = $rpm_model->createRecruit($post);
                }
                # code...
                break;
        }

        //当审核未通过时,删除发展党员信息
        if ($post['review_status'] != 2) {
            $rpm_res = $rpm_model->deleteRecruit([['number', '=', $post['number']], ['stage', '>=', $post['stage']]]);
            return $this->create($post, $rpm_res);
        }



        if ($ja_res === true && $rpm_res === true) {
            return $this->create($post,  '审核成功');
        } else {
            return $this->create($post, ['审核失败', $ja_res, $rpm_res], 204);
        }
    }

    //浏览查询申请列表
    public function viewRecruit(Request $request)
    {
        $post = request()->param();

        // return json($post);
        $tooken_res = $request->data;
        $number = $tooken_res['data']->uuid;
        $role = $tooken_res['data']->role;
        $person_model = new PersonModel();
        $rpm_model = new RecruitPartyMemberModel();

        //权限为4需要加查询条件
        $faculty = '';
        if ($role === 4) {
            $faculty = $person_model->getInfoByNumber($number, 'faculty');
        }
        $list_rows = !empty($post['list_rows']) ? $post['list_rows'] : '';
        $list = $rpm_model->getRecruit($list_rows, ['query' => $post], $faculty, $post, $role);
        return $this->create($list, '查询成功');
    }
















    //首页charts数据
    public function getCount(Request $request)
    {
        $tooken_res = $request->data;
        $number = $tooken_res['data']->uuid;
        $role = $tooken_res['data']->role;

        $person_model = new PersonModel();
        $ja_model = new JoinApplyModel();
        $faculty = $role <= 3 ? null : $person_model->getInfoByNumber($number, 'faculty');
        $apply_person_count = $ja_model->getPersonCount($faculty);
        $reviewing_count = $ja_model->getReviewCount(1, $faculty);
        $reviewed_count = $ja_model->getReviewCount(2, $faculty);
        $no_reviewed_count = $ja_model->getReviewCount(3, $faculty);

        $list = [
            'applyPersonCount' => $apply_person_count,
            'reviewingCount' => $reviewing_count,
            'reviewedCount' => $reviewed_count,
            'noReviewedCount' => $no_reviewed_count,
            // 'headerCounts'=>[$apply_person_count,$reviewing_count,$reviewed_count,$no_reviewed_count]
        ];
        return $this->create($list, '查询成功');
    }































    //TODO:删除
    /************************************* */


    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function selectAll()
    {
        $post = request()->param();
        $emp_model = new EmployeeModel();
        $list = $emp_model->getEmpInfo($post['list_rows'], '', ['query' => $post]);
        if ($list) {
            return $this->create($list, '查询成功');
        } else {
            return $this->create($list, '暂无数据', 204);
        }
    }
    /**
     * @description: 通过工号查询员工数据
     * @param {type} 
     * @return {type} 
     */
    public function selectByInfo($work_num = '', $real_name = '')
    {
        $emp_model = new EmployeeModel();
        $data = $emp_model->getEmpByWrokNum($work_num, $real_name);
        $list = [
            'data' => $data
        ];
        if ($list) {
            return $this->create($list, '查询成功');
        } else {
            return $this->create($list, '暂无数据', 204);
        }
    }
    /**
     * @description: 通过权限等级查询员工数据
     * @param {type} 
     * @return {type} 
     */
    public function selectByRole()
    {
        $post = request()->param();
        $emp_model = new EmployeeModel();
        $data = $emp_model->getEmpByRole($post['list_rows'], '', ['query' => $post], $post['role']);
        if ($data) {
            return $this->create($data, '查询成功');
        } else {
            return $this->create($data, '暂无数据', 204);
        }
    }


    /***************** 员工账户信息 ********************/


    /**
     * @description: 获取员工资料信息
     * @param {type} 
     * @return {type} 
     */
    public function selectAcAll()
    {
        $post = request()->param();
        $emplogin_model = new EmpLoginModel();
        $list = $emplogin_model->getEmpAc($post['list_rows'], '', ['query' => $post]);
        if ($list) {
            return $this->create($list, '查询成功');
        } else {
            return $this->create($list, '暂无数据', 204);
        }
    }
    /**
     * @description: 通过昵称查询员工数据
     * @param {type} 
     * @return {type} 
     */
    public function selectAcByName($nick_name = '')
    {
        $emplogin_model = new EmpLoginModel();
        $data = $emplogin_model->getEmpAcByName($nick_name);
        // return $data;
        $list = [
            'data' => $data
        ];
        if ($data) {
            return $this->create($list, '查询成功');
        } else {
            return $this->create($list, '暂无数据', 204);
        }
    }
    /**
     * @description: 通过权限等级查询员工资料
     * @param {type} 
     * @return {type} 
     */
    public function selectAcByRole()
    {
        $post = request()->param();
        $emplogin_model = new EmpLoginModel();
        $data = $emplogin_model->getEmpAcByRole($post['list_rows'], '', ['query' => $post], $post['role']);
        if ($data) {
            return $this->create($data, '查询成功');
        } else {
            return $this->create($data, '暂无数据', 204);
        }
    }
    //忘记密码-发送验证码
    public function sendRecoverCode()
    {
        $post = request()->param();
        $emp_model = new EmployeeModel();
        $emp_model->deleteEmpCode($post['work_num']);
        //验证码
        $code = rand(111111, 999999);
        $time = time();
        $time_code = (string)$time . (string)$code;
        //邮箱内容
        $title = '验证码';
        $content = '你好, <b>朋友</b>! <br/>这是一封来自<a href="http://www.xchtzon.top"  
            target="_blank">学创科技</a>的邮件！<br/><span>你正在修改你的密码,你的验证码是:' . (string)$code;
        $res = $emp_model->where('work_num', $post['work_num'])->where('email', $post['email'])->find();
        if (!empty($res)) {

            $res = $emp_model->saveEmpCode($post['work_num'], $time_code, $title);
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
        $emp_uuid = EmployeeModel::where('work_num', $post['work_num'])->value('uuid');
        $code_info = Db::table('temp_code')->where('uuid', $emp_uuid)->find();

        $string_code = (string)$code_info['code'];
        $code = substr($string_code, 10, 6);
        //获取当前时间戳
        $now = time();
        //获取登录码时间戳
        $time = substr($string_code, 0, 10);
        if ($code == $post['code']) {
            if ($time + config("login.code_timeout") >= $now) {
                return $this->create(['uuid' => $emp_uuid], '成功', 200);
            } else {
                return $this->create('', '验证码超时', 201);
            }
        } else {
            return $this->create('', '验证码错误', 204);
        }
    }
    //忘记密码-修改
    public function updateAcPW()
    {
        $post = request()->param();
        $emp_login = new EmpLoginModel();
        $res = $emp_login->updatePW($post['uuid'], $post['password']);
        if ($res) {
            return $this->create('', '成功', 200);
        } else {
            return $this->create('', '修改失败', 204);
        }
    }
    //激活账号验证码
    public function sendActivateCode()
    {
        $post = request()->param();
        $emp_model = new EmployeeModel();
        $emp_model->deleteEmpCode($post['work_num']);
        $emp_uuid = $emp_model->where('work_num', $post['work_num'])->where('email', $post['email'])->value('uuid');
        $code = rand(111111, 999999);
        $time = time();
        $time_code = (string)$time . (string)$code;
        //邮箱信息
        $title = '验证码';
        $content = '你好, <b>朋友</b>! <br/>这是一封来自<a href="http://www.xchtzon.top"  
            target="_blank">学创科技</a>的邮件！<br/><span>你正在激活你的员工账户,你的验证码是:' . (string)$code;
        if (!empty($emp_uuid)) {
            $res = $emp_model->saveEmpCode($post['work_num'], $time_code, $title);
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
            return $this->create(['uuid' => $emp_uuid], $msg, $code);
        } else {
            return $this->create('', '用户信息有误', 204);
        }
    }
    //激活账号
    public function createEmpAc()
    {
        $post = request()->param();
        $emp_login = new EmpLoginModel();
        //验证码
        $code_info = Db::table('temp_code')->where('uuid', $post['uuid'])->find();
        $string_code = (string)$code_info['code'];
        $code = substr($string_code, 10, 6);
        //获取当前时间戳
        $now = time();
        //获取登录码时间戳
        $time = substr($string_code, 0, 10);
        if ($code == $post['code']) {
            if ($time + config("login.code_timeout") >= $now) {
                $res = $emp_login->insertEmpAc($post);
                $update_res = EmployeeModel::where('uuid', $post['uuid'])->save(['review_status' => 1]);
                if ($res && $update_res) {
                    return $this->create('', '激活成功', 200);
                } else {
                    return $this->create('', '激活失败,未知错误', 204);
                }
            } else {
                return $this->create('', '验证码超时', 201);
            }
        } else {
            return $this->create('', '验证码', 201);
        }
    }

    //提交业绩
    public function submitPerformanc(Request $request)
    {
        $post = request()->param();
        $res = $request->data;
        $performance_model = new PerformanceModel();
        $res = $performance_model->insertPerformance($res['data']->uuid, $post['goods_id']);
        if ($res === true) {
            return $this->create('', '添加成功', 200);
        } else {
            return $this->create($res, '添加失败', 204);
        }
    }

    //员工查询个人业绩
    public function selectPerformanceByUuid(Request $request)
    {
        $post =  request()->param();
        $res = $request->data;
        $per_model = new PerformanceModel();
        $key = !empty($post['key']) ? $post['key'] : '';
        $value = !empty($post['value']) ? $post['value'] : '';
        $list_rows = !empty($post['list_rows']) ? $post['list_rows'] : '';
        $data = $per_model->selectPerformance($res['data']->uuid, $key, $value, $list_rows, false, ['query' => $post]);
        if ($data) {
            return $this->create($data, '查询成功');
        } else {
            return $this->create($data, '暂无数据', 204);
        }
    }

    //员工删除个人业绩
    public function deletePerformanceByUuuid(Request $request)
    {
        $post =  request()->param();
        $res = $request->data;
        $per_model = new PerformanceModel();
        $res = $per_model->softDeletePerformance($res['data']->uuid, $post['id']);
        if ($res === true) {
            return $this->create('', '删除成功', 200);
        } else {
            return $this->create($res, '删除失败', 204);
        }
    }

    //员工查询个人推广商品
    public function selectPerformanceGoodsByUuid(Request $request)
    {
        $post =  request()->param();
        $res = $request->data;
        $per_model = new PerformanceModel();
        $key = !empty($post['key']) ? $post['key'] : '';
        $value = !empty($post['value']) ? $post['value'] : '';
        $list_rows = !empty($post['list_rows']) ? $post['list_rows'] : '';
        $data = $per_model->selectPerformanceGoods($res['data']->uuid, $key, $value, $list_rows, false, ['query' => $post]);
        if ($data) {
            return $this->create($data, '查询成功');
        } else {
            return $this->create($data, '暂无数据', 204);
        }
    }









    //结束
}
