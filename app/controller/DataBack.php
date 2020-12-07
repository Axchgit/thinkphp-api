<?php


/*
 * @Description: 
 * @Author: 罗曼
 * @Date: 2020-12-07 02:56:02
 * @FilePath: \testd:\wamp64\www\thinkphp-api\app\controller\DataBack.php
 * @LastEditTime: 2020-12-07 14:15:35
 * @LastEditors: 罗曼
 */

namespace app\controller;

// use PHPExcel_IOFactory;

// use think\Db;



class DataBack extends Base
{
    //查看目录内的文件和目录，并按生成时间排序
    function getDirContent($file_path = 'E:/backup/')
    {
        //要查看的目录
        // $file_path = '../extend/';

        //判断 Mac 是否有 DS_Store，拉取文件是否有.gitkeep、.keep，并排除
        $files = [];
        $file = scandir($file_path, 1);
        if (!empty($file)) {
            foreach ($file as $k => $v) {
                if ($v != '.' && $v != '..' && $v != '.DS_Store' && $v != '.gitkeep' && $v != '.keep') {
                    $files[] = $v;
                }
            }
        }

        $list = [];
        if (is_array($files)) {
            foreach ($files as $k => $v) {
                $filesize = filesize($file_path . $v);
                if ($filesize < 1024) {
                    $size = sprintf("%01.2f", $filesize) . "B";
                } elseif ($filesize < 1024 * 1024) {
                    $size = sprintf("%01.2f", ($filesize / 1024)) . "KB";
                } elseif ($filesize < 1024 * 1024 * 1024) {
                    $size = sprintf("%01.2f", ($filesize / (1024 * 1024))) . "MB";
                } elseif ($filesize < 1024 * 1024 * 1024 * 1024) {
                    $size = sprintf("%01.2f", ($filesize / (1024 * 1024 * 1024))) . "GB";
                }
                $list[] = [
                    'file' =>$file_path. $v,
                    'size' => $size,
                    'create_time' => filemtime($file_path . $v),
                    'create_date' => date('Y-m-d H:i:s', filemtime($file_path . $v)),
                ];
            }
        }

        //根据文件和目录生成时间按倒序排列
        $list = $this->arraySort($list, 'create_time', SORT_DESC);
        
        return $this->create($list,'成功');

        // echo '<pre>';
        // print_r($list);
        // die;
        // echo '</pre>';
    }

    /**
     * 二维数组根据某个字段排序
     * @param array $array	要排序的数组
     * @param string $keys	要排序的键字段
     * @param string $sort	排序类型: SORT_ASC 升序, SORT_DESC 降序
     * @return array 		排序后的数组
     */
    public function arraySort($array, $keys, $sort = SORT_DESC)
    {
        $keysValue = [];
        foreach ($array as $k => $v) {
            $keysValue[$k] = $v[$keys];
        }
        array_multisort($keysValue, $sort, $array);
        return $array;
    }


    /**
     * @description: 数据库备份
     *@param string $dbname

     *@param bool $backupFile

     *@return {*}
     */
    public function backupSql($dbname = 'test', string $backupFile = '+_+_',$mysqldump_path ="D:\/wamp64\/bin\/mysql\/mysql5.7.24\/bin\/")
    {
        // $dbhost = '127.0.0.1';config
        $dbhost = config('database.connections.mysql.hostname');
        $dbuser = config('database.connections.mysql.username');
        $dbpass = config('database.connections.mysql.password');
        if ($backupFile === '+_+_') {
            $backupFile = 'E:/backup/' . $dbname . '_' . date("Y-m-d_His") . '.sql';
        }
        if ($dbpass === '') {
            exec($mysqldump_path."mysqldump -h $dbhost -u$dbuser  $dbname > $backupFile");
        } else {
            exec($mysqldump_path."mysqldump -h $dbhost -u$dbuser -p$dbpass  $dbname > $backupFile");
        }
        return  stripslashes($backupFile);
    }
    /**
     * @description: 数据库还原
     *@param string $dbname

     *@param string $backupFile

     *@return {*}
     */
    public function restoreSql($backupFile, $dbname = 'test',$mysqldump_path ="D:\/wamp64\/bin\/mysql\/mysql5.7.24\/bin\/")
    {

        // $dbhost = '127.0.0.1';config
        $dbhost = config('database.connections.mysql.hostname');
        $dbuser = config('database.connections.mysql.username');
        $dbpass = config('database.connections.mysql.password');
        $backupFile = stripslashes($backupFile);
        if ($dbpass === '') {
            exec($mysqldump_path."mysql -h $dbhost -u$dbuser  $dbname < $backupFile");
        } else {
            exec($mysqldump_path."mysql  -h $dbhost -u$dbuser -p$dbpass  $dbname < $backupFile");
        }
        return $backupFile;
    }





    //over
}
