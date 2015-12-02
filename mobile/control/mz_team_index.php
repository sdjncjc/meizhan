<?php
/**
 * 美站小组
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
class mz_team_indexControl extends mobileMemberControl {

    public function __construct() {
        parent::__construct();
    }

    public function indexOp() {
        exit;
    }
    /**
     * 获取小组用户信息
     * @return [type] [description]
     */
    public function getUserTeamOp(){
    	$condition = array();
    	$condition['member_id'] = $this->member_info['member_id'];
    	$model_mz_member =  Model('mz_member');
    	$userteam = $model_mz_member->getMemberInfo($condition,"*",array("mz_team"));
    	if (empty($userteam)) {
            $userteam['member_id'] = $this->member_info['member_id'];
            $userteam['team_id'] = 0;
            $userteam['type'] = 0;
    	}
    	output_data(array('data'=>$userteam));
    }
    /**
     * 获取申请列表
     * @return [type] [description]
     */
    public function getApplyListOp(){
        $data = Model()->table("mz_team_log")->where(array('member_id'=>$this->member_info['member_id']))->order("addtime desc")->select();
        if (!empty($data)) {
            foreach ($data as $key => $value) {
                $data[$key]['addtime'] = date("Y-m-d",$value['addtime']);
            }
        }
        output_data(array('data'=>$data));
    }
    /**
     * 申请创建小组
     * @return [type] [description]
     */
    public function createTeamOp(){
        $team_user_info = Model('mz_member')->getMemberInfo(array('member_id'=>$this->member_info['member_id']));
        // 判断小组用户信息是否存在
        if (!empty($team_user_info)) {
            if ($team_user_info['team_id'] > 0 ) {
                output_error("已加入小组，无法创建新小组");
                exit();
            }
        }else{
            // 插入小组用户信息
            $data_user = array();
            $data_user['member_id'] = $this->member_info['member_id'];
            $data_user['team_id'] = 0;
            $data_user['integral'] = 0;
            $data_user['type'] = 0;
            Model("mz_member")->addTeamUser($data_user);
        }
        $data = array();
        $data['team_type'] = intval($_POST['team_type']);
        $data['team_name'] = trim($_POST['team_name']);
        $data['provinceid'] = intval($_POST['provinceid']);
        $data['province'] = trim($_POST['province']);
        $data['city_school'] = trim($_POST['city_school']);
        $data['team_domain_name'] = trim($_POST['team_domain_name']);
        $data['team_intro'] = trim($_POST['team_intro']);
        $data['team_status'] = 0;
        $data['createtime'] = TIMESTAMP;
        $count = Model('mz_team')->where(array('team_domain_name'=>$data['team_domain_name']))->count();
        if ($count > 0) {
            output_error("子域名重复");
        }
        $team_id = Model('mz_team')->addTeam($data);
        if ($team_id) {
            // 将创建的小组id写入小组用户
            Model("mz_member")->editTeamUser(array('member_id'=>$this->member_info['member_id']),array('team_id'=>$team_id));
            $apply_data = array();
            $apply_data['member_id'] = $this->member_info['member_id'];
            $apply_data['type'] = 1;
            $apply_data['team_name'] = trim($_POST['team_name']);
            $apply_data['team_id'] = $team_id;
            $apply_data['status'] = 0;
            $apply_data['addtime'] = TIMESTAMP;
            $result = Model()->table("mz_team_log")->insert($apply_data);
            if ($result) {
                // 删除小组加入申请
                Model()->table("mz_team_log")->where(array('member_id'=>$this->member_info['member_id'],'type'=>0))->delete();
                output_data("创建成功，请等待管理员审核");
            }else{
                output_error("系统错误");   
            }
        }else{
            output_error("系统错误");   
        }
    }
    /**
     * 获取学校列表
     * @return [type] [description]
     */
    public function getSchoollistOp(){
        $province_id = intval($_GET['province_id']);
        $model_school = Model('school');
        $school_list = $model_school->getSchoolList(array('province_id'=>$province_id));
        output_data(array('data'=>$school_list));
    }
    /**
     * 搜索小组
     * @return [type] [description]
     */
    public function searchTeamListOp(){
        $condition = array();
        $condition['team_type'] = intval($_GET['team_type']);
        $condition['provinceid'] = intval($_GET['provinceid']);
        $condition['city_school'] = trim($_GET['city_school']);
        $condition['team_status'] = 1;
        $condition['team_name'] = array('like','%'.trim($_GET['keywords']).'%');

        $teams = Model("mz_team")->where($condition)->select();
        output_data(array('data'=>$teams));
    }
    /**
     * 加入小组
     * @return [type] [description]
     */
    public function joinTeamOp(){
        $result = false;
        $team_id = intval($_GET['team_id']);
        $team_info = Model("mz_team")->getTeamInfo(array('team_id'=>$team_id));
        if (!empty($team_info)) {
            if ($team_info['team_status'] != 1) {
                output_error("小组状态不正确，无法加入");
            }
            if ($team_info['num'] >= $team_info['max_num']) {
                output_error("小组人数超过限制，无法加入");
            }
        }else{
            output_error("小组不存在！");
        }
        $data = array();
        $data['team_id'] = $team_id;
        $data['type'] = 0;
        $model_mz_member = Model("mz_member");
        $team_user_info = $model_mz_member->getMemberInfo(array('member_id'=>$this->member_info['member_id']));
        if (!empty($team_user_info)) {
            if ($team_user_info['team_id'] > 0) {
                if ($team_user_info['type'] > 0) {
                    output_error("当前为小组成员，申请失败");
                }else{
                    output_error("已提交申请，申请失败");
                }
            }
            $result = $model_mz_member->editTeamUser(array('member_id'=>$this->member_info['member_id']),$data);
        }else{
            $data['member_id'] = $this->member_info['member_id'];
            $result = $model_mz_member->addTeamUser($data);
        }
        if ($result) {
            output_data("申请提交成功");
        }else{
            output_error("系统错误");
        }
    }
}