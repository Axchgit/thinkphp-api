<?php
/*
 * @Description: 
 * @Author: 罗曼
 * @Date: 2020-10-13 17:12:47
 * @FilePath: \testd:\wamp64\www\thinkphp-api\app\Model\RecruitPartyMember.php
 * @LastEditTime: 2020-11-10 20:12:01
 * @LastEditors: 罗曼
 */


namespace app\model;

// use PHPExcel_IOFactory;

// use think\Db;
use think\Model;
use think\facade\Db;

use app\model\Person as PersonModel;
// use app\model\RecruitPartyMember as RecruitPartyMemberModel;

// use app\model\Material as MaterialModel;

class RecruitPartyMember extends Model
{
    //添加发展党员信息
    public function createRecruit($data)
    {
        try {
            $this->create($data, ['number', 'stage', 'stage_time', 'contacts', 'introducer', 'remarks']);  //只允许第二个参数内的值被修改
            return true;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }
    //删除发展党员信息
    public function deleteRecruit($data)
    {
        try {
            $this->where($data)->delete();  //只允许第二个参数内的值被修改
            return true;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }
    //获取人员信息,分页显示 
    public function getRecruit($list_rows, $config, $faculty, $post, $role, $isSimple = false)
    {
        //删除指定键名元素
        $post = array_diff_key($post, ["list_rows" => 0, "page" => 0]);
        $person_model = new PersonModel();
        //获取json数据
        $fileName = config('app.json_path') . '/options.json';
        $string = file_get_contents($fileName);
        $json_data = json_decode($string, true);
        //获取当前时间
        $now = date("Y-m-d H:i:s");
        $data = $this
            ->where('stage_time', '<', $now)
            ->where($post)
            ->distinct(true)
            ->group('number')
            ->paginate($list_rows, $isSimple, $config);
        foreach ($data as $k => $v) {
            $person_info = $person_model->getAllInfoByNumber($v['number']);  //获取人员信息
            //二级管理员查看时剔除非本学院人员信息
            if ($role == 4 && $faculty !== $person_info['faculty']) {
                unset($data[$k]);
                continue;
            }
            /************个人信息*/
            $data[$k]['name'] = $person_info['name'];
            $data[$k]['sex'] = $person_info['sex'];
            $data[$k]['post'] = $person_info['post'];
            $data[$k]['nation'] = $person_info['nation'];
            $data[$k]['native_place'] = $person_info['native_place'];
            $data[$k]['id_card'] = $person_info['id_card'];
            $data[$k]['phone_number'] = $person_info['phone_number'];
            $data[$k]['politival_status'] = $person_info['political_status'];
            //学院
            $found_arr = array_column($json_data, 'value'); //所查询键名组成的数组
            $found_key = array_search($person_info['faculty'], $found_arr); //所查询数据在josn_data数组中的下标
            $data[$k]['faculty'] = $json_data[$found_key]['label'];
            //党支部
            $found_child_arr = array_column($json_data[$found_key]['children'], 'value'); //所查询键名组成的数组
            $found_child_key = array_search($person_info['party_branch'], $found_child_arr); //所查询数据在josn_data数组中的下标
            $data[$k]['party_branch'] = $json_data[$found_key]['children'][$found_child_key]['label'];
            /************发展党员信息*/
            for ($i = 0; $i <= 8; $i++) {
                $recruit_info[$i] = $this->where('number', $v['number'])->where('stage', $i + 1)->where('stage_time', '<', $now)->find();
                $data[$k]['stage' . $i] = substr($recruit_info[$i]['stage_time'], 0, 10);
                if (!empty($recruit_info[$i]['contacts'])) {
                    $data[$k]['contacts_is'] = $recruit_info[$i]['contacts'];
                }
                if (!empty($recruit_info[$i]['introducer'])) {
                    $data[$k]['introducer_is'] = $recruit_info[$i]['introducer'];
                }
            }
            $data[$k]['contacts'] = !empty($data[$k]['contacts_is']) ? $data[$k]['contacts_is'] : '';
            $data[$k]['introducer'] = !empty($data[$k]['introducer_is']) ? $data[$k]['introducer_is'] : '';
        }
        return $data;
    }
    //通过学工号获取信息
    public function getAllByNumber($number)
    {
        return $this->where('number', $number)->select();
    }
    //查询是否有超过当前日期的信息
    public function getIsExceedNow($number)
    {
        $now = date("Y-m-d H:i:s");
        $count = $this->where('number', $number)->where('stage_time', '>', $now)->count();
        if ($count >= 1) {
            return true;
        } else {
            return false;
        }
    }


    public function testOne()
    {
        $now = date("Y-m-d H:i:s");

        $data = $this
            ->where('stage_time', '>', $now)->select();
        return $data;
    }





    //over
}
