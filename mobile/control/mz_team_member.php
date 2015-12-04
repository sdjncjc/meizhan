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
class mz_team_memberControl extends mobileMemberControl {

    public function __construct() {
        parent::__construct();
    }

    public function indexOp() {
        exit;
    }
    public function getApplyListOp(){
    	$team_member_info = Model("mz_member")->getMemberInfo(array('member_id'=>$this->member_info['member_id']));
    	if ($team_member_info['type'] != 1) {
    		output_error("无权限查看申请信息");
    	}
    	$condition = array();
    	$condition['team_id'] = $team_member_info['team_id'];
    	$condition['type'] = 0;
    	$condition['status'] = 0;
    	$applyList = Model("mz_team_log")->where($condition)->select();
    	if (!empty($applyList)) {
    		foreach ($applyList as $key => $value) {
    			$applyList[$key]['member_name'] = Model("member")->where(array('member_id'=>$value['member_id']))->get_field("member_name");
                $integral = Model("mz_member")->where(array('member_id'=>$value['member_id']))->get_field("integral");
                $grade_info = $this->getMemberGrade($integral);
    			$applyList[$key]['level'] = $grade_info['level_name'];
    			$applyList[$key]['addtime'] = date("Y-m-d",$value['addtime']);
    		}
    	}
    	output_data(array('data'=>$applyList));
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
    public function auditMemberOp(){
    	$team_log_id = intval($_GET['id']);
    	$agree = intval($_GET['agree']);
    	$team_member_info = Model("mz_member")->getMemberInfo(array('member_id'=>$this->member_info['member_id']));
    	// 判断小组所属
    	if ($team_member_info['type'] != 1) {
    		output_error("无权限");
    	}
    	// 判断小组状态
		$team_info = Model("mz_team")->where(array('team_id'=>$team_member_info['team_id']))->find();
		if (empty($team_info)) {
			output_error("小组信息不存在");
		}
		if ($team_info['team_status'] !=1) {
			output_error("小组状态不正确,无法审核");
		}
		// 判断申请信息
    	$team_log_info = Model('mz_team_log')->where(array('id'=>$team_log_id))->find();
    	if (empty($team_log_info)) {
    		output_error("参数错误");
    	}
    	if ($team_member_info['team_id'] != $team_log_info['team_id']) {
    		output_error("无权限");
    	}
    	if ($agree == 0) {
    		$result = Model("mz_team_log")->where(array('id'=>$team_log_id))->update(array('status'=>1));
    		if($result) {
    			output_data("已拒绝");
    		}else{
    			output_error("系统错误");
    		}
    	}else if ($agree == 1) {
    		if ($team_info['num'] >= $team_info['max_num']) {
    			output_error("成员已达最大人数");
    		}
    		Model("mz_team_log")->where(array('id'=>$team_log_id))->delete();
    		Model("mz_member")->where(array('member_id'=>$team_log_info['member_id']))->update(array('team_id'=>$team_member_info['team_id']));
    		Model("mz_team")->where(array('team_id'=>$team_member_info['team_id']))->update(array('num'=>$team_info['num'] + 1));
    		output_data("已同意添加到本组");
    	}
    	output_error("参数错误");
    }
    public function getMemberListOp(){
        $team_member_info = Model("mz_member")->getMemberInfo(array('member_id'=>$this->member_info['member_id']));
        $member_list = Model("mz_member")->where(array('team_id'=>$team_member_info['team_id']))->select();
        foreach ($member_list as $key => $value) {
            $member_info = Model('member')->where(array('member_id'=>$value['member_id']))->field('member_name,member_avatar')->find();
            $member_list[$key]['member_name'] = $member_info['member_name'];
            $member_list[$key]['member_grade'] = $this->getMemberGrade($value['integral']);
            $member_list[$key]['member_avatar'] = getMemberAvatar($member_info['member_avatar']);

        }
        output_data(array('data'=>$member_list));
    }
}