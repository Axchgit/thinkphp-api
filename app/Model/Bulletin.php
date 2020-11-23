<?php
/*
 * @Description: 
 * @Author: 罗曼
 * @Date: 2020-11-23 01:30:43
 * @FilePath: \testd:\wamp64\www\thinkphp-api\app\Model\Bulletin.php
 * @LastEditTime: 2020-11-23 12:55:05
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
    public function getBulletin($number,$post)
    {
        $count = Db::table('bulletin')
            ->alias('a')
            ->leftjoin('bulletin_target b', 'a.id = b.bulletin_id')
            ->leftjoin('bulletin_read c', 'a.id = c.bulletin_id')

            ->field('a.*')
            ->field('c.read_time')
            // ->fieldRaw('count(*) AS 人数')
            // ->fieldRaw('SUM(CASE WHEN c.read_time != null THEN 1 ELSE 0 END) AS reading')
            ->fieldRaw('(CASE WHEN c.read_time <> null THEN 1 ELSE 2 END) AS reading')





            //查询条件为空时,忽略该条件
            ->whereRaw("((target_type=1 or target_type = 2) and target_person = '$number') or (target_type = 3 and target_person='$post') or (target_type = 4)")
            // ->where('step', 1)
            // // ->fetchSql(true)
            // ->field('a.nation as 民族')
            // ->fieldRaw('count(*) AS 人数')
            // ->group('nation')
            ->select();
        return $count;
    }
    // (target_type = ‘指定用户或多个用户’ AND user = ‘用户id’) OR (target_type = ‘指定的用户群体’ AND user = ‘用户群体’ ) OR (target_type = ‘全部’)



    //over
}
