<?php


/*
 * @Description: 
 * @Author: 罗曼
 * @Date: 2020-12-07 02:56:02
 * @FilePath: \testd:\wamp64\www\thinkphp-api\app\controller\DataBack.php
 * @LastEditTime: 2020-12-07 02:59:15
 * @LastEditors: 罗曼
 */
namespace app\controller;

// use PHPExcel_IOFactory;

// use think\Db;



class DataBack extends Base
{

    // public function dataBack()
    // {
    //     $post = request()->param();
    //     return $this->restoreSql($post['file_name']);
    // }
    public function backupSql($dbname = 'test', $backupFile = false)
    {
        // $dbhost = '127.0.0.1';config
        $dbhost = config('database.connections.mysql.hostname');
        $dbuser = config('database.connections.mysql.username');
        $dbpass = config('database.connections.mysql.password');
        if ($backupFile === false) {
            $backupFile = 'E:/backup/' . $dbname . '_' . date("Y-m-d_His") . '.sql';
        }
        if ($dbpass === '') {
            exec("D:\wamp64\bin\mysql\mysql5.7.24\bin\mysqldump -h $dbhost -u$dbuser  $dbname > $backupFile");
        } else {
            exec("D:\wamp64\bin\mysql\mysql5.7.24\bin\mysqldump -h $dbhost -u$dbuser -p$dbpass  $dbname > $backupFile");
        }
        return  stripslashes($backupFile);
    }

    public function restoreSql($backupFile = false, $dbname = 'test')
    {

        // $dbhost = '127.0.0.1';config
        $dbhost = config('database.connections.mysql.hostname');
        $dbuser = config('database.connections.mysql.username');
        $dbpass = config('database.connections.mysql.password');
        $backupFile=stripslashes($backupFile);
        if ($dbpass === '') {
            exec("D:\wamp64\bin\mysql\mysql5.7.24\bin\mysql -h $dbhost -u$dbuser  $dbname < $backupFile");
        } else {
            exec("D:\wamp64\bin\mysql\mysql5.7.24\bin\mysql  -h $dbhost -u$dbuser -p$dbpass  $dbname < $backupFile");
        }
        return $backupFile;
    }





    //over
}
