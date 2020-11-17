<?php
/*
 * @Description: 
 * @Author: 罗曼
 * @Date: 2020-10-13 17:12:47
 * @FilePath: \testd:\wamp64\www\thinkphp-api\app\Model\RecruitPartyMember.php
 * @LastEditTime: 2020-11-17 14:50:17
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
    // public function getRecruitOld($list_rows, $config, $faculty, $post, $role, $isSimple = false)
    // {
    //     //删除指定键名元素
    //     $post = array_diff_key($post, ["list_rows" => 0, "page" => 0]);
    //     $person_model = new PersonModel();
    //     //获取json数据
    //     $fileName = config('app.json_path') . '/options.json';
    //     $string = file_get_contents($fileName);
    //     $json_data = json_decode($string, true);
    //     //获取当前时间
    //     $now = date("Y-m-d H:i:s");
    //     $data = $this
    //         ->where('stage_time', '<', $now)
    //         ->where($post)
    //         ->distinct(true)
    //         ->group('number')
    //         ->paginate($list_rows, $isSimple, $config);
    //     foreach ($data as $k => $v) {
    //         $person_info = $person_model->getAllInfoByNumber($v['number']);  //获取人员信息
    //         //二级管理员查看时剔除非本学院人员信息
    //         if ($role == 4 && $faculty !== $person_info['faculty']) {
    //             unset($data[$k]);
    //             continue;
    //         }
    //         /************个人信息*/
    //         $data[$k]['name'] = $person_info['name'];
    //         $data[$k]['sex'] = $person_info['sex'];
    //         $data[$k]['post'] = $person_info['post'];
    //         $data[$k]['nation'] = $person_info['nation'];
    //         $data[$k]['native_place'] = $person_info['native_place'];
    //         $data[$k]['id_card'] = $person_info['id_card'];
    //         $data[$k]['phone_number'] = $person_info['phone_number'];
    //         $data[$k]['politival_status'] = $person_info['political_status'];
    //         $data[$k]['faculty'] = (int)($person_info['faculty']);
    //         // $data[$k]['post'] = (int)($person_info['faculty']);

    //         $data[$k]['remarkes'] = (int)($person_info['faculty']);


    //         //学院
    //         $found_arr = array_column($json_data, 'value'); //所查询键名组成的数组
    //         $found_key = array_search($person_info['faculty'], $found_arr); //所查询数据在josn_data数组中的下标
    //         // $data[$k]['faculty'] = $json_data[$found_key]['label'];
    //         //党支部
    //         $found_child_arr = array_column($json_data[$found_key]['children'], 'value'); //所查询键名组成的数组
    //         $found_child_key = array_search($person_info['party_branch'], $found_child_arr); //所查询数据在josn_data数组中的下标
    //         $data[$k]['party_branch'] = $json_data[$found_key]['children'][$found_child_key]['label'];
    //         /************发展党员信息*/
    //         for ($i = 0; $i <= 8; $i++) {
    //             $recruit_info[$i] = $this->where('number', $v['number'])->where('stage', $i + 1)->where('stage_time', '<', $now)->find();
    //             $data[$k]['stage' . $i] = substr($recruit_info[$i]['stage_time'], 0, 10);
    //             if (!empty($recruit_info[$i]['contacts'])) {
    //                 $data[$k]['contacts_is'] = $recruit_info[$i]['contacts'];
    //             }
    //             if (!empty($recruit_info[$i]['introducer'])) {
    //                 $data[$k]['introducer_is'] = $recruit_info[$i]['introducer'];
    //             }
    //         }
    //         $data[$k]['contacts'] = !empty($data[$k]['contacts_is']) ? $data[$k]['contacts_is'] : '';
    //         $data[$k]['introducer'] = !empty($data[$k]['introducer_is']) ? $data[$k]['introducer_is'] : '';
    //     }
    //     return $data;
    // }

    //发展党员信息
    public function getRecruit($list_rows, $config, $faculty, $post, $role, $isSimple = false)
    {
        $post = array_diff_key($post, ["list_rows" => 0, "page" => 0]);
        $select_post = [];
        $now = date("Y-m-d H:i:s");
        // array_push($select_post,'stage_time<'. $now);
        foreach ($post as $k => $v) {
            $select_post['person.' . $k] = $v;
            if ($k == 'faculty') {
                $v > 9 ? $select_post['person.' . $k] = (string)$v : $select_post['person.' . $k] = '0' . (string)$v;
            }
            if ($k == 'stage') {
                unset($select_post['person.' . $k]);
                $select_stage = $v;
            }
        }
        //二级管理员查看时剔除非本学院人员信息
        if ($role !== 4) {
            $faculty = '';
        }
        // return $select_post;
        $list =  Db::view('person')
            ->view('recruit_party_member', 'stage', 'person.number=recruit_party_member.number')
            ->fieldRaw('max(case when stage=1 then  DATE_FORMAT(stage_time,"%Y-%m-%d") else "" end) as stage0')
            ->fieldRaw('max(case when stage=2 then DATE_FORMAT(stage_time,"%Y-%m-%d") else "" end) as stage1')
            ->fieldRaw('max(case when stage=3 then DATE_FORMAT(stage_time,"%Y-%m-%d") else "" end) as stage2')
            ->fieldRaw('max(case when stage=4 then DATE_FORMAT(stage_time,"%Y-%m-%d") else "" end) as stage3')
            ->fieldRaw('max(case when stage=5 then DATE_FORMAT(stage_time,"%Y-%m-%d") else "" end) as stage4')
            ->fieldRaw('max(case when stage=6 then DATE_FORMAT(stage_time,"%Y-%m-%d") else "" end) as stage5')
            ->fieldRaw('max(case when stage=7 then DATE_FORMAT(stage_time,"%Y-%m-%d") else "" end) as stage6')
            ->fieldRaw('max(case when stage=8 then DATE_FORMAT(stage_time,"%Y-%m-%d") else "" end) as stage7')
            ->fieldRaw('max(case when stage=9 then DATE_FORMAT(stage_time,"%Y-%m-%d") else "" end) as stage8')
            ->fieldRaw('max(stage) as stage')
            ->where($select_post)
            ->where('stage_time', '<', $now)
            ->whereRaw("faculty='$faculty' or '$faculty' =''")
            ->group('person.number')
            ->paginate($list_rows, $isSimple, $config)
            ->each(function ($item, $key) {
                $item['faculty'] = (int)$item['faculty'];
                return $item;
            });
        foreach ($list as $key => $value) {
            if (!empty($select_stage) && $list[$key]['stage'] != $select_stage) {
                unset($list[$key]);
            }
        }
        return $list;
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























    /******************发展党员大数据 */
    //统计民族信息
    public function countRecruitNation($faculty)
    {
        $count = Db::table('person')
            ->alias('a')
            ->join('recruit_party_member b', 'a.number = b.number')
            ->whereRaw("faculty='$faculty' or '$faculty' =''")
            // ->where('step', 1)
            // ->fetchSql(true)
            ->where('stage', 1)
            ->field('a.nation as 民族')
            ->fieldRaw('count(*) AS 人数')
            ->group('nation')
            ->select();
        return $count;
    }
    //统计性别信息
    public function countRecruitSex(string $faculty = '')
    {
        $list = [];
        $count = Db::table('person')
            ->alias('a')
            ->join('recruit_party_member b', 'a.number = b.number')
            ->whereRaw("faculty='$faculty' or '$faculty' =''")
            ->where('stage', 1)
            ->fieldRaw('SUM(CASE WHEN sex = 1 THEN 1 ELSE 0 END) AS 男')
            ->fieldRaw('SUM(CASE WHEN sex = 2 THEN 1 ELSE 0 END) AS 女')
            ->find();
        $count = array_diff_key($count, ["id" => -1, "number" => -1]);
        $i = 0;
        foreach ($count as $k => $v) {
            $list[$i]['性别'] = $k;
            $list[$i]['人数'] = $v;
            $i++;
        }
        return $list;
    }

    //统计学院信息
    public function countRecruitFaculty($faculty)
    {
        $count = Db::table('person')
            ->alias('a')
            ->join('recruit_party_member b', 'a.number = b.number')
            ->whereRaw("faculty='$faculty' or '$faculty' =''")
            // ->where('step', 1)
            // ->fetchSql(true)
            ->where('stage', 1)
            ->field('a.faculty as 学院')
            ->fieldRaw('count(*) AS 人数')
            ->group('faculty')
            ->select();
        return $count;
    }

    //统计职务信息
    public function countRecruitPost($faculty)
    {
        $count = Db::table('person')
            ->alias('a')
            ->join('recruit_party_member b', 'a.number = b.number')
            ->whereRaw("faculty='$faculty' or '$faculty' =''")
            // ->where('step', 1)
            // ->fetchSql(true)
            ->where('stage', 1)
            ->field('a.post as 职务')
            ->fieldRaw('count(*) AS 人数')
            ->group('post')
            ->select();
        return $count;
    }
    //统计发展阶段信息

    public function countRecruitStage($faculty)
    {
        $count = Db::table('person')
            ->alias('a')
            ->join('recruit_party_member b', 'a.number = b.number')
            ->whereRaw("faculty='$faculty' or '$faculty' =''")
            ->fieldRaw('stage as 发展阶段')
            ->fieldRaw('count(*) as 人数')
            ->group('stage')
            ->select();
        // 成长阶段:1为申请入党,2为推优育苗,3为团组织推优,4为积极分子,5为发展对象,6为预备党员,7为预备党委审批,8为正式党员,9为正式党委审批',
        $stage_list = ['申请入党', '推优育苗', '团组织推优', '积极分子', '发展对象', '预备党员', '预备党委审批', '正式党员', '正式党委审批'];
        $list = [];
        foreach ($count as $k => $v) {
            $list[$k]['发展阶段'] = $stage_list[$k];
            $list[$k]['人数'] = !empty($count[$k + 1]) ? $count[$k]['人数'] - $count[$k + 1]['人数'] : $count[$k]['人数'];
        }
        return $list;
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
