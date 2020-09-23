<?php
/*
 * @Author: xch
 * @Date: 2020-08-15 12:01:16
 * @LastEditTime: 2020-09-23 18:57:56
 * @LastEditors: Chenhao Xing
 * @Description: 
 * @FilePath: \epdemoc:\wamp64\www\api-thinkphp\app\Model\EmployeeLogin.php
 */

namespace app\model;

// use PHPExcel_IOFactory;

// use think\Db;
use think\Model;
use think\facade\Db;


class PersonAccount extends Model
{

    //添加id_photo存放地址
    public function insertPersonAccount($post)
    {
        return $this->allowField(['number', 'password', 'profile'])->save($post);
    }

    /**
     * @description: 人员登录验证
     * @param {type} 
     * @return {type} 
     */
    public function findPersonAccount($number, $password)
    {
        return $this->where('number', $number)->where('password', $password)->find();
    }
    /**
     * @description: 根据uuid查询单个信息
     * @param {type} 
     * @return {type} 
     */
    public function getInfoByNumber($number, $value)
    {
        return Db::table('person')->where('number', $number)->value($value);
    }

//更新密码
    public function updatePassword($number,$new_password){
        $person =  $this->where('number',$number)->find();
        $person->password = $new_password;
        return $person->save();
    }










    /**
     * @description: 获取员工资料,分页显示
     * @param {type} 
     * @return {type} 
     */
    public function getEmpAc($list_rows, $isSimple = false, $config)
    {
        $data = $this->paginate($list_rows, $isSimple = false, $config);
        //判断是否有值
        if ($data->isEmpty()) {
            return false;
        } else {
            return $data;
        }
    }
    /**
     * @description: 通过昵称查询
     * @param {type} 
     * @return {type} 
     */
    public function getEmpAcByName($nick_name)
    {
        // if (empty($work_num)) {
        $data = $this->where('nick_name', $nick_name)->select();
        // } else if (empty($real_name)) {
        //     $data = $this->where('work_num', $work_num)->select();
        // } else {
        //     $data = $this->where('work_num', $work_num)->where('real_name', $real_name)->find();
        // }
        if (!$data) {
            return false;
        } else {
            return $data;
        }
    }
    /**
     * @description: 通过权限查询,多个数据,用到分页
     * @param {type} 
     * @return {type} 
     */
    public function getEmpAcByRole($list_rows, $isSimple = false, $config, $role)
    {
        $data = $this->where('role', $role)->paginate($list_rows, $isSimple = false, $config);
        if ($data->isEmpty()) {
            return false;
        } else {
            return $data;
        }
    }

    public function getAcInfo($emp_uuid)
    {
        return $this->where('uuid', $emp_uuid)->select();
    }

    public function insertEmpAc($post)
    {
        return $this->allowField(['nick_name', 'password', 'profile', 'uuid'])->save($post);
    }
}
