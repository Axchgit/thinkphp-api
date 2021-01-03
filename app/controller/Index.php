<?php
/*
 * @Description: 
 * @Author: 罗曼
 * @Date: 2020-09-12 02:32:00
 * @FilePath: \testd:\wamp64\www\thinkphp-api\app\controller\Index.php
 * @LastEditTime: 2021-01-03 15:41:26
 * @LastEditors: xch
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
use app\model\TempCode as TempCodeModel;





class Index extends Base
{
    //获取账户资料
    public function getProfile(Request $request)
    {
        $tooken_res = $request->data;
        $number = $tooken_res['data']->uuid;
        // return $number;

        // $person_model = new PersonModel();
        $lg_model = new LoginRecordModel();
        $pa_model = new PersonAccountModel();

        $pa_info = $pa_model->getAllInfoByNumber($number);
        $login_record = $lg_model->selectRecord($number);
        return $this->create(['login_record' => $login_record, 'pa_info' => $pa_info], '查询成功');
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
    /**
     * @description: 发送登录码
     * @param {type} 
     * @return {type} 
     */
    public function sendEmailCode(Request $request)
    {
        $post =  request()->param();
        $tooken_res = $request->data;

        $number = $tooken_res['data']->uuid;
        $person_model = new PersonModel();
        $code_model = new TempCodeModel();

        //PHP获得随机数-验证码
        $v_code = rand(111111, 999999);
        //PHP获取时间戳
        $time = time();
        //拼接时间戳与登录码
        $log_code = (string)$time . (string)$v_code;

        //删除之前的登录码
        $code_model->deleteCode($number);
        //保存登录码信息到临时表
        $res =  $code_model->saveCode($number, $log_code, $post['msg'] . '验证码');
        //字符串截取指定片段
        $v_code = substr($log_code, 10, 6);
        //查询账户对应email
        $person_info = $person_model->getAllInfoByNumber($number);

        // $person_email = $person_model->getInfoByNumber($post['number'], 'email');
        $title = '验证码';
        // $data = json_decode($string, true);
        $content = emailHtmlModel($person_info['name'], $v_code, $post['msg']);
        // return $this->create($content);

        // $content = '你好, <b>' . $person_info['name'] . '</b>管理员! <br/>这是一封来自河池学院党支部的邮件！<br/><span>你正在登录管理员账户,你的验证码是:' . (string)$v_code;

        // $content = '你好, <b>朋友</b>! <br/><br/><span>你的验证码是:' . (string)$code;
        if ($res === true) {
            if (sendMail($person_info['email'], $title, $content)) {
                $code = 200;
                $msg = '发送成功';
            } else {
                $code = 204;
                $msg = '发送失败';
            }
        } else {
            $code = 204;
            $msg = $res;
        }
        return $this->create('', $msg, $code);
    }

    //上传头像
    public function uploadAvatar(Request $request)
    {
        $post = request()->param();
        // return $this->create('', $post, 204);
        $file = request()->file('img');
        // return $this->create($file, '上传成功', 200);

        $tooken_res = $request->data;
        $number = $tooken_res['data']->uuid;
        $pa_model = new PersonAccountModel();

        try {
            validate(['file' => ['fileSize:1024000', 'fileExt:jpg,png,gif']])->check(['file' => $file]);
            $savename = \think\facade\Filesystem::disk('avatar')->putFile('avatar', $file, 'md5');
            $res = $pa_model->updateByNumber($number, ['id_photo' => $savename]);
            if ($res === true) {
                return $this->create($savename, '上传成功', 200);
            }
            return $this->create($res, '修改头像失败，数据库出错', 204);
        } catch (\think\exception\ValidateException $e) {
            return $this->create('', $e->getMessage(), 204);
        }
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
