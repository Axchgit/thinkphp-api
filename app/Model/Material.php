<?php
/*
 * @Description: 
 * @Author: 罗曼
 * @Date: 2020-10-13 17:12:47
 * @FilePath: \testd:\wamp64\www\thinkphp-api\app\Model\Material.php
 * @LastEditTime: 2021-01-07 00:50:16
 * @LastEditors: xch
 */


namespace app\model;

// use PHPExcel_IOFactory;

// use think\Db;
use think\Model;
use think\facade\Db;


class Material extends Model
{
    //  添加资料信息
    public function addMaterial($number, $category, $serial_number, $score, $remarks)
    {
        try {
            $data = [
                'number' => $number,
                'category' => $category,
                'serial_number' => $serial_number,
                'score' => $score,
                'remarks' => $remarks
            ];
            $this->save($data);
            // $this->update(['review_status' => $data['review_status'], 'id' => $data['id']]);
            return true;
        } catch (\Exception $e) {
            return  $e->getMessage();
        }
    }
    //更新考核成绩
    public function updateMaterial($data){
        try {
            // $data = request()->only(['id', 'role','faculty','party_branch']);
            $this->update($data, ['id' => $data['id']], ['category', 'serial_number', 'score', 'remarks']);
            return true;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }
    //删除考核成绩
    public function deleteMaterial($id)
    {
        try {
            //软删除
            // $this->destroy($id);
            //真实删除
            $this->destroy($id,true);
            return true;
        } catch (\Exception $e) {
            return $e;
        }
        // $res = $this->save($data);
    }

    //根据学号查询信息
    public function getInfoByNumber($number, $key, $value)
    {
        return $this->where('number', $number)->where($key, $value)->find();
    }

    //根据学号查询所有数据
    public function getAllByNumber($number)
    {
        return $this->where('number', $number)->select();
    }

    //导入成绩
    public function insertMaterial($dataArr)
    {
        // $pt_mode = new PersonTempModel();
        $year = date("Y");
        $material = [];
        $num = 1;
        foreach ($dataArr as $k => $v) {
            if ($num < 10) {
                $num = '0' . (string)$num;
            }

            $material[$k]['number'] = $v['学工号'];
            // $material[$k]['faculty'] = $v['学院代码'];
            // $person[$k]['major'] = $v['专业'];
            $material[$k]['grade'] = $v['年级'];
            $material[$k]['category'] = (string)$v['类别'];


            $material[$k]['score'] = $v['分数'];
            if ($material[$k]['score'] >= 60) {
                $material[$k]['serial_number'] = $year . $v['学院代码'] . $v['类别'] . (string)$num;
            }

            // $person[$k]['name'] = $v['姓名'];
            // $person[$k]['sex'] = $v['性别'] === '男' ? 1 : 2;
            // $person[$k]['nation'] = $v['民族'];
            // $person[$k]['id_card'] = $v['身份证号'];
            // $person[$k]['education'] = $v['学历'];
            // $person[$k]['post'] = $v['职务'];
            // $person[$k]['phone_number'] = $v['手机号'];
            // $person[$k]['email'] = $v['邮箱'];
            // $material[$k]['comment'] = $v['备注'];
            // switch ($person[$k]['post']) {
            //     case '学生':
            //         $person[$k]['role'] = 8;
            //         break;
            //     case '教师':
            //         $person[$k]['role'] = 6;
            //         break;
            //     default:
            //         $person[$k]['role'] = 9;
            // }
            $num++;
        }
        Db::startTrans();
        try {
            if (!empty($material)) {
                $this->saveAll($material);
                Db::commit();
                return true;
            } else {
                Db::rollback();
                return false;
            }
        } catch (\Exception  $e) {
            Db::rollback();
            return $e->getMessage();
        }
        // $res = $this->limit(100)->insertAll($material);

        // if ($res) {
        //     Db::table('person_temp')->delete(true);
        //     Db::commit();
        //     return true;
        // } else {
        //     // Db::rollback();
        //     return '插入person表失败' . $res;
        // }
        // return $material;
        // }

        // Db::startTrans();
        // try {
        //     if (!empty($person)) {
        //         $pt_mode->saveAll($person);
        //     } else {
        //         // Db::rollback();
        //         return false;
        //     }
        //     //查询重复数据
        //     $same = Db::view('person')
        //         ->view('person_temp', 'name', 'person.number = person_temp.number')
        //         ->select();
        //     //删除临时表里的重复数据
        //     foreach ($same as $k => $v) {
        //         Db::table('person_temp')->where('number', $v['number'])->delete();
        //     }
        //     //查询临时表数据
        //     //查询时忽略某个字段
        //     $data = Db::table('person_temp')->withoutField('id')->select()->toArray();
        //     if (empty($data)) {
        //         // Db::rollback();
        //         return '没有新数据';
        //     }
        //     $res = $this->limit(100)->insertAll($data);

        //     if ($res) {
        //         Db::table('person_temp')->delete(true);
        //         Db::commit();
        //         return true;
        //     } else {
        //         // Db::rollback();
        //         return '插入person表失败' . $res;
        //     }
        // } catch (\Exception  $e) {
        //     Db::rollback();
        //     // return '插入goods表失败';
        //     return $e->getMessage() . 'catch';
        // }
    }


    //分页获取考核成绩信息
    public function getMaterial($list_rows, $config, $faculty, $post, $role, $isSimple = false)
    {
        try {
            $post = array_diff_key($post, ["list_rows" => 0, "page" => 0]);
            $select_post = [];
            $now = date("Y-m-d H:i:s");
            // array_push($select_post,'stage_time<'. $now);
            foreach ($post as $k => $v) {
                $select_post['person.' . $k] = $v;
                if ($k == 'faculty') {
                    $v > 9 ? $select_post['person.' . $k] = (string)$v : $select_post['person.' . $k] = '0' . (string)$v;
                }
            }
            // //二级管理员查看时剔除非本学院人员信息
            // if ($role !== 4) {
            //     $faculty = '';
            // }
            // return $select_post;
            //知识点:查询所有学生的各科成绩,根据学号分组,别忘记max聚合函数
            $list = Db::table('person')
                ->alias('a')
                ->join('material b', 'a.number = b.number')

                ->fieldRaw('max(case when category=1 then score else "" end) as score_1')
                ->fieldRaw('max(case when category=2 then score else "" end) as score_2')
                ->fieldRaw('max(case when category=3 then score else "" end) as score_3')

                ->fieldRaw('max(case when category=1 then serial_number else "" end) as serial_number_1')
                ->fieldRaw('max(case when category=2 then serial_number else "" end) as serial_number_2')
                ->fieldRaw('max(case when category=3 then serial_number else "" end) as serial_number_3')
                ->field('person.name,person.faculty,person.number')
                ->where($select_post)
                ->where('category', '<>', 4)
                ->whereRaw("faculty='$faculty' or '$faculty' =''")
                ->fieldRaw("avg(score)")
                ->group('person.number')
                ->paginate($list_rows, $isSimple, $config)
                ->each(function ($item, $key) {
                    $item['faculty'] = (int)$item['faculty'];
                    $item['materialOne']= $item['serial_number_1']>10;
                    $item['materialTwo']= $item['serial_number_2']>10;
                    $item['materialThree']= $item['serial_number_3']>10;
                    return $item;
                });
            // foreach ($list as $key => $value) {
            //     if (!empty($select_stage) && $list[$key]['stage'] != $select_stage) {
            //         unset($list[$key]);
            //     }
            // }
            return [true, $list];
        } catch (\Exception $e) {
            //throw $th;
            return [false, $e->getMessage()];
        }
    }

    //获取所有行数据
    public function getAllList($list_rows, $config, $faculty, $post, $isSimple = false){
        try {
            $post = array_diff_key($post, ["list_rows" => 0, "page" => 0]);
            $select_post=[];
            foreach ($post as $k => $v) {
                $select_post['person.' . $k] = $v;
                // if ($k == 'faculty') {
                //     $v > 9 ? $select_post['person.' . $k] = (string)$v : $select_post['person.' . $k] = '0' . (string)$v;
                // }
            }
            $list = Db::table('person')
            ->alias('a')
            ->join('material b', 'a.number = b.number')

            ->field('person.name,person.faculty,person.number')
            ->field('b.*')
            ->where($select_post)
            ->order('a.number')
            ->where('category', '<>', 4)
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







    //over
}
