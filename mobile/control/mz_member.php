<?php
/**
 * 美站个人中心
 *
 *
 *
 * @copyright  Copyright (c) 2007-2013 ShopNC Inc. (http://www.shopnc.net)
 * @license    http://www.shopnc.net
 * @link       http://www.shopnc.net
 * @since      File available since Release v1.1
 */

use Shopnc\Tpl;

defined('InShopNC') or exit('Access Invalid!');
class mz_memberControl extends mobileMemberControl {

    public function __construct() {
        parent::__construct();
    }

    public function indexOp() {
        exit;
    }

    /**
     * 获取用户信息
     */
    public function getUserInfoOp() {
        $member_info = array();

        $team_member_info = Model('mz_member')->getMemberInfo(array('member_id'=>$this->member_info['member_id']));
        // $member_info = $this->member_info;
        $member_info['member_avatar'] = getMemberAvatar($this->member_info['member_avatar']);
        $member_info['team_id'] = $team_member_info['team_id'];
        if ($team_member_info['team_id'] > 0) {
            $month_income = Model("mz_statistical")->where(array('stat_time'=>date("Y-m"),'stat_team_id'=>$team_member_info['team_id']))->get_field("stat_amount");
            $member_info['month_income'] = empty($month_income)?0:$month_income;
        }
        $member_info['member_points'] = $team_member_info['integral'];
        $member_info['member_grade_info'] = $this->getMemberGrade($team_member_info['integral']);

        $member_info['member_name'] = $this->member_info['member_name'];

        $member_info['available_rc_balance'] = $this->member_info['available_rc_balance'];
        $member_info['available_predeposit'] = $this->member_info['available_predeposit'];
        $member_info['member_sex'] = $this->member_info['member_sex'];
        $member_info['member_birthday'] = $this->member_info['member_birthday'];
        
        $member_info['member_email_bind'] = $this->member_info['member_email_bind'];
        $member_info['member_email'] = encryptShow($this->member_info['member_email'],4,4);
        $member_info['member_mobile_bind'] = $this->member_info['member_mobile_bind'];
        $member_info['member_mobile'] = encryptShow($this->member_info['member_mobile'],4,4);

        $member_info['member_idcard_bind'] = (empty($this->member_info['member_truename']) || empty($this->member_info['member_idnum']))?false:true;
        $member_info['member_truename'] = encryptShow($this->member_info['member_truename'],2,2);
        $member_info['member_idnum'] = encryptShow($this->member_info['member_idnum'],7,8);
        output_data(array('data'=>$member_info));
    }
    /**
     * 通过积分获取等级信息
     * @param  [type] $integral [description]
     * @return [type]           [description]
     */
    private function getMemberGrade($integral){
        $membergrade = "";
        $mz_member_grade_arr = unserialize(C('mz_member_grade'));
        if (!empty($mz_member_grade_arr)) {
            foreach ($mz_member_grade_arr as $key => $value) {
                if ($integral <= $value['integral']) {
                    $membergrade = $value;
                    break;
                }
            }
        }
        return $membergrade;
    }
    /**
     * 修改用户信息
     * @return [type] [description]
     */
    public function editUserOp(){
        $editFields = array('member_email','member_avatar','member_sex','member_birthday','group_id');
        $data = array();
        if (!empty($_POST)) {
            $condition = array();
            $condition['member_id'] = $this->member_info['member_id'];
            if (isset($_POST['member_passwd'])) {
                if ($this->member_info['member_passwd'] == md5($_POST['member_passwd'])) {
                    if (!empty($_POST['member_repasswd'])) {
                        $_POST['member_passwd'] = md5($_POST['member_repasswd']);
                        unset($_POST['member_repasswd']);
                    }else{
                        output_error("新密码不能为空");
                    }
                }else{
                    output_error('原密码错误');
                }
            }
            foreach ($_POST as $key => $value) {
                if (in_array($key, $editFields)) {
                    $data[$key] = $value;
                }
            }
            if (!empty($data)) {
                if (Model("member")->editMember($condition,$data)){
                    output_data("修改成功");
                }else{
                    output_error("系统错误");
                }
            }else{
                output_error("非法操作");
            }
        }else{
            output_error("非法操作");
        }
    }
    /**
     * 上传头像
     * @return [type] [description]
     */
    public function uploadHeadIconOp(){
        //上传图片
        $upload = new UploadFile();
        $upload->set('thumb_width', 500);
        $upload->set('thumb_height',499);
        $ext = strtolower(pathinfo($_FILES['headiconedit']['name'], PATHINFO_EXTENSION));
        $upload->set('file_name',"avatar_".$this->member_info['member_id'].".$ext");
        $upload->set('thumb_ext','_new');
        $upload->set('ifremove',true);
        $upload->set('default_dir',ATTACH_AVATAR);
        if (!empty($_FILES['headiconedit']['tmp_name'])){
            $result = $upload->upfile('headiconedit');
            if (!$result){
                output_error($upload->error);
            }
        }else{
            output_error("上传失败，请尝试更换图片格式或小图片");
        }

        $condition = $data =  array();
        $condition['member_id'] = $this->member_info['member_id'];
        $data["member_avatar"] = $upload->thumb_image;

        if (Model("member")->editMember($condition,$data)){
            output_data("修改成功");
        }else{
            output_error("系统错误");
        }
    }
}