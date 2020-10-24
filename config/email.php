<?php
/*
 * @Description: 
 * @Author: 罗曼
 * @Date: 2020-09-12 02:32:00
 * @FilePath: \testd:\wamp64\www\thinkphp-api\config\email.php
 * @LastEditTime: 2020-10-23 11:22:27
 * @LastEditors: 罗曼
 */
// +----------------------------------------------------------------------
// | 发送邮件设置
// +----------------------------------------------------------------------

return [
    //smtp服务器的名称
    'host'          => 'smtp.qq.com', 
    //发件人主机域名
    'host_name'     => 'www.xchtzon.top', 
    //启用smtp认证
    'smtp_auth'     =>  TRUE, 
    //SMTP服务器端口
    'port'          => '465', 
    //你的邮箱名
    'user_name'     => '1027854092@qq.com', 
    //发件人地址
    'from'          => '1027854092@qq.com', 
    //发件人姓名
    'from_name'     => '河池学院党支部', 
    //邮箱密码切记是邮箱授权码
    'password'      => 'jymsvcmwcmrbbfcj', 
    //设置邮件编码
    'charset'       => 'utf-8', 
    // 是否HTML格式邮件
    'isHTML'        =>  TRUE, 


];
