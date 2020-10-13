<?php
/*
 * @Description: 
 * @Author: 罗曼
 * @Date: 2020-09-17 12:09:09
 * @FilePath: \testd:\wamp64\www\thinkphp-api\app\Model\PersonAccount.php
 * @LastEditTime: 2020-10-05 18:58:09
 * @LastEditors: 罗曼
 */

namespace app\model;

// use PHPExcel_IOFactory;

// use think\Db;
use think\Model;
use think\facade\Db;
use think\model\concern\SoftDelete;

use app\model\Person as PersonModel;



class PersonAccount extends Model
{
    use SoftDelete;
    protected $deleteTime = 'delete_time';

    //TODO添加id_photo存放地址
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
        try {
            $res = $this->where('number', $number)->where('password', $password)->find();
            return $res;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
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
    public function updatePassword($number, $new_password)
    {
        $person =  $this->where('number', $number)->find();
        $person->password = $new_password;
        return $person->save();
    }

    /*********管理员数据操作 */
    //查询person账户
    public function selectPersonAccount($key, $value, $list_rows = 10, $isSimple = false, $config = '')
    {
        switch ($key) {
            case 'number':
                $data = $this->where($key, $value)->paginate($list_rows, $isSimple, $config);
                break;
                // case 'name':
                //     $data = $this->where($key, $value)->paginate($list_rows, $isSimple, $config);
                //     break;
                // case 'goods_name':
                //     $data = $this->whereLike($key, '%' . $value . '%')->paginate($list_rows, $isSimple, $config);
                //     break;
                // case 'shop_name':
                //     $data = $this->whereLike($key, '%' . $value . '%')->paginate($list_rows, $isSimple, $config);
                //     break;
            default:
                $data = $this->paginate($list_rows, $isSimple, $config);
        }
        if (empty($data)) {
            return false;
        } else {
            return $data;
        }
    }

    // 修改人员账户信息
    public function updatePersonAccount($data)
    {
        try {
            $this->update($data);
            return true;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
        // $res = $this->save($data);
    }

    // 修改人员信息
    public function deletePersonAccount($id)
    {

        try {
            //软删除
            $number = $this->where('id', $id)->value('number');
            $this->destroy($id);
            //更新账户激活状态
            $person_model = new PersonModel();
            $person = $person_model->where('number', $number)->find();
            $person->active_state = 0;
            $person->save();
            return true;
        } catch (\Exception $e) {
            return $e->getMessage();
        }

        try {
            //软删除
            $this->destroy($id);
            //真实删除
            // $this->destroy($id,true);
            return true;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
        // $res = $this->save($data);
    }
    /*************** */










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
