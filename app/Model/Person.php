<?php
/*
 * @Author: 罗曼
 * @Date: 2020-08-15 12:01:16
 * @LastEditTime: 2020-11-10 20:38:00
 * @LastEditors: 罗曼
 * @Description: 员工信息
 * @FilePath: \testd:\wamp64\www\thinkphp-api\app\Model\Person.php
 */

namespace app\model;

// use PHPExcel_IOFactory;

// use think\Db;
use think\Model;
use think\facade\Db;
use app\model\PersonTemp as PersonTempModel;
use think\model\concern\SoftDelete;


class Person extends Model

{
    //软删除
    use SoftDelete;
    protected $deleteTime = 'delete_time';
    //插入信息
    public function insertPerson($dataArr)
    {
        $pt_mode = new PersonTempModel();
        $person = [];
        foreach ($dataArr as $k => $v) {
            $person[$k]['number'] = $v['学工号'];
            $person[$k]['faculty'] = $v['学院'];
            $person[$k]['major'] = $v['专业'];
            $person[$k]['grade'] = $v['年级'];
            $person[$k]['class'] = $v['班级'];
            $person[$k]['name'] = $v['姓名'];
            $person[$k]['sex'] = $v['性别'] === '男' ? 1 : 2;
            $person[$k]['nation'] = $v['民族'];
            $person[$k]['id_card'] = $v['身份证号'];
            $person[$k]['education'] = $v['学历'];
            $person[$k]['post'] = $v['职务'];
            $person[$k]['phone_number'] = $v['手机号'];
            $person[$k]['email'] = $v['邮箱'];
            $person[$k]['comment'] = $v['备注'];
            switch ($person[$k]['post']) {
                case '学生':
                    $person[$k]['role'] = 8;
                    break;
                case '教师':
                    $person[$k]['role'] = 6;
                    break;
                default:
                    $person[$k]['role'] = 9;
            }
        }
        // }

        Db::startTrans();
        try {
            if (!empty($person)) {
                $pt_mode->saveAll($person);
            } else {
                // Db::rollback();
                return false;
            }
            //查询重复数据
            $same = Db::view('person')
                ->view('person_temp', 'name', 'person.number = person_temp.number')
                ->select();
            //删除临时表里的重复数据
            foreach ($same as $k => $v) {
                Db::table('person_temp')->where('number', $v['number'])->delete();
            }
            //查询临时表数据
            //知识点:查询时忽略某个字段
            $data = Db::table('person_temp')->withoutField('id')->select()->toArray();
            if (empty($data)) {
                // Db::rollback();
                return '没有新数据';
            }
            $res = $this->limit(100)->insertAll($data);

            if ($res) {
                Db::table('person_temp')->delete(true);
                Db::commit();
                return true;
            } else {
                // Db::rollback();
                return '插入person表失败' . $res;
            }
        } catch (\Exception  $e) {
            Db::rollback();
            // return '插入goods表失败';
            return $e->getMessage() . 'catch';
        }
    }
    //查询person
    public function selectPerson($key, $value, $list_rows = 10, $isSimple = false, $config = '')
    {
        switch ($key) {
            case 'number':
                $data = $this->where($key, $value)->paginate($list_rows, $isSimple, $config);
                break;
            case 'name':
                $data = $this->where($key, $value)->paginate($list_rows, $isSimple, $config);
                break;
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

    // 修改人员信息
    public function updatePerson($data)
    {
        try {
            $data = request()->only(['id','role']);
            $this->update($data);
            return true;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
        // $res = $this->save($data);
    }

    // 删除人员信息
    public function deletePerson($id)
    {
        try {
            //软删除
            $this->destroy($id);
            //真实删除
            // $this->destroy($id,true);
            return true;
        } catch (\Exception $e) {
            return $e;
        }
        // $res = $this->save($data);
    }
    //发送验证码
    public function savePersonCode($number, $time_code, $msg = '验证码')
    {
        // $emp_uuid = $this->where('work_num',$work_num)->value('uuid');        
        $data = [
            'uuid' => $number,
            'code' => $time_code,
            'msg' => $msg
        ];
        //知识点:跨表数据库操作
        return Db::table('temp_code')->insert($data);
        // $admin->code = $log_code;
    }

    //删除验证码

    public function deletePersonCode($number)
    {
        return Db::table('temp_code')->where('uuid', $number)->delete();
    }
    //获取所有信息
    public function getAllInfoByNumber($number)
    {
        return $this->where('number', $number)->find();
    }
    //获取单个信息
    public function getInfoByNumber($number, $value)
    {
        return $this->where('number', $number)->value($value);
    }
    //根据学号修改信息
    public function updateByNumber($number, $value)
    {
        try {
            $this->where('number', $number)->update($value);
            return true;
        } catch (\Exception $e) {
            return $e;
        }
    }
    //获取人员信息,分页显示

    public function getAllPerson($list_rows, $config, $faculty, $post, $isSimple = false)
    {
        //知识点:删除指定键名元素
        $post = array_diff_key($post, ["list_rows" => 0, "page" => 0]);
        // return $post;
        if ($faculty == '') {
            $data = $this->where($post)->paginate($list_rows, $isSimple, $config);
        } else {
            $data = $this->where($post)->where('faculty', $faculty)->paginate($list_rows, $isSimple = false, $config);
        }
        //判断是否有值
        // if ($data->isEmpty()) {
        //     return $data;
        // } else {
        $fileName = config('app.json_path') . '/options.json';
        $string = file_get_contents($fileName);
        $json_data = json_decode($string, true);
        foreach ($data as $k => $v) {
            $data[$k]['faculty'] = (int)$data[$k]['faculty'];
            // PHP数组查询
            //学院
            $found_arr = array_column($json_data, 'value'); //所查询键名组成的数组
            $found_key = array_search($v['faculty'], $found_arr); //所查询数据在josn_data数组中的下标
            // $data[$k]['faculty'] = $json_data[$found_key]['label'];

            //党支部
            $found_child_arr = array_column($json_data[$found_key]['children'], 'value'); //所查询键名组成的数组
            $found_child_key = array_search($v['party_branch'], $found_child_arr); //所查询数据在josn_data数组中的下标
            $data[$k]['party_branch'] = $json_data[$found_key]['children'][$found_child_key]['label'];
        }

        return $data;
        // }
    }













    //TODO:删除
    /************************************ */

    //获取员工信息,分页显示
    public function getEmpInfo($list_rows, $isSimple = false, $config)
    {
        $data = $this->paginate($list_rows, $isSimple = false, $config);
        //判断是否有值
        if ($data->isEmpty()) {
            return false;
        } else {
            return $data;
        }
    }
    //通过uuid查询
    public function getInfoByUuid($emp_uuid, $value)
    {
        return $this->where('uuid', $emp_uuid)->value($value);
    }
    //通过工号查询
    public function getInfoByWorkNum($work_num, $value)
    {
        return $this->where('work_num', $work_num)->value($value);
    }
    //通过工号/姓名查询
    public function getEmpByWrokNum($work_num, $real_name)
    {
        if (empty($work_num)) {
            $data = $this->where('real_name', $real_name)->select();
        } else if (empty($real_name)) {
            $data = $this->where('work_num', $work_num)->select();
        } else {
            $data = $this->where('work_num', $work_num)->where('real_name', $real_name)->find();
        }
        if (empty($data)) {
            return false;
        } else {
            return $data;
        }
    }
    //通过姓名查询
    // public function findEmpAc($work_num,$email)
    // {

    //     //姓名可能会有重复,使用select查询
    //     $data = $this->where('real_name', $real_name)->select();
    //     if ($data->isEmpty()) {
    //         return false;
    //     } else {
    //         return $data;
    //     }
    // }
    //通过权限查询,多个数据,用到分页
    public function getEmpByRole($list_rows, $isSimple = false, $config, $role)
    {
        $data = $this->where('role', $role)->paginate($list_rows, $isSimple = false, $config);
        if ($data->isEmpty()) {
            return false;
        } else {
            return $data;
        }
    }
}
