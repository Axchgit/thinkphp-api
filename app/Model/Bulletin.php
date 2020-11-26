<?php
/*
 * @Description: 
 * @Author: 罗曼
 * @Date: 2020-11-23 01:30:43
 * @FilePath: \testd:\wamp64\www\thinkphp-api\app\Model\Bulletin.php
 * @LastEditTime: 2020-11-24 14:33:45
 * @LastEditors: 罗曼
 */


namespace app\model;

// use PHPExcel_IOFactory;

// use think\Db;
use think\Model;
use think\facade\Db;
use app\model\BulletinTarget as BulletinTargetModel;


class Bulletin extends Model
{
    //添加记录
    public function createBulletin($data)
    {
        $bt_model = new BulletinTargetModel();

        try {
            // Transfer::create($data,['number', 'contacts_phone','receive_organization','reason','remarks','review_status','reviewer']);
            $create_res = $this->create($data, ['level', 'title', 'content', 'target_type', 'create_number']);
            $data['bulletin_id'] = $create_res->id;
            switch ($data['target_type']) {
                case 2:
                    $target_person_arr = explode('-', $data['target_person']);
                    // return $target_person_arr[0];
                    for ($i = 0; $i < count($target_person_arr); $i++) {
                        $data['target_person'] = $target_person_arr[$i];
                        $bt_model->createBulletinTarget($data);
                    }
                    // return $data;
                    break;
                case 4:
                    return true;
                    break;
                default:
                    $bt_model->createBulletinTarget($data);
                    break;
            }
            return true;
        } catch (\Exception $e) {
            return  $e->getMessage();
        }
    }

    //获取通告
    public function getBulletin($list_rows, $config, $info_post, $number, $isSimple = false)
    {
        //知识点:多表联合子查询
        $subsql = Db::table('bulletin_read')
            ->where('target_number', $number)
            ->buildSql();

        $list = Db::table('bulletin')
            ->alias('a')
            ->leftjoin('bulletin_target b', 'a.id = b.bulletin_id')
            ->leftjoin([$subsql=>'c'], 'a.id = c.bulletin_id')
            ->leftjoin('person d', 'a.create_number = d.number')
            ->field('a.*')
            ->field('d.name as creater')
            ->fieldRaw("(CASE WHEN (c.read_time is not null and c.target_number = '$number') THEN DATE_FORMAT(c.read_time,'%m-%d %H:%i')  ELSE 0 END) AS is_read")
            ->whereRaw("((target_type=1 or target_type = 2) and target_person = '$number') or (target_type = 3 and target_person='$info_post') or (target_type = 4)")
            ->order('a.id', 'desc')
            ->paginate($list_rows, $isSimple, $config);
        return $list;
    }
    // (target_type = ‘指定用户或多个用户’ AND user = ‘用户id’) OR (target_type = ‘指定的用户群体’ AND user = ‘用户群体’ ) OR (target_type = ‘全部’)


    public function countUnreadBulletin($info_post, $number)
    {
        try {
            $all_count = Db::table('bulletin')
                ->alias('a')
                ->leftjoin('bulletin_target b', 'a.id = b.bulletin_id')
                ->leftjoin('bulletin_read c', 'a.id = c.bulletin_id')
                ->whereRaw("((target_type=1 or target_type = 2) and target_person = '$number') or (target_type = 3 and target_person='$info_post') or (target_type = 4)")
                // ->fieldRaw("SUM(CASE WHEN c.read_time is not null and c.target_number = '$number' THEN 1 ELSE 0 END) AS unread")
                ->group('a.id')

                ->count();
            $readed_count = Db::table('bulletin')
                ->alias('a')
                ->leftjoin('bulletin_target b', 'a.id = b.bulletin_id')
                ->leftjoin('bulletin_read c', 'a.id = c.bulletin_id')
                ->whereRaw("((target_type=1 or target_type = 2) and target_person = '$number') or (target_type = 3 and target_person='$info_post') or (target_type = 4)")
                ->fieldRaw("SUM(CASE WHEN c.read_time is not null and c.target_number = '$number' THEN 1 ELSE 0 END) AS unread")
                ->find();

            $count = (int)$all_count - (int)$readed_count['unread'];
            //code...
        } catch (\Exception $e) {
            return  $e->getMessage();
        }


        return $count;
    }


    //over
}
