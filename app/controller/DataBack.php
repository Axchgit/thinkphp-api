<?php


/*
 * @Description: 
 * @Author: 罗曼
 * @Date: 2020-12-07 02:56:02
 * @FilePath: \testd:\wamp64\www\thinkphp-api\app\controller\DataBack.php
 * @LastEditTime: 2020-12-11 10:17:30
 * @LastEditors: 罗曼
 */

namespace app\controller;

// use PHPExcel_IOFactory;

// use think\Db;



class DataBack extends Base
{

    //获取备份文件列表
    public function viewBackupFile()
    {
        $post = request()->param();
        $file_path = !empty($post['file_path'])?$post['file_path']:'D:\/backup\/';
        $res = $this->getDirContent( $file_path);
        if ($res[0] !== true) {
            return $this->create('', '系统错误');
        }
        return $this->create($res[1], '成功');
    }
    //备份数据库
    public function backupSqlApi(){
        $post = request()->param();
        $dbname = !empty($post['dbname'])?$post['dbname']:'test';
        $path = !empty($post['path'])?$post['path']:'+_+';
        $this->backupSql($dbname,$path);
        return $this->create('', '成功');
    }
    //数据库恢复数据
    public function restoreThisSqlByBackupFile()
    {
        $post = request()->param();
        $dbname = !empty($post['dbname'])?$post['dbname']:config('database.connections.mysql.database');
        $this->restoreSql($post['file'],$dbname);
        return $this->create('', '成功');
    }
    //删除数据库备份文件
    public function deleteBackupFile()
    {
        $post = request()->param();
        unlink($post['file']);
        return $this->create('', '成功');
    }



















    //查看目录内的文件和目录，并按生成时间排序
    function getDirContent($file_path)
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
                // explode("\\",$file_path);
                
                // substr($v,0,strpos($v, '_2'));
                // $new_file_path = stripslashes($file_path);
                $list[] = [
                    'index' => $k,
                    'file' => $file_path . $v,
                    // 'dbname' => explode("-", $v)[0],
                    'dbname' => substr($v,0,strpos($v, '_2')),
                    'size' => $size,
                    'create_time' => filemtime($file_path . $v),
                    'create_date' => date('Y-m-d H:i:s', filemtime($file_path . $v)),
                ];
            }
        }

        //根据文件和目录生成时间按倒序排列
        $list = $this->arraySort($list, 'create_time', SORT_DESC);
        if (empty($list)) {
            return [false];
        } else {
            return [true, $list];
        }

        // return $this->create($list,'成功');

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
    public function backupSql($dbname = 'party_api', string $backupFile = '+_+', $mysqldump_path = "D:\/wamp64\/bin\/mysql\/mysql5.7.24\/bin\/")
    {
        // $dbhost = '127.0.0.1';config
        $dbhost = config('database.connections.mysql.hostname');
        $dbuser = config('database.connections.mysql.username');
        $dbpass = config('database.connections.mysql.password');

        
        if ($backupFile === '+_+') {
            $backupFile = 'D:/backup/' . $dbname . '_' . date("Y-m-d_His") . '.sql';
        }
        if ($dbpass === '') {
            exec($mysqldump_path . "mysqldump -h $dbhost -u$dbuser  $dbname > $backupFile");
        } else {
            exec($mysqldump_path . "mysqldump -h $dbhost -u$dbuser -p$dbpass  $dbname > $backupFile");
        }
        return  stripslashes($backupFile);
    }
    /**
     * @description: 数据库还原
     *@param string $dbname 数据库名称
     *@param string $backupFile 
     *@param string $mysqldump_path


     *@return {*}
     */
    public function restoreSql($backupFile, $dbname = 'party_api', $mysqldump_path = "D:\/wamp64\/bin\/mysql\/mysql5.7.24\/bin\/")
    {

        // $dbhost = '127.0.0.1';config
        $dbhost = config('database.connections.mysql.hostname');
        $dbuser = config('database.connections.mysql.username');
        $dbpass = config('database.connections.mysql.password');
        $backupFile = stripslashes($backupFile);
        if ($dbpass === '') {
            exec($mysqldump_path . "mysql -h $dbhost -u$dbuser  $dbname < $backupFile");
        } else {
            exec($mysqldump_path . "mysql  -h $dbhost -u$dbuser -p$dbpass  $dbname < $backupFile");
        }
        return $backupFile;
    }





    //over
}
