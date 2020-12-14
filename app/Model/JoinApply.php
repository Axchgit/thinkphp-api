<?php
/*
 * @Description: 
 * @Author: 罗曼
 * @Date: 2020-10-16 16:28:24
 * @FilePath: \testd:\wamp64\www\thinkphp-api\app\Model\JoinApply.php
 * @LastEditTime: 2020-12-14 17:55:42
 * @LastEditors: 罗曼
 */

namespace app\model;

// use PHPExcel_IOFactory;

// use think\Db;
use think\Model;
use think\facade\Db;

use app\model\Person as PersonModel;
use app\model\Material as MaterialModel;


class JoinApply extends Model
{
    //添加申请记录
    public function addApply($number, $step, $review_status = 1, $reviewer = '', $remarks = '')
    {
        try {
            $data = [
                'number' => $number,
                'step' => $step,
                'review_status' => $review_status,
                'reviewer' => $reviewer,
                'remarks' => $remarks
            ];
            $this->save($data);
            // $this->update(['review_status' => $data['review_status'], 'id' => $data['id']]);
            return true;
        } catch (\Exception $e) {
            return  $e->getMessage();
        }
    }
    //查询申请进程
    public function selectApplyStep($number)
    {
        //获取该人员最新步骤
        // try {
        $step = $this->where('number', $number)->max('step');
        return $this->where('number', $number)->where('step', $step)->find();
        // } catch (\Exception $e) {
        //     return  $e->getMessage();
        // }
    }

    public function getApplyById($id, $value)
    {
        return $this->where('id', $id)->value($value);
    }

    //获取人员信息,分页显示 
    public function getAllApply($list_rows, $config, $faculty, $post, $role, $isSimple = false)
    {
        $person_model = new PersonModel();
        $material_model = new MaterialModel();

        //删除指定键名元素
        $select_post = array_diff_key($post, ["list_rows" => 0, "page" => 0]);
        $select_post_new = [];
        foreach ($select_post as $k => $v) {
            $select_post_new['person.' . $k] = $v;
            if ($k == 'step' || $k == 'review_status') {
                unset($select_post_new['person.' . $k]);
                $select_post_new['d.' . $k] = $v;
            }
        }
        $subsql = Db::table('join_apply')
            ->alias('a')
            ->field('a.number,max(a.id) as id')
            ->join('person b', 'a.number = b.number')
            ->fieldRaw('max(step) as high_step')
            ->group('b.number')
            // ->select();
            ->buildSql();
        $list =  Db::table('person')
            ->alias('a')
            ->leftjoin('material b', 'a.number = b.number')
            ->join([$subsql => 'c'], 'a.number = c.number')
            ->join('join_apply d', 'd.id = c.id')
            ->field('faculty,party_branch')
            // ->field('c.*')
            ->field('d.id,d.step,d.review_status,d.reviewer,d.remarks,d.create_time')
            ->field('a.number,name,sex,faculty,party_branch,nation,email,post')
            
            ->fieldRaw('max(case when category=1 then score else "未认证" end) as certificate_one')
            ->fieldRaw('max(case when category=2 then score else "未认证" end) as certificate_two')
            ->fieldRaw('max(case when category=3 then score else "未认证" end) as certificate_three')
            ->fieldRaw('max(case when category=4 then b.remarks else "" end) as applicationPath')
            // ->fieldRaw('max(stage) as stage')
            ->where($select_post_new)
            ->whereRaw("faculty='$faculty' or '$faculty' =''")
            ->group('person.number')
            ->paginate($list_rows, $isSimple, $config)
            ->each(function ($item, $key) {
                $item['faculty'] = (int)$item['faculty'];
                // $person_model->getJsonData();
                return $item;
            })->toArray();
        foreach ($list['data'] as $k => $v) {
            if ($v['party_branch'] == 0) {
                $list['data'][$k]['party_branch'] = '未选择';
            } else {
                $list['data'][$k]['party_branch'] = $person_model->getJsonData('options.json', $v['faculty'], $v['party_branch'], true);
            }
        }
        return  $list;
    }

    //修改/审核申请
    public function updateJoinApply($data)
    {
        try {
            $this->update($data, ['id' => $data['id']], ['step', 'review_status', 'reviewer', 'remarks']);  //只允许第二个参数内的值被修改
            return true;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }






    /*********首页charts数据*/

    //查询所有申请人数
    public function countApplyPerson(string $faculty = '')
    {
        return Db::view('person', 'number,faculty')
            ->view('join_apply', 'number', 'person.number=join_apply.number')
            ->whereRaw("faculty='$faculty' or '$faculty' =''")
            ->count();
    }
    //查询审核数
    public function countReview(int $review_status = 2, string $faculty = '')
    {
        return Db::view('person', 'faculty')
            ->view('join_apply', 'number', 'person.number=join_apply.number')
            ->whereRaw("faculty='$faculty' or '$faculty' =''")
            ->where('review_status', $review_status)
            ->count();
    }

    //折线图数据
    public function countLineCharts(string $faculty = '')
    {
        $year = (int)date('Y');
        $list = [];
        for ($i = $year; $i > $year - 10; $i--) {
            $count = Db::view('person', 'faculty')
                ->view('join_apply', 'number', 'person.number=join_apply.number')
                ->where('step', 1)
                ->whereYear('join_apply.create_time', $i)
                ->whereRaw("faculty='$faculty' or '$faculty' =''")
                // ->group("date_format(join_apply.create_time,'%Y')")
                ->count();
            $list[$year - $i]['年份'] = (string)$i;
            $list[$year - $i]['新增申请人数'] = $count;
        }
        return $list;
    }

    //男女占比
    public function countPersonSex(string $faculty = '')
    {
        $list = [];
        $count = Db::view('person', 'id')
            ->view('join_apply', 'number', 'person.number=join_apply.number')
            ->whereRaw("faculty='$faculty' or '$faculty' =''")
            ->where('step', 1)
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
        // $count = Db::table('person')
        //     ->alias('a')
        //     ->join('join_apply b', 'a.number = b.number')
        //     ->where('step', 1)
        //     ->field('a.sex as 性别')
        //     ->fieldRaw('count(*) AS 人数')
        //     ->group('sex')
        //     ->select();
        return $list;
    }

    public function countPersonNation(string $faculty = '')
    {
        $count = Db::table('person')
            ->alias('a')
            ->join('join_apply b', 'a.number = b.number')
            //知识点:查询条件为空时,忽略该条件
            ->whereRaw("faculty='$faculty' or '$faculty' =''")
            ->where('step', 1)
            // ->fetchSql(true)
            ->field('a.nation as 民族')
            ->fieldRaw('count(*) AS 人数')
            ->group('nation')
            ->select();
        return $count;
    }










    //over
}
