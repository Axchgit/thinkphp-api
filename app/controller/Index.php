<?php
/*
 * @Description: 
 * @Author: 罗曼
 * @Date: 2020-09-12 02:32:00
 * @FilePath: \testd:\wamp64\www\thinkphp-api\app\controller\Index.php
 * @LastEditTime: 2020-11-26 15:29:21
 * @LastEditors: 罗曼
 */

namespace app\controller;

use app\BaseController;
use think\Request;

use app\model\Person as PersonModel;
use app\model\PersonAccount as PersonAccountModel;
use app\model\JoinApply as JoinApplyModel;
use app\model\RecruitPartyMember as RecruitPartyMemberModel;
use app\model\LoginRecord as LoginRecordModel;
use app\model\Bulletin as BullteinModel;

use app\model\DownloadFile as DownloadFileModel;




class Index extends Base
{
    public function getProfile(Request $request)
    {
        $tooken_res = $request->data;
        $number = $tooken_res['data']->uuid;
        // return $number;

        $person_model = new PersonModel();
        $lg_model = new LoginRecordModel();

        $login_record = $lg_model->selectRecord($number);
        return $this->create(['login_record' => $login_record], '查询成功');
    }
    //获取未读通告统计
    public function getCountUnreadBulletin(Request $request)
    {
        $tooken_res = $request->data;
        $number = $tooken_res['data']->uuid;
        $person_model = new PersonModel();
        $bulletin_model = new BullteinModel();

        $info_post = $person_model->getInfoByNumber($number, 'post');
        $count = $bulletin_model->countUnreadBulletin($info_post, $number);
        if (is_int($count)) {
            return $this->create(['unread' => $count], '查询成功');
        } else {
            return $this->create($count, '查询失败', 204);
        }
    }

    //获取公共文件
    public function viewPublicFile()
    {
        $post = request()->param();
        $df_model = new DownloadFileModel();
        $list_rows = !empty($post['list_rows']) ? $post['list_rows'] : '';
        $res = $df_model->selectFileList($list_rows, ['query' => $post], $post);
        if ($res[0] === true) {
            return $this->create($res[1], '成功');
        }
        return $this->create($res[1], '失败', 204);
    }














    public function getDict()
    {
        $person_model = new PersonModel();
        return [0 => 1];
        return $person_model->getDictFromJson();
    }

    public function getJsonDataByFileName()
    {
        $post =  request()->param();
        $person_model = new PersonModel();
        return $person_model->getJson($post['json_file_name']);
    }






















    public function index()
    {
        return '<style type="text/css">*{ padding: 0; margin: 0; } div{ padding: 4px 48px;} a{color:#2E5CD5;cursor: pointer;text-decoration: none} a:hover{text-decoration:underline; } body{ background: #fff; font-family: "Century Gothic","Microsoft yahei"; color: #333;font-size:18px;} h1{ font-size: 100px; font-weight: normal; margin-bottom: 12px; } p{ line-height: 1.6em; font-size: 42px }</style><div style="padding: 24px 48px;"> <h1>:) </h1><p> ThinkPHP V' . \think\facade\App::version() . '<br/><span style="font-size:30px;">14载初心不改 - 你值得信赖的PHP框架</span></p><span style="font-size:25px;">[ V6.0 版本由 <a href="https://www.yisu.com/" target="yisu">亿速云</a> 独家赞助发布 ]</span></div><script type="text/javascript" src="https://tajs.qq.com/stats?sId=64890268" charset="UTF-8"></script><script type="text/javascript" src="https://e.topthink.com/Public/static/client.js"></script><think id="ee9b1aa918103c4fc"></think>';
    }

    public function hello($name = 'ThinkPHP6')
    {
        return 'hello,' . $name;
    }








    //over
}
