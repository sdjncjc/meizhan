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
class mz_teamControl extends mobileMemberControl {

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
    	$userteam = Model('mz_member')->getMemberInfo(array('member_id'=>$this->member_info['member_id']),"*",array("mz_team"));
    	if (empty($userteam)) {
            $userteam['member_id'] = $this->member_info['member_id'];
            $userteam['team_id'] = 0;
            $userteam['type'] = 0;
    	}
        $team_apply = Model("mz_team_log")->where(array('member_id'=>$this->member_info['member_id'],'type'=>1,'team_id'=>$userteam['team_id']))->field("id,status")->order('addtime desc')->find();
        if (!empty($team_apply)) {
            $userteam['team_apply_id'] = $team_apply['id'];
            $userteam['team_apply_status'] = $team_apply['status'];
        }else{
            if ($userteam['type'] == 1) {
                $userteam['apply_member_num'] = Model('mz_team_log')->where(array('type'=>0,'team_id'=>$userteam['team_id']))->count();
            }
        }
    	output_data(array('data'=>$userteam));
    }
    /**
     * 获取申请列表
     * @return [type] [description]
     */
    public function getApplyListOp(){
        $data = Model("mz_team_log")->where(array('member_id'=>$this->member_info['member_id']))->order("addtime desc")->select();
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
        $team_id = intval($_POST['team_id']);
        $team_user_info = Model('mz_member')->getMemberInfo(array('member_id'=>$this->member_info['member_id']));
        // 判断小组用户信息是否存在
        if (!empty($team_user_info)) {
            if ($team_id > 0) {
                if ($team_user_info['team_id'] != $team_id) {
                    output_error("无权限修改");
                    exit();
                }
            }else{
                if ($team_user_info['team_id'] > 0 ) {
                    output_error("已加入小组，无法创建新小组");
                    exit();
                }
                Model("mz_member")->editTeamUser(array('member_id'=>$this->member_info['member_id']),array('type'=>1));
            }
        }else{
            if ($team_id > 0) {
                output_error("参数错误");
                exit();
            }
            // 插入小组用户信息
            $data_user = array();
            $data_user['member_id'] = $this->member_info['member_id'];
            $data_user['team_id'] = 0;
            $data_user['integral'] = 0;
            $data_user['type'] = 1;
            Model("mz_member")->addTeamUser($data_user);
        }

        $condition = array();
        $condition['team_domain_name'] = trim($_POST['team_domain_name']);
        if ($team_id >0 ) {
            $condition['team_id'] = array('neq',$team_id);
        }
        $count = Model('mz_team')->where($condition)->count();
        if ($count > 0) {
            output_error("子域名重复");
        }
        $data = array();
        $data['team_type'] = intval($_POST['team_type']);
        $data['team_name'] = trim($_POST['team_name']);
        $data['provinceid'] = intval($_POST['provinceid']);
        $data['province'] = trim($_POST['province']);
        $data['city_school_id'] = trim($_POST['city_school_id']);
        $data['city_school'] = trim($_POST['city_school']);
        $data['team_domain_name'] = trim($_POST['team_domain_name']);
        $data['recommend_id'] = intval($_POST['recommend_id']);
        $data['team_intro'] = trim($_POST['team_intro']);
        $data['team_status'] = 0;
        $data['createtime'] = TIMESTAMP;
        // 判断推广人
        if (!in_array($data['recommend_id'],array(123,164))) {
            $data['recommend_id'] = 0;
        }
        if ($team_id > 0) {
            Model("mz_team")->where(array('team_id'=>$team_id))->update($data);
        }else{
            $team_id = Model('mz_team')->insert($data);
        }
        if ($team_id) {
            $team_apply_info = Model("mz_team_log")->where(array('member_id'=>$this->member_info['member_id'],"type"=>1,'team_id'=>$team_id))->find();
            if (!empty($team_apply_info)) {
                $result = Model("mz_team_log")->where(array('id'=>$team_apply_info['id']))->update(array('status'=>0));
            }else{
                // 将创建的小组id写入小组用户
                Model("mz_member")->editTeamUser(array('member_id'=>$this->member_info['member_id']),array('team_id'=>$team_id));
                $apply_data = array();
                $apply_data['member_id'] = $this->member_info['member_id'];
                $apply_data['type'] = 1;
                $apply_data['team_name'] = trim($_POST['team_name']);
                $apply_data['team_id'] = $team_id;
                $apply_data['status'] = 0;
                $apply_data['addtime'] = TIMESTAMP;
                $result = Model("mz_team_log")->insert($apply_data);
            }
            if ($result) {
                // 删除小组加入申请
                Model("mz_team_log")->where(array('member_id'=>$this->member_info['member_id'],'type'=>0))->delete();
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
        $condition['city_school_id'] = intval($_GET['city_school_id']);
        $condition['team_status'] = 1;
        $condition['team_name'] = array('like','%'.trim($_GET['keywords']).'%');
        // 查询数量统计
        $size = 10;     
        $page = intval($_GET['page']);
        $page = $page <= 0 ? 1 : $page;
        $count = Model("mz_team")->where($condition)->count();
        $data_info['thispage'] = $page;
        $data_info['totalpage'] = ceil($count / $size);
        // 查询当前已申请的小组id
        $apply_teams = Model("mz_team_log")->where(array('member_id'=>$this->member_info['member_id'],'type'=>0,'status'=>0))->field('team_id')->select();
        $apply_team_ids = array();
        if (!empty($apply_teams)) {
            foreach ($apply_teams as $key => $value) {
                $apply_team_ids[] = $value['team_id'];
            }
        }
        // 分页设置
        $teams = Model("mz_team")->where($condition)->limit((($page-1)*$size).','.$size)->select();
        if (!empty($teams)) {
            foreach ($teams as $key => $value) {
                if (in_array($value['team_id'], $apply_team_ids)) {
                    $teams[$key]['has_apply'] = 1;
                }else{
                    $teams[$key]['has_apply'] = 1;
                }
            }
        }

        output_data(array('data'=>$teams,'data_info'=>$data_info));
    }
    /**
     * 加入小组
     * @return [type] [description]
     */
    public function joinTeamOp(){
        $result = false;
        $team_id = intval($_GET['team_id']);
        $team_info = Model("mz_team")->where(array('team_id'=>$team_id))->find();
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
        // 判断小组用户信息是否存在，否则添加用户
        $model_mz_member = Model("mz_member");
        $team_user_info = $model_mz_member->getMemberInfo(array('member_id'=>$this->member_info['member_id']));
        if (!empty($team_user_info)) {
            if ($team_user_info['team_id'] > 0) {
                output_error("当前为小组成员，无法提交申请");
            }
        }else{
            $data_user = array();
            $data_user['member_id'] = $this->member_info['member_id'];
            $data_user['type'] = 0;
            $model_mz_member->addTeamUser($data_user);
        }
        // 统计提交的申请记录
        $condition = array();
        $condition['member_id'] = $this->member_info['member_id'];
        $join_num = Model('mz_team_log')->where($condition)->count();
        if ($join_num > 10) {
            output_error("申请小组数量已超出，无法提交申请");
        }
        // 判断是否已申请该小组
        $condition['team_id'] = $team_info['team_id'];
        $has_join = Model('mz_team_log')->where($condition)->count();
        if ($has_join > 0) {
            $result = Model('mz_team_log')->where($condition)->update(array('status'=>0,'addtime'=>TIMESTAMP));
        }else{
            $data = array();
            $data['member_id'] = $this->member_info['member_id'];
            $data['team_id'] =  $team_info['team_id'];
            $data['team_name'] =  $team_info['team_name'];
            $data['type'] =  0;
            $data['status'] = 0;
            $data['addtime'] = TIMESTAMP;
            $result = Model('mz_team_log')->insert($data);
        }
        if ($result) {
            output_data("申请提交成功");
        }else{
            output_error("系统错误");
        }
    }
    /**
     * 删除申请
     * @return [type] [description]
     */
    public function deleteApplyOp(){
        $id = intval($_GET['id']);
        $apply_info = Model("mz_team_log")->where(array('member_id'=>$this->member_info['member_id'],'id'=>$id))->find();
        if (empty($apply_info)) {
            output_error("参数错误");
        }
        // 删除申请记录
        $result = Model("mz_team_log")->where(array('member_id'=>$this->member_info['member_id'],'id'=>$id))->delete();
        if ($result) {
            if ($apply_info['type'] == 1) {
                Model("mz_team")->where(array('team_id'=>$apply_info['team_id']))->delete();
                Model("mz_member")->editTeamUser(array('member_id'=>$this->member_info['member_id']),array('team_id'=>0));
            }
            output_data("删除成功");
        }else{
            output_error("删除失败");
        }
    }
    /**
     * 获取小组信息
     * @return [type] [description]
     */
    public function getTeamInfoOp(){
        $team_id = intval($_GET['team_id']);
        $team_info = Model("mz_team")->where(array('team_id'=>$team_id,'team_status'=>1))->find();
        $team_member = Model('mz_member')->getMemberInfo(array('member_id'=>$this->member_info['member_id']));
        if (!empty($team_info) && !empty($team_member)) {
            if ($team_member['team_id'] == $team_info['team_id']) {
                $team_info['is_join'] = 1;
                $team_info['is_leader'] = $team_member['type'];
            }else{
                // 查询当前已申请的小组id
                $apply_teams = Model("mz_team_log")->where(array('member_id'=>$this->member_info['member_id'],'type'=>0,'status'=>0))->field('team_id')->select();
                $apply_team_ids = array();
                if (!empty($apply_teams)) {
                    foreach ($apply_teams as $key => $value) {
                        $apply_team_ids[] = $value['team_id'];
                    }
                }
                if (in_array($team_id, $apply_team_ids)) {
                    $team_info['has_apply'] = 1;
                }else{
                    $team_info['has_apply'] = 0;
                }
                $team_info['is_join'] = 0;
                $team_info['is_leader'] = 0;
            }
        }
        output_data(array('data'=>$team_info));
    }
    /**
     * 更新小组信息
     * @return [type] [description]
     */
    public function editTeamInfoOp(){
        $team_name = trim($_POST['team_name']);
        $team_intro = trim($_POST['team_intro']);
        $team_member = Model('mz_member')->getMemberInfo(array('member_id'=>$this->member_info['member_id']));
        if ($team_member['type'] == 0) {
            output_error("无权限");
        }
        $data = array();
        $data['team_name'] = $team_name;
        $data['team_intro'] = $team_intro;
        $result = Model("mz_team")->where(array('team_id'=>$team_member['team_id'],'team_status'=>1))->update($data);
        if ($result) {
            output_data("更新成功");
        }else{
            output_error("更新失败");
        }
    }
    /**
     * 获取小组金额日志
     * @return [type] [description]
     */
    public function getTeamBalanceLogOp(){
        $type = trim($_GET['type']);
        $team_member_info = Model('mz_member')->getMemberInfo(array('member_id'=>$this->member_info['member_id']));
        $condition = array();
        $condition['balance_teamid'] = $team_member_info['team_id'];
        if (empty($type) || $type=='income') {
            $condition['balance_stage'] = "order";
        }else{
            $condition['balance_stage'] = "allot";
        }

        // 查询数量统计
        $size = 10;     
        $page = intval($_GET['page']);
        $page = $page <= 0 ? 1 : $page;
        $count = Model("mz_team")->where($condition)->count();
        $data_info['thispage'] = $page;
        $data_info['totalpage'] = ceil($count / $size);

        $log_list = Model("mz_balance_log")->where($condition)->limit((($page-1)*$size).','.$size)->order("balance_addtime desc")->select();
        if (!empty($log_list)) {
            foreach ($log_list as $key => $value) {
                $log_list[$key]['balance_addtime'] = date("Y-m-d",$value['balance_addtime']);
            }
        }
        output_data(array('data'=>$log_list,'data_info'=>$data_info));
    }
}