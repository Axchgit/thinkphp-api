<?php
/*
 * @Author: 罗曼
 * @Date: 2020-08-17 22:03:01
 * @LastEditTime: 2020-12-18 16:22:53
 * @LastEditors: 罗曼
 * @FilePath: \testd:\wamp64\www\thinkphp-api\app\controller\Admin.php
 * @Description: 
 */

declare(strict_types=1);

namespace app\controller;

use think\Request;

use think\model\concern\SoftDelete;

use app\model\Person as PersonModel;
use app\model\PersonAccount as PersonAccountModel;
use app\model\JoinApply as JoinApplyModel;
use app\model\RecruitPartyMember as RecruitPartyMemberModel;
use app\model\Transfer as TransferModel;
use app\model\Material as MaterialModel;
use app\model\Bulletin as BullteinModel;
use app\model\DownloadFile as DownloadFileModel;


class Admin extends Base
{


    // protected $defaultSoftDelete = 0;


    public function importExcel()
    {
        $post =  request()->param();
        $person_model = new PersonModel();
        // return $this->create($post, '90', 204);

        $res = $person_model->insertPerson($post);
        if ($res === true) {
            return $this->create('成功', '添加成功', 200);
        } else {
            return $this->create('', $res, 200);
        }
    }

    //导入考核成绩
    public function importMaterialExcel()
    {
        $post =  request()->param();
        $material_model = new MaterialModel();
        // return $this->create($post, '90', 204);
        $res = $material_model->insertMaterial($post);
        if ($res === true) {
            return $this->create('成功', '添加成功', 200);
        } else {
            return $this->create('', $res, 200);
        }
    }
    //浏览考核成绩
    public function viewMaterial(Request $request)
    {
        $post = request()->param();

        $tooken_res = $request->data;
        $number = $tooken_res['data']->uuid;
        $role = $tooken_res['data']->role;

        $material_model = new MaterialModel();
        $person_model = new PersonModel();
        //权限为4需要加查询条件
        $faculty = '';
        if ($role === 4) {
            $faculty = $person_model->getInfoByNumber($number, 'faculty');
        }
        $list_rows = !empty($post['list_rows']) ? $post['list_rows'] : '';
        // $list_rows, ['query' => $post], $faculty, $post, $role
        $res = $material_model->getMaterial($list_rows, ['query' => $post], $faculty, $post, $role);
        if ($res[0] === true) {
            return $this->create($res[1], '成功');
        }
        return $this->create($res[1], '失败', 204);
    }

    public function editMaterial(Request $request)
    {
        $post = request()->param();

        $tooken_res = $request->data;
        // $number = $tooken_res['data']->uuid;
        $role = $tooken_res['data']->role;
        $material_model = new MaterialModel();
        // $person_model = new PersonModel();
        try {
            if ($post['materialOne'] !== true) {
                if ($material_model->where(['number' => $post['number'], 'category' => 1])->find() !== null) {
                    $res = $material_model->where(['number' => $post['number'], 'category' => 1])->update(['serial_number' => $post['serial_number_1'], 'score' => $post['score_1']]);
                } else {
                    $res = $material_model->save(['number' => $post['number'], 'category' => 1, 'serial_number' => $post['serial_number_1'], 'score' => $post['score_1']]);
                }
            }
            if ($post['materialTwo'] !== true) {
                if ($material_model->where(['number' => $post['number'], 'category' => 2])->find() !== null) {
                    $res = $material_model->where(['number' => $post['number'], 'category' => 2])->update(['serial_number' => $post['serial_number_2'], 'score' => $post['score_2']]);
                } else {
                    $res = $material_model->save(['number' => $post['number'], 'category' => 2, 'serial_number' => $post['serial_number_2'], 'score' => $post['score_2']]);
                }
            }
            if ($post['materialThree'] !== true) {
                if ($material_model->where(['number' => $post['number'], 'category' => 3])->find() !== null) {
                    $res = $material_model->where(['number' => $post['number'], 'category' => 3])->update(['serial_number' => $post['serial_number_3'], 'score' => $post['score_3']]);
                } else {
                    $res = $material_model->save(['number' => $post['number'], 'category' => 3, 'serial_number' => $post['serial_number_3'], 'score' => $post['score_3']]);
                }
            }
            return $this->create($res, '成功');
        } catch (\Exception $e) {
            return $this->create('', $e->getMessage(), 204);
        }
    }
    /***********人员信息 */
    //查询人员信息
    public function selectPerson()
    {

        $post =  request()->param();
        // $res = $request->data;
        $person_model = new PersonModel();
        $val = !empty($post['number']) || !empty($post['name']);
        $key = '';
        $value = '';
        // return $val;
        if ($val) {
            $key = !empty($post['number']) ? 'number' : 'name';
            $value = urldecode($post[$key]);
            // return $key;
        }
        // $key = !empty($post['key']) ? $post['key'] : '';
        // $value = !empty($post['value']) ? $post['value'] : '';
        $list_rows = !empty($post['list_rows']) ? $post['list_rows'] : '';
        $data = $person_model->selectPerson($key, $value, $list_rows, false, ['query' => $post]);
        if ($data) {
            return $this->create($data, '查询成功');
        } else {
            return $this->create($data, '暂无数据', 204);
        }
    }


    //修改人员权限
    public function updatePersonRole()
    {
        $post =  request()->param();
        $person_model = new PersonModel();
        $res = $person_model->updatePerson(['id' => $post['id'], 'role' => $post['role'], 'number' => $post['number']]);
        if ($res === true) {
            return $this->create('', '修改成功', 200);
        } else {
            return $this->create('', $res, 204);
        }
    }

    //修改人员信息
    public function updatePerson()
    {
        $post =  request()->param();
        $person_model = new PersonModel();
        $update_post = array_diff_key($post, ["party_branch" => '&*&*&']);
        $res = $person_model->updatePerson($update_post);
        if ($res === true) {
            return $this->create('', '修改成功', 200);
        } else {
            return $this->create('', $res, 204);
        }
    }

    //修改人员信息
    public function deletePerson()
    {
        $post =  request()->param();
        $person_model = new PersonModel();
        $res = $person_model->deletePerson($post['id']);
        if ($res === true) {
            return $this->create('', '人员信息删除成功', 200);
        } else {
            return $this->create('', $res, 204);
        }
    }

    /***********人员账户信息 */
    //查询人员账户
    public function selectPersonAccount()
    {
        $post =  request()->param();
        // $res = $request->data;
        $person_model = new PersonModel();
        $pa_model = new PersonAccountModel();
        $val = !empty($post['number']) || !empty($post['name']);
        $key = '';
        $value = '';
        // return $val;
        if ($val) {
            $key = !empty($post['number']) ? 'number' : 'name';
            $value = urldecode($post[$key]);
            // return $key;
        }
        // $key = !empty($post['key']) ? $post['key'] : '';
        // $value = !empty($post['value']) ? $post['value'] : '';
        $list_rows = !empty($post['list_rows']) ? $post['list_rows'] : '';
        $resArr = $pa_model->selectPersonAccount($key, $value, $list_rows, false, ['query' => $post]);
        foreach ($resArr as $k => $v) {
            $person_info = $person_model->getAllInfoByNumber($v['number']);
            $resArr[$k]['name'] = $person_info['name'];
            $resArr[$k]['role'] = $person_info['role'];
            $resArr[$k]['post'] = $person_info['post'];
            $resArr[$k]['phone_number'] = $person_info['phone_number'];
            $resArr[$k]['comment'] = $person_info['comment'];
        }
        if ($resArr) {
            return $this->create($resArr, '查询成功');
        } else {
            return $this->create('', '暂无数据', 204);
        }
    }

    //修改人员账户
    public function updatePersonAccount()
    {
        $post =  request()->param();
        $pa_model = new PersonAccountModel();
        $res = $pa_model->updatePersonAccount($post);
        if ($res === true) {
            return $this->create('', '修改成功', 200);
        } else {
            return $this->create('', $res, 204);
        }
    }

    //删除人员账户
    public function deletePersonAccount()
    {
        $post =  request()->param();
        $pa_model = new PersonAccountModel();
        $res = $pa_model->deletePersonAccount($post['id']);
        if ($res === true) {
            return $this->create('', '人员信息删除成功', 200);
        } else {
            return $this->create('', $res, 204);
        }
    }
    /*************** */

    //一二级管理员浏览人员信息
    public function viewAllPerson(Request $request)
    {
        $post = request()->param();

        // return json($post);
        $tooken_res = $request->data;
        $number = $tooken_res['data']->uuid;
        $role = $tooken_res['data']->role;
        $person_model = new PersonModel();
        //权限为4需要加查询条件
        $faculty = '';
        if ($role === 4) {
            $faculty = $person_model->getInfoByNumber($number, 'faculty');
        }
        if (!empty($post['faculty'])) {
            $post['faculty'] = strlen($post['faculty']) > 1 ? (string)$post['faculty'] : '0' . (string)$post['faculty'];
        }
        $list_rows = !empty($post['list_rows']) ? $post['list_rows'] : '';
        $list = $person_model->getAllPerson($list_rows, ['query' => $post], $faculty, $post);
        // if ($list) {
        return $this->create($list, '查询成功');
        // } else {
        //     return $this->create($list, '暂无数据');
        // }
    }

    //一二级管理员浏览人员账户信息
    public function viewAllPersonAccount(Request $request)
    {
        $post = request()->param();

        // return json($post);
        $tooken_res = $request->data;
        $number = $tooken_res['data']->uuid;
        $role = $tooken_res['data']->role;

        $person_model = new PersonModel();
        $pa_model = new PersonAccountModel();
        //权限为4需要加查询条件
        $faculty = '';
        if ($role === 4) {
            $faculty = $person_model->getInfoByNumber($number, 'faculty');
        }
        $list_rows = !empty($post['list_rows']) ? $post['list_rows'] : '';
        $list = $pa_model->getAllPersonAccount($list_rows,  ['query' => $post], $faculty, $post);
        // if ($list) {
        return $this->create($list, '查询成功');
        // } else {
        //     return $this->create($list, '暂无数据');
        // }
    }

    //浏览查询申请列表
    public function viewApply(Request $request)
    {
        $post = request()->param();

        // return json($post);
        $tooken_res = $request->data;
        $number = $tooken_res['data']->uuid;
        $role = $tooken_res['data']->role;
        $person_model = new PersonModel();
        $ja_model = new JoinApplyModel();
        //权限为4需要加查询条件
        $faculty = '';
        if ($role === 4) {
            $faculty = $person_model->getInfoByNumber($number, 'faculty');
        }
        if (!empty($post['faculty'])) {
            $post['faculty'] = strlen($post['faculty']) > 1 ? (string)$post['faculty'] : '0' . (string)$post['faculty'];
        }
        $list_rows = !empty($post['list_rows']) ? $post['list_rows'] : '';
        $list = $ja_model->getAllApply($list_rows, ['query' => $post], $faculty, $post, $role);
        // $data = json($list);
        // return gettype($list);

        // foreach($list as $k=>$v){
        //         $list[$k]['party_branch'] = $person_model->getJsonData('options.json',$v['party_branch'],true);

        // }
        return $this->create($list, '查询成功');
    }

    //审核申请
    public function reviewApply(Request $request)
    {
        $tooken_res = $request->data;
        $role = $tooken_res['data']->role;
        $is_high_admin = $role < 4;
        $number = $tooken_res['data']->uuid;
        $post = request()->param();
        $person_model = new PersonModel();
        $ja_model = new JoinApplyModel();
        $rpm_model = new RecruitPartyMemberModel();

        // $post['introducer'] = $ja_model->getApplyById($post['id'],'remarks');

        $introducer = $ja_model->getApplyById($post['id'], 'remarks');

        $post['reviewer'] = $number;
        $post['remarks'] = '';
        $ja_res = $ja_model->updateJoinApply($post);
        // $post['stage'] = $post['step'] == 1 ? 1 : $post['step'] + 2;
        if ($post['review_status'] == 2) {
            //根据申请步骤判断审核后操作
            switch ($post['step']) {
                case 1:
                    $post['stage'] = 1;
                    $rpm_res = $rpm_model->createRecruit($post);
                    if ($rpm_res === true) {
                        $post['stage'] = 2;
                        $post['stage_time'] = date("Y-m-d H:i:s", strtotime("+3 month"));
                        $rpm_res = $rpm_model->createRecruit($post);
                    }
                    break;
                case 2:
                    $post['stage'] = 4;
                    $rpm_res = $rpm_model->createRecruit($post);
                    if ($rpm_res === true) {
                        $post = array_diff_key($post, ["contacts" => 0, "introducer" => 0]);
                        $post['stage'] = 3;
                        $post['stage_time'] = date("Y-m-d H:i:s", strtotime("-15 day"));
                        $rpm_res = $rpm_model->createRecruit($post);
                    }
                    break;
                case 3:
                    // if ($post['review_status'] == 2) {
                    $post['review_status'] = 4;
                    $post['remarks'] = $post['introducer'];
                    $ja_res = $ja_model->updateJoinApply($post);
                    // } 
                    // else if ($post['review_status'] == 2) {
                    $post['stage'] = 5;
                    $post['introducer'] = $introducer;
                    $rpm_res = $rpm_model->createRecruit($post);
                    break;
                    // }

                case 4:
                    // if ($post['review_status'] == 2) {
                    //申请通过后,修改人员政治面貌
                    $res_person = $person_model->updateByNumber($post['number'], ['political_status' => 3]);
                    if ($res_person !== true) {
                        return $this->create('',  '系统未知错误', 204);
                    }
                    // $post['review_status'] = 4;
                    $ja_res = $ja_model->updateJoinApply($post);
                    // }
                    $post['stage'] = 6;
                    $rpm_res = $rpm_model->createRecruit($post);
                    if ($rpm_res === true) {
                        $post['stage'] = 7;
                        $post['stage_time'] = date("Y-m-d H:i:s", strtotime("+7 day"));
                        $rpm_res = $rpm_model->createRecruit($post);
                    }
                    break;
                case 5:
                    // if ($post['review_status'] == 2) {
                    //申请通过后,修改人员政治面貌
                    $res_person = $person_model->updateByNumber($post['number'], ['political_status' => 4]);
                    if ($res_person !== true) {
                        return $this->create('',  '系统未知错误', 204);
                    }
                    // $post['review_status'] = 4;
                    $ja_res = $ja_model->updateJoinApply($post);
                    // }
                    $post['stage'] = 8;
                    $rpm_res = $rpm_model->createRecruit($post);
                    if ($rpm_res === true) {
                        $post['stage'] = 9;
                        $post['stage_time'] = date("Y-m-d H:i:s", strtotime("+7 day"));
                        $rpm_res = $rpm_model->createRecruit($post);
                    }
                    # code...
                    break;
            }
        } else {
            $rpm_res = true;
            $ja_res === true;
        }



        //当审核未通过时,删除发展党员信息
        // if ($post['review_status'] != 2) {
        //     $rpm_res = $rpm_model->deleteRecruit([['number', '=', $post['number']], ['stage', '>=', $post['stage']]]);
        //     return $this->create($post, $rpm_res);
        // }



        if ($ja_res === true && $rpm_res === true) {
            return $this->create($post,  '审核成功');
        } else {
            return $this->create($post, ['审核失败', $ja_res, $rpm_res], 204);
        }
    }

    //浏览发展党员列表
    public function viewRecruit(Request $request)
    {
        $post = request()->param();

        // return json($post);
        $tooken_res = $request->data;
        $number = $tooken_res['data']->uuid;
        $role = $tooken_res['data']->role;
        $person_model = new PersonModel();
        $rpm_model = new RecruitPartyMemberModel();

        //权限为4需要加查询条件
        $faculty = '';
        if ($role === 4) {
            $faculty = $person_model->getInfoByNumber($number, 'faculty');
        }
        $list_rows = !empty($post['list_rows']) ? $post['list_rows'] : '';
        $list = $rpm_model->getRecruit($list_rows, ['query' => $post], $faculty, $post, $role);
        return $this->create($list, '查询成功');
    }
    //修改发展党员信息
    public function updateRecruitCI()
    {
        $post = request()->param();
        $rpm_model = new RecruitPartyMemberModel();
        if (!empty($post['stage3'])) {
            $post_select = [
                ['number', '=', $post['number']],
                ['stage', '=', 4],

            ];
            $post_update['id'] = $rpm_model->getAllByPost($post_select)['id'];
            $post_update['contacts'] = $post['contacts'];
            $res = $rpm_model->updateRecruit($post_update);
            $post_update = [];
            // return json([$post_update,$post_select,$res]);
        }
        if (!empty($post['stage4'])) {
            $post_select = [
                ['number', '=', $post['number']],
                ['stage', '=', 5],
            ];
            $post_update['id'] = $rpm_model->getAllByPost($post_select)['id'];
            $post_update['introducer'] = $post['introducer'];
            $res = $rpm_model->updateRecruit($post_update);
        }
        if ($res === true) {
            return $this->create('', '修改成功');
        }
        return $this->create($res, '修改失败', 204);
    }

    //删除发展党员信息
    public function deleteRecruit()
    {
        $post = request()->param();
        $rpm_model = new RecruitPartyMemberModel();

        $rpm_res = $rpm_model->deleteRecruit([['number', '=', $post['number']]]);
        if ($rpm_res === true) {
            return $this->create('', '删除成功');
        }
        return $this->create($rpm_res, '删除失败', 204);
    }

    //获取组织关系转接申请信息
    public function viewTransferApply(Request $request)
    {
        $post = request()->param();

        // return json($post);
        $tooken_res = $request->data;
        $number = $tooken_res['data']->uuid;
        $role = $tooken_res['data']->role;
        $person_model = new PersonModel();
        $transfer_model = new TransferModel();

        $list_rows = !empty($post['list_rows']) ? $post['list_rows'] : '';
        if ($role === 3) {
            $post_string = 'review_steps >= 3';
        } else if ($role === 4) {
            $faculty = $person_model->getInfoByNumber($number, 'faculty');
            $post_string = 'faculty = ' . $faculty . ' or(receive_faculty = ' . $faculty . ' and review_steps>=2)';
        }
        $list = $transfer_model->getAllApply($list_rows, ['query' => $post], $post, $post_string, $role);
        return $list;
    }

    //审核组织关系转出申请
    public function reviewTransferApply(Request $request)
    {
        $post = request()->param();
        $tooken_res = $request->data;
        $number = $tooken_res['data']->uuid;
        $role = $tooken_res['data']->role;
        $person_model = new PersonModel();
        $transfer_model = new TransferModel();
        switch ($post['review_steps']) {
            case '1':
                $post['out_low_reviewer'] = $number;
                break;
            case '2':
                $post['in_low_reviewer'] = $number;
                break;
            case '3':
                $post['high_reviewer'] = $number;
                if ($post['review_status'] === 2) {
                    $post['review_steps'] = $post['review_steps'] + 1;
                    $person_update_data = [
                        'faculty' => $post['receive_faculty'] < 10 ? '0' . (string)$post['receive_faculty'] : (string)$post['receive_faculty'],
                        'party_branch' => $post['receive_organization'],
                        'major' => $post['receive_major'],
                        'number' => $post['number']
                    ];
                    $update_person_res = $person_model->updatePerson($person_update_data);
                    $update_transfer_res = $transfer_model->updateTransfer($post);
                    if ($update_transfer_res === true && $update_person_res === true) {
                        return $this->create($person_update_data, '组织关系转接成功,已更改发起人组织信息');
                    } else {
                        return $this->create([$update_transfer_res, $update_person_res], '失败');
                    }
                }
                break;
            default:
                # code...
                break;
        }
        if ($post['review_status'] === 2) {
            $post['review_steps'] = $post['review_steps'] + 1;
            $post['review_status'] = 1;
        }

        // return [$post,$transfer_model->updateTransfer($post)];
        $update_transfer_res = $transfer_model->updateTransfer($post);

        if ($update_transfer_res) {
            return $this->create('', '审核成功');
        } else {
            return $this->create($update_transfer_res, '审核失败', 204);
        }
    }

    //删除发展党员信息
    public function deleteTransfer()
    {
        $post = request()->param();
        $transfer_model = new TransferModel();

        $res = $transfer_model->deleteTransfer($post);
        if ($res === true) {
            return $this->create('', '删除成功');
        }
        return $this->create($res, '删除失败', 204);
    }



    /**************通告 */
    public function sendBulletin(Request $request)
    {
        $post = request()->param();
        $tooken_res = $request->data;
        $number = $tooken_res['data']->uuid;
        $post['create_number'] = $number;
        $bulletin_model = new BullteinModel();
        $res = $bulletin_model->createBulletin($post);
        if ($res === true) {
            return $this->create('', '发送成功');
        } else {
            return $this->create($res, '发送失败', 204);
        }
    }

    /*********** */



    /**************公共文件 */
    public function uploadPublicFile(Request $request)
    {
        $post = request()->param();
        // return $this->create('', $post, 204);
        $file = request()->file('file');
        // $branch_value = request()->header('partyBranch');
        $tooken_res = $request->data;
        $number = $tooken_res['data']->uuid;
        $df_model = new DownloadFileModel();
        // $material_model = new MaterialModel();
        // $ja_model = new JoinApplyModel();
        // $rpm_model = new RecruitPartyMemberModel();
        // return json(['code' => 200, $post, $header_data, $file->getOriginalName()]);

        // 知识点:获取上传文件信息上传文件名:getOriginalName(); 获取上传文件类型信息:getOriginalMime(); 获取上传文件扩展名:getOriginalExtension()
        try {
            validate(['file' => ['filesize:51200000', 'fileExt:xls,xlsx,doc,docx,zip,rar,7z']])
                ->check(['file' => $file]);
            $save_path = \think\facade\Filesystem::disk('public')->putFileAs('index_file/' . $post['file_category'], $file, $file->getOriginalName());
            $file_data = [
                'uploader_number' => $number,
                'file_category' => $post['file_category'],
                'file_name' => $file->getOriginalName(),
                'file_path' => $save_path,
                'file_remarks' => $post['file_remarks']
            ];
            $res = $df_model->createFileInfo($file_data);
            if ($res === true) {
                return $this->create('', '上传成功');
            } else {
                return $this->create($res, '上传失败', 204);
            }
        } catch (\think\exception\ValidateException $e) {
            return $this->create('', $e->getMessage(), 204);
        }
    }

    //管理员获取公共文件
    public function viewAllFile(Request $request)
    {
        $post = request()->param();

        $tooken_res = $request->data;
        $number = $tooken_res['data']->uuid;
        $df_model = new DownloadFileModel();

        $list_rows = !empty($post['list_rows']) ? $post['list_rows'] : '';

        $res = $df_model->selectFileList($list_rows, ['query' => $post], $post);
        if ($res[0] === true) {
            return $this->create($res[1], '成功');
        }
        return $this->create($res[1], '失败', 204);
    }
    //删除公共文件信息及源文件
    public function deleteFile(Request $request)
    {
        $post = request()->param();
        $tooken_res = $request->data;
        $number = $tooken_res['data']->uuid;
        $df_model = new DownloadFileModel();
        $res = $df_model->deleteFileById($post['id']);
        if ($res === true) {
            return $this->create('', '成功');
        }
        return $this->create($res, '找不到文件,可能已被删除', 204);
    }

    /*********** */














    /*******
     * 联表查询信息修改 
     * */

    //考核成绩
    public function meterialJoinSelectManage(Request $request)
    {
        $post = request()->param();
        $tooken_res = $request->data;
        $number = $tooken_res['data']->uuid;
        $role = $tooken_res['data']->role;
        $person_model = new PersonModel();
        $material_model = new MaterialModel();

        if ($role <= 3) {
            $faculty = '';
        } else {
            $faculty = $person_model->getInfoByNumber($number, 'faculty');
        }

        $list_rows = !empty($post['list_rows']) ? $post['list_rows'] : '';

        $res = $material_model->getAllList($list_rows, ['query' => $post], $faculty, $post);
        if ($res[0] === true) {
            return $this->create($res[1], '成功');
        }
        return $this->create($res[1], '失败', 204);
    }
    //添加考核成绩
    public function addMeterialJoinSelectManage(Request $request)
    {
        $post = request()->param();
        $tooken_res = $request->data;
        $number = $tooken_res['data']->uuid;
        $role = $tooken_res['data']->role;
        $person_model = new PersonModel();
        $material_model = new MaterialModel();

        $person_info = $person_model->getAllInfoByNumber($post['number']);
        // return json($person_info);
        if ($person_info === null) {
            return $this->create('', '找不到该学工信息,请检查学工号是否正确', 204);
        }
        if ($role === 4) {
            $admin_faculty = $person_model->getInfoByNumber($number, 'faculty');
            // $person_faculty = $person_model->getInfoByNumber($post['number'], 'faculty');
            if ($admin_faculty != $person_info['faculty']) {
                return $this->create('', '请添加本学院信息', 204);
            }
        }
        $post['remarks'] = !empty($post['remarks']) ? $post['remarks'] : '';
        $post['serial_number'] = !empty($post['serial_number']) ? $post['serial_number'] : '';
        $res = $material_model->addMaterial($post['number'], $post['category'], $post['serial_number'], $post['score'], $post['remarks']);
        if ($res === true) {
            return $this->create('', '成功');
        }
        return $this->create('', $res, 204);
    }

    //修改考核成绩
    public function updateMeterialJoinSelectManage()
    {
        $post = request()->param();
        $material_model = new MaterialModel();
        $res = $material_model->updateMaterial($post);
        if ($res === true) {
            return $this->create('', '成功');
        }
        return $this->create('', $res, 204);
    }
    //删除考核成绩
    public function deleteMeterialJoinSelectManage()
    {
        $post = request()->param();
        $material_model = new MaterialModel();
        $res = $material_model->deleteMaterial($post['id']);
        if ($res === true) {
            return $this->create('', '成功');
        }
        return $this->create('', $res, 204);
    }

    /********** */



    /*******发展党员信息*/
    public function recruitlJoinSelectManage(Request $request)
    {
        $post = request()->param();
        $tooken_res = $request->data;
        $number = $tooken_res['data']->uuid;
        $role = $tooken_res['data']->role;
        $person_model = new PersonModel();
        $rpm_model = new RecruitPartyMemberModel();

        if ($role <= 3) {
            $faculty = '';
        } else {
            $faculty = $person_model->getInfoByNumber($number, 'faculty');
        }

        $list_rows = !empty($post['list_rows']) ? $post['list_rows'] : '';

        $res = $rpm_model->getAllList($list_rows, ['query' => $post], $faculty, $post);
        if ($res[0] === true) {
            return $this->create($res[1], '成功');
        }
        return $this->create($res[1], '失败', 204);
    }
    //添加考核成绩
    public function addRecruitJoinSelectManage(Request $request)
    {
        $post = request()->param();
        $tooken_res = $request->data;
        $number = $tooken_res['data']->uuid;
        $role = $tooken_res['data']->role;
        $person_model = new PersonModel();
        $rpm_model = new RecruitPartyMemberModel();


        $person_info = $person_model->getAllInfoByNumber($post['number']);
        // return json($person_info);
        if ($person_info === null) {
            return $this->create('', '找不到该学工信息,请检查学工号是否正确', 204);
        }
        if ($role === 4) {
            $admin_faculty = $person_model->getInfoByNumber($number, 'faculty');
            // $person_faculty = $person_model->getInfoByNumber($post['number'], 'faculty');
            if ($admin_faculty != $person_info['faculty']) {
                return $this->create('', '请添加本学院信息', 204);
            }
        }
        // $post['stage_time']=date("Y-m-d H:i:s",$post['stage_time']/1000);
        
        $post['remarks'] = !empty($post['remarks']) ? $post['remarks'] : '';
        $post['contacts'] = !empty($post['contacts']) ? $post['contacts'] : '';
        $post['introducer'] = !empty($post['introducer']) ? $post['introducer'] : '';
        $res = $rpm_model->createRecruit($post);
        if ($res === true) {
            return $this->create('', '成功');
        }
        return $this->create('', $res, 204);
    }

    //修改考核成绩
    public function updateRecruitJoinSelectManage()
    {
        $post = request()->param();
        $rpm_model = new RecruitPartyMemberModel();

        // $post['stage_time']=date("Y-m-d H:i:s",$post['stage_time']/1000);

        $res = $rpm_model->updateRecruit($post);
        if ($res === true) {
            return $this->create('', '成功');
        }
        return $this->create('', $res, 204);
    }
    //删除考核成绩
    public function deleteRecruitJoinSelectManage()
    {
        $post = request()->param();
        $rpm_model = new RecruitPartyMemberModel();

        $res = $rpm_model->deleteRecruitById($post['id']);
        if ($res === true) {
            return $this->create('', '成功');
        }
        return $this->create('', $res, 204);
    }

    /****** */

    /**
     * 
     * over
     */



























    /***********首页charts数据*/
    public function getCount(Request $request)
    {
        $tooken_res = $request->data;
        $number = $tooken_res['data']->uuid;
        $role = $tooken_res['data']->role;

        $person_model = new PersonModel();
        $ja_model = new JoinApplyModel();
        $faculty = $role <= 3 ? '' : $person_model->getInfoByNumber($number, 'faculty');
        $apply_person_count = $ja_model->countApplyPerson($faculty);
        $reviewing_count = $ja_model->countReview(1, $faculty);
        $reviewed_count = $ja_model->countReview(2, $faculty);
        $no_reviewed_count = $ja_model->countReview(3, $faculty);

        $list = [
            'applyPersonCount' => $apply_person_count,
            'reviewingCount' => $reviewing_count,
            'reviewedCount' => $reviewed_count,
            'noReviewedCount' => $no_reviewed_count,
            // 'headerCounts'=>[$apply_person_count,$reviewing_count,$reviewed_count,$no_reviewed_count]
        ];
        return $this->create($list, '查询成功');
    }

    //折线图
    public function getLineCharts(Request $request)
    {
        $tooken_res = $request->data;
        $number = $tooken_res['data']->uuid;
        $role = $tooken_res['data']->role;

        $person_model = new PersonModel();
        $ja_model = new JoinApplyModel();
        $faculty = $role <= 3 ? '' : $person_model->getInfoByNumber($number, 'faculty');
        $list = $ja_model->countLineCharts($faculty);
        return $this->create(['columns' => ['年份', '新增申请人数'], 'rows' => $list = array_reverse($list)], '查询成功');
    }

    //性别统计
    public function getCountPersonSex(Request $request)
    {
        $tooken_res = $request->data;
        $number = $tooken_res['data']->uuid;
        $role = $tooken_res['data']->role;

        $person_model = new PersonModel();
        $ja_model = new JoinApplyModel();
        $faculty = $role <= 3 ? '' : $person_model->getInfoByNumber($number, 'faculty');

        $result = $ja_model->countPersonSex($faculty);
        return $this->create(['columns' => ['性别', '人数'], 'rows' => $result], '查询成功');
    }

    public function getCountPersonNation(Request $request)
    {
        $tooken_res = $request->data;
        $number = $tooken_res['data']->uuid;
        $role = $tooken_res['data']->role;

        $person_model = new PersonModel();
        $ja_model = new JoinApplyModel();
        $faculty = $role <= 3 ? '' : $person_model->getInfoByNumber($number, 'faculty');

        $result = $ja_model->countPersonNation();
        return $this->create(['columns' => ['民族', '人数'], 'rows' => $result], '查询成功');
    }









    /***********    发展党员大数据   */

    //民族统计
    public function getCountRecruitNation(Request $request)
    {
        $tooken_res = $request->data;
        $number = $tooken_res['data']->uuid;
        $role = $tooken_res['data']->role;

        $person_model = new PersonModel();
        $rpm_model = new RecruitPartyMemberModel();
        $faculty = $role <= 3 ? '' : $person_model->getInfoByNumber($number, 'faculty');

        $result = $rpm_model->countRecruitNation($faculty);
        return $this->create(['columns' => ['民族', '人数'], 'rows' => $result], '查询成功');
    }
    // 性别统计
    public function getCountRecruitSex(Request $request)
    {
        $tooken_res = $request->data;
        $number = $tooken_res['data']->uuid;
        $role = $tooken_res['data']->role;

        $person_model = new PersonModel();
        $rpm_model = new RecruitPartyMemberModel();

        $faculty = $role <= 3 ? '' : $person_model->getInfoByNumber($number, 'faculty');

        $result = $rpm_model->countRecruitSex($faculty);
        return $this->create(['columns' => ['性别', '人数'], 'rows' => $result], '查询成功');
    }

    //学院统计
    public function getCountRecruitFaculty(Request $request)
    {
        $tooken_res = $request->data;
        $number = $tooken_res['data']->uuid;
        $role = $tooken_res['data']->role;

        $person_model = new PersonModel();
        $rpm_model = new RecruitPartyMemberModel();
        $faculty = $role <= 3 ? '' : $person_model->getInfoByNumber($number, 'faculty');

        $result = $rpm_model->countRecruitFaculty($faculty);
        return $this->create(['columns' => ['学院', '人数'], 'rows' => $result], '查询成功');
    }
    //职务统计
    public function getCountRecruitPost(Request $request)
    {
        $tooken_res = $request->data;
        $number = $tooken_res['data']->uuid;
        $role = $tooken_res['data']->role;

        $person_model = new PersonModel();
        $rpm_model = new RecruitPartyMemberModel();
        $faculty = $role <= 3 ? '' : $person_model->getInfoByNumber($number, 'faculty');

        $result = $rpm_model->countRecruitPost($faculty);
        return $this->create(['columns' => ['职务', '人数'], 'rows' => $result], '查询成功');
    }

    //发展阶段统计
    public function getCountRecruitStage(Request $request)
    {
        $tooken_res = $request->data;
        $number = $tooken_res['data']->uuid;
        $role = $tooken_res['data']->role;

        $person_model = new PersonModel();
        $rpm_model = new RecruitPartyMemberModel();
        $faculty = $role <= 3 ? '' : $person_model->getInfoByNumber($number, 'faculty');

        $result = $rpm_model->countRecruitStage($faculty);
        return $this->create(['columns' => ['发展阶段', '人数'], 'rows' => $result], '查询成功');
    }

    //发展阶段统计
    public function getCountRecruitPoliticalStatus(Request $request)
    {
        $tooken_res = $request->data;
        $number = $tooken_res['data']->uuid;
        $role = $tooken_res['data']->role;

        $person_model = new PersonModel();
        $rpm_model = new RecruitPartyMemberModel();
        $faculty = $role <= 3 ? '' : $person_model->getInfoByNumber($number, 'faculty');

        $result = $rpm_model->countRecruitPoliticalStatus($faculty);
        return $this->create(['columns' => ['政治面貌', '人数'], 'rows' => $result], '查询成功');
    }

    //发展阶段统计
    public function getCountRecruitGrowthStage(Request $request)
    {
        $tooken_res = $request->data;
        $number = $tooken_res['data']->uuid;
        $role = $tooken_res['data']->role;

        $person_model = new PersonModel();
        $rpm_model = new RecruitPartyMemberModel();
        $faculty = $role <= 3 ? '' : $person_model->getInfoByNumber($number, 'faculty');

        $result = $rpm_model->countRecruitGrowthStage($faculty);
        return $this->create(['columns' => ['成长阶段', '人数'], 'rows' => $result], '查询成功');
    }








































    //结束
}
