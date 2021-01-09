<?php
/*
 * @Description: 
 * @Author: 罗曼
 * @Date: 2020-10-13 17:12:47
 * @FilePath: \testd:\wamp64\www\thinkphp-api\app\Model\RecruitPartyMember.php
 * @LastEditTime: 2021-01-09 18:42:01
 * @LastEditors: 罗曼
 */


namespace app\model;

// use PHPExcel_IOFactory;
use think\model\concern\SoftDelete;


// use think\Db;
use think\Model;
use think\facade\Db;

use app\model\Person as PersonModel;
// use app\model\RecruitPartyMember as RecruitPartyMemberModel;

// use app\model\Material as MaterialModel;

class RecruitPartyMember extends Model
{
    // use SoftDelete;
    // protected $deleteTime = 'delete_time';
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
    //修改发展党员信息
    public function updateRecruit($data)
    {
        try {
            // $data = request()->only(['id', 'role','faculty','party_branch']);
            $this->update($data, ['id' => $data['id']], ['stage_time', 'contacts', 'introducer', 'remarks']);
            return true;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }
    //删除发展党员信息
    public function deleteRecruit($data)
    {
        try {
            $this->where($data)->delete();
            return true;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    //删除考核成绩
    public function deleteRecruitById($id)
    {
        try {
            //软删除
            // $this->destroy($id);
            //真实删除
            $this->destroy($id, true);
            return true;
        } catch (\Exception $e) {
            return $e;
        }
        // $res = $this->save($data);
    }

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
            ->fieldRaw('max(case when stage=1 then DATE_FORMAT(stage_time,"%Y-%m-%d") else "" end) as stage0')
            ->fieldRaw('max(case when stage=2 then DATE_FORMAT(stage_time,"%Y-%m-%d") else "" end) as stage1')
            ->fieldRaw('max(case when stage=3 then DATE_FORMAT(stage_time,"%Y-%m-%d") else "" end) as stage2')
            ->fieldRaw('max(case when stage=4 then DATE_FORMAT(stage_time,"%Y-%m-%d") else "" end) as stage3')
            ->fieldRaw('max(case when stage=5 then DATE_FORMAT(stage_time,"%Y-%m-%d") else "" end) as stage4')
            ->fieldRaw('max(case when stage=6 then DATE_FORMAT(stage_time,"%Y-%m-%d") else "" end) as stage5')
            ->fieldRaw('max(case when stage=7 then DATE_FORMAT(stage_time,"%Y-%m-%d") else "" end) as stage6')
            ->fieldRaw('max(case when stage=8 then DATE_FORMAT(stage_time,"%Y-%m-%d") else "" end) as stage7')
            ->fieldRaw('max(case when stage=9 then DATE_FORMAT(stage_time,"%Y-%m-%d") else "" end) as stage8')
            ->fieldRaw('max(case when stage=4 then contacts else "" end) as contacts')
            ->fieldRaw('max(case when stage=5 then introducer else "" end) as introducer')
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
    //通过学工号获取信息
    public function getAllByPost($post)
    {
        return $this->where($post)->find();
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



    //获取所有行数据
    public function getAllList($list_rows, $config, $faculty, $post, $isSimple = false)
    {
        try {
            $post = array_diff_key($post, ["list_rows" => 0, "page" => 0]);
            $select_post = [];
            foreach ($post as $k => $v) {
                $select_post['person.' . $k] = $v;
                // if ($k == 'faculty') {
                //     $v > 9 ? $select_post['person.' . $k] = (string)$v : $select_post['person.' . $k] = '0' . (string)$v;
                // }
            }
            $list = Db::table('person')
                ->alias('a')
                ->join('recruit_party_member b', 'a.number = b.number')

                ->field('person.name,person.faculty,person.number')
                ->field('b.*')
                ->where($select_post)
                ->order('b.stage_time','desc')
                // ->where('category', '<>', 4)
                ->whereRaw("faculty='$faculty' or '$faculty' =''")
                // ->group('person.number')
                ->paginate($list_rows, $isSimple, $config);
            // ->each(function ($item, $key) {
            //     $item['faculty'] = (int)$item['faculty'];
            //     $item['materialOne']= $item['serial_number_1']>10;
            //     $item['materialTwo']= $item['serial_number_2']>10;
            //     $item['materialThree']= $item['serial_number_3']>10;
            //     return $item;
            // });
            return [true, $list];
        } catch (\Exception $e) {
            return [false, $e->getMessage()];
        }
    }



















    /******************发展党员大数据 */
    //统计民族信息
    public function countRecruitNation($faculty)
    {
        $subsql = Db::table('recruit_party_member')
            ->fieldRaw('max(stage)')
            ->field('number')
            ->group('number')
            ->buildSql();
        $count = Db::table('person')
            ->alias('a')
            ->join([$subsql => 'b'], 'a.number = b.number')
            ->whereRaw("faculty='$faculty' or '$faculty' =''")
            ->field('a.nation as 民族')
            ->fieldRaw('count(*) AS 人数')
            ->group('nation')
            ->select();
        return $count;
    }
    //统计性别信息
    public function countRecruitSex(string $faculty = '')
    {
        $count = [];
        $subsql = Db::table('recruit_party_member')
            ->fieldRaw('max(stage)')
            ->field('number')
            ->group('number')
            ->buildSql();
        $list = Db::table('person')
            ->alias('a')
            ->join([$subsql => 'b'], 'a.number = b.number')
            ->whereRaw("faculty='$faculty' or '$faculty' =''")
            ->fieldRaw('SUM(CASE WHEN sex = 1 THEN 1 ELSE 0 END) AS 男')
            ->fieldRaw('SUM(CASE WHEN sex = 2 THEN 1 ELSE 0 END) AS 女')
            ->find();
        $list = array_diff_key($list, ["id" => -1, "number" => -1]);
        $i = 0;
        foreach ($list as $k => $v) {
            $count[$i]['性别'] = $k;
            $count[$i]['人数'] = $v;
            $i++;
        }
        return $count;
    }

    //统计学院信息
    public function countRecruitFaculty($faculty)
    {
        $subsql = Db::table('recruit_party_member')
            ->fieldRaw('max(stage)')
            ->field('number')
            ->group('number')
            ->buildSql();
        $list = Db::table('person')
            ->alias('a')
            ->join([$subsql => 'b'], 'a.number = b.number')
            ->whereRaw("faculty='$faculty' or '$faculty' =''")
            ->field('a.faculty as 学院')
            ->fieldRaw('count(*) AS 人数')
            ->group('faculty')
            ->select();
        $faculty_map =   ['文学与传媒学院', '马克思主义学院', '外国语学院', '数学与统计学院', '物理与机电工程学院', '化学与生物工程学院', '计算机与信息工程学院', '体育学院', '教师教育学院', '音乐舞蹈学院', '经济与管理学院', '历史与社会学院', '美术与设计学院'];
        $count = [];
        foreach ($list as $k => $v) {
            $count[$k]['学院'] = $faculty_map[(int)$list[$k]['学院'] - 1];
            $count[$k]['人数'] = $v['人数'];
        }

        return $count;
    }

    //统计职务信息
    public function countRecruitPost($faculty)
    {
        $subsql = Db::table('recruit_party_member')
            ->fieldRaw('max(stage)')
            ->field('number')
            ->group('number')
            ->buildSql();
        $count = Db::table('person')
            ->alias('a')
            ->join([$subsql => 'b'], 'a.number = b.number')
            ->whereRaw("faculty='$faculty' or '$faculty' =''")
            ->field('a.post as 职务')
            ->fieldRaw('count(*) AS 人数')
            ->group('post')
            ->select();
        return $count;
    }
    //统计发展阶段信息

    public function countRecruitStage($faculty)
    {
        // $now = date("Y-m-d H:i:s");
        $subsql = Db::table('recruit_party_member')
            ->fieldRaw('max(stage) as stage')
            ->where('stage_time', '<', date("Y-m-d H:i:s"))
            ->field('number')
            ->group('number')
            ->buildSql();
        $list = Db::table('person')
            ->alias('a')
            ->join([$subsql => 'b'], 'a.number = b.number')
            ->whereRaw("faculty='$faculty' or '$faculty' =''")
            ->field('stage')
            ->fieldRaw('count(*) as 人数')
            ->group('stage')
            ->select();
        // 成长阶段:1为申请入党,2为推优育苗,3为团组织推优,4为积极分子,5为发展对象,6为预备党员,7为预备党委审批,8为正式党员,9为正式党委审批',
        $stage_list = ['申请入党', '推优育苗', '团组织推优', '积极分子', '发展对象', '预备党员', '预备党委审批', '正式党员', '正式党委审批'];
        $count = [];
        foreach ($list as $k => $v) {
            $count[$k]['发展阶段'] = $stage_list[$v['stage'] - 1];
            $count[$k]['人数'] = $v['人数'];
        }
        return $count;
    }

    //统计政治面貌信息
    public function countRecruitPoliticalStatus($faculty)
    {
        $subsql = Db::table('person')
            ->alias('a')
            ->join('recruit_party_member b', 'a.number = b.number')
            ->whereRaw("faculty='$faculty' or '$faculty' =''")
            ->distinct(true)
            // ->field('political_status')
            ->field('b.number')
            // ->select();
            ->buildSql();
        $list = Db::table('person')
            ->alias('a')
            ->join([$subsql => 'b'], 'a.number = b.number')
            ->field('a.political_status')
            ->fieldRaw('count(*) AS 人数')
            ->group('political_status')
            ->select();
        $political_status_list = ['默认为共青团员', '群众', '共青团员', '预备党员', '正式党员'];
        $count = [];
        foreach ($list as $k => $v) {
            $count[$k]['政治面貌'] = $political_status_list[$v['political_status']];
            $count[$k]['人数'] = $v['人数'];
            // $count[$k]['test']=$v;
        }
        return $count;
    }

    //统计成长阶段信息
    public function countRecruitGrowthStage($faculty)
    {
        $subsql = Db::table('join_apply')
            ->fieldRaw('max(step) as high_step')
            ->field('number')
            ->where('review_status', 2)
            ->group('number')
            ->buildSql();
        $list = Db::table('person')
            ->alias('a')
            ->join([$subsql => 'b'], 'a.number = b.number')
            ->field('high_step')
            ->fieldRaw('count(*) AS 人数')
            ->whereRaw("faculty='$faculty' or '$faculty' =''")
            ->group('high_step')
            ->select();
        $growth_stage_list = ['苗子', '积极分子', '发展对象', '预备党员', '正式党员'];
        $count = [];
        foreach ($list as $k => $v) {
            $count[$k]['成长阶段'] = $growth_stage_list[$v['high_step'] - 1];
            $count[$k]['人数'] = $v['人数'];
        }
        return $count;
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
