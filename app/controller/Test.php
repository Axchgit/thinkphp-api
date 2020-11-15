<?php
/*
 * @Author: xch
 * @Date: 2020-08-15 11:34:38
 * @LastEditTime: 2020-11-15 13:01:12
 * @LastEditors: 罗曼
 * @Description: 
 * @FilePath: \testd:\wamp64\www\thinkphp-api\app\controller\Test.php
 */




declare(strict_types=1);

namespace app\controller;

// use think\facade\Request;
use think\Request;
use app\model\Admin as AdminModel;
use think\facade\Db;
use app\model\Goods as GoodsModel;
use app\model\GoodsTemp as GoodsTempModel;

use app\model\RecruitPartyMember as RecruitPartyMemberModel;
use app\model\JoinApply as JoinApplyModel;




class Test extends Base
{
    public function ten(){
        $ja_model= new JoinApplyModel();
        return $ja_model->countLineCharts();
    }
    
    public function night()
    {
        $fileName = config('app.json_path') . '/options.json';
        $string = file_get_contents($fileName);
        $data = json_decode($string, true);
        $faculty = '02';

        // PHP数组查询
        // 定义要查找的是哪个键的值
        $found_arr = array_column($data, 'value');
        // var_dump($found_arr)
        $found_key = array_search($faculty, $found_arr);
        // $found_key = 0; 返回键名
        return $data[$found_key];
        return $data[0];
    }

    public function eight()
    {

        $goods_model = new GoodsModel();
        // return '132';
        return $goods_model->incrementalUpdata();
    }
    public function seven()
    {

        $post =  request()->param();
        $gt_model = new GoodsTempModel();
        $data = $gt_model->insertGoods($post);
        if ($data) {
            return $this->create('', '成功', 200);
        } else {
            return $this->create('', '失败', 204);
        }
        // $admin_model = new AdminModel();
        // $data = $admin_model->saveLogcode('呵粑粑牛',12321);

    }

    public function testOne()
    {
        // $admin_model = new AdminModel();
        // $data = $admin_model->saveLogcode('呵粑粑牛',12321);
        return json(true);
    }
    public function testTwo()
    {
        $rpm_model = new RecruitPartyMemberModel();


        // $admin_model = new AdminModel();
        // $data = $admin_model->deleteLogcode('呵粑粑牛');
        $test= '2020-11-02 22:46:48';
        return $rpm_model->testOne();
        return date("Y-m-d H:i:s")<$test;
        return time();
    }
    //获取一个uuid
    public function testThree()
    {
        // $admin_model = new AdminModel();
        // $data = $admin_model->deleteLogcode('呵粑粑牛');
        return createGuid();
    }
    public function testFour($uuid)
    {
        $records = [
            'uuid' => $uuid,
            'login_time' => time(),
            'login_ip' => request()->host()
        ];
        Db::table('login_record')->insert($records);
        return true;
    }
    //中间件传参
    public function testFive(Request $request)
    {
        $res = $request->data;
        // return $res;
        return $this->create($res['data']->uuid);
    }
    //前端表格数据
    public function testSix()
    {
        $post = request()->param();
        $goods = [];
        $dataArr = [
            [
                "订单号" => 124465937518,
                "父单号" => 0,
                "订单状态" => "待付款",
                "下单时间" => 44000.46486111111,
                "商品ID" => 10445274829,
            ],
            [
                "订单号" => 118921513114,
                "父单号" => 0,
                "订单状态" => "已付款",
                "下单时间" => 44000.46476851852,
                "商品ID" => 931177,
            ]
        ];

        foreach ($dataArr as $k => $v) {
            $goods[$k]['id'] = $v['订单号'];
            $goods[$k]['f_id'] = $v['父单号'];

            // return [$k=>$v];
        }
        return json($goods);

        // return json($post);
    }

    /**
     * @var \think\Request Request实例
     */
    protected $request;

    /**
     * 构造方法
     * @param Request $request Request对象
     * @access public
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
    }
    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index()
    {
        //

        return createGuid();

        return com_create_guid();
        return AdminModel::select();
    }
    /**
     * @description: 发送登录码
     * @param {type} 
     * @return {type} 
     */
    public function sendCode()
    {
        $post =  $this->request->param();
        //知识点:PHP获得随机数
        $code = rand(111111, 999999);
        //知识点:PHP获取时间戳
        $time = time();
        //拼接时间戳与登录码
        $log_code = $code + $time * 1000000;
        $admin_model = new AdminModel();
        $res =  $admin_model->saveLogCode($post['username'], $log_code);
        $string_code = (string)$log_code;
        $code = substr($string_code, 10, 6);
        $admin_email = $admin_model->selectMail($post['username']);
        $title = '登录码';
        $content = '你好, <b>朋友</b>! <br/>这是一封来自<a href="http://www.xchtzon.top"  
        target="_blank">学创科技</a>的邮件！<br/><span>你的验证码是:' . (string)$code;
        if ($res) {
            if (sendMail($admin_email, $title, $content)) {
                $code = 200;
                $msg = '发送成功';
                // return View::fetch('success');
            } else {
                $code = 204;
                $msg = '发送失败';
            }
        } else {
            $code = 204;
            $msg = '找不到收件人';
        }
        //知识点:PHP类型转换
        // $string_code = (string)$log_code;
        // $data = [
        //     'log_code' => $log_code,
        //     //知识点:字符串截取指定片段
        //     'code' => substr($string_code,10,6),  //截取出登录码
        //     'res' => $res
        // ];
        $data = [
            'code' => $code,
            'msg' => $msg,
            "data" => ["list" => '']
        ];

        return json($data);

        // return substr($string_code,10,3);
        // return $post['username'];
    }
    // public function checkAdminLogin(){
    //     $post =  $this->request->param();
    //     $string_code = (string)$post['logcode'];
    //     $code = substr($string_code, 10, 6);
    //     $admin_model = new AdminModel();
    //     $admin_model->

    // }

}
