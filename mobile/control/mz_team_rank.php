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
class mz_team_rankControl extends mobileMemberControl {

    public function __construct() {
        parent::__construct();
    }

    public function indexOp() {
        exit;
    }
    /**
     * 获取地区小组销售排行
     * @return [type] [description]
     */
    public function getTeamRankOp(){
    	$city_info = array();
    	$city_info['team_type'] = 0;
    	$city_info['city_school_id'] = intval($_GET["city_school_id"]);
    	$team_member_info = Model('mz_member')->getMemberInfo(array('member_id'=>$this->member_info['member_id']),"*",array('mz_team'));
    	if (!empty($team_member_info['extend_team_info'])) {
    		$city_info['team_type'] = $team_member_info['extend_team_info']['team_type'];
			if ($city_info['city_school_id'] == 0) {
				$city_info['city_school_id'] = $team_member_info['extend_team_info']['city_school_id'];
			}
    	}
    	if ($city_info['team_type'] == 0) {
    		$city_info['provinceid'] = Model("area")->where(array('area_id'=>$city_info['city_school_id']))->get_field("area_parent_id");
    	}else{
    		$city_info['provinceid'] = Model("school")->where(array('id'=>$city_info['city_school_id']))->get_field("province_id");
    	}

    	$condition = array();
    	$condition['mz_team.city_school_id'] = $city_info['city_school_id'];
    	$condition['mz_team.team_status'] = 1;
    	$team_list = Model()->table('mz_team,mz_statistical')->field("mz_statistical.*,mz_team.team_name,mz_team.num,mz_team.max_num")->join('left')->on("mz_statistical.stat_team_id = mz_team.team_id")->where($condition)->order("mz_statistical.stat_amount desc")->limit(10)->select();
    	output_data(array("data"=>array('team_list'=>$team_list,'city_info'=>$city_info)));
    }
    public function getTeamMemberRankOp(){
    	$city_info = array();
    	$city_info['team_type'] = 0;
    	$city_info['city_school_id'] = intval($_GET["city_school_id"]);
    	$team_member_info = Model('mz_member')->getMemberInfo(array('member_id'=>$this->member_info['member_id']),"*",array('mz_team'));
    	if (!empty($team_member_info['extend_team_info'])) {
    		$city_info['team_type'] = $team_member_info['extend_team_info']['team_type'];
			if ($city_info['city_school_id'] == 0) {
				$city_info['city_school_id'] = $team_member_info['extend_team_info']['city_school_id'];
			}
    	}
    	if ($city_info['team_type'] == 0) {
    		$city_info['provinceid'] = Model("area")->where(array('area_id'=>$city_info['city_school_id']))->get_field("area_parent_id");
    	}else{
    		$city_info['provinceid'] = Model("school")->where(array('id'=>$city_info['city_school_id']))->get_field("province_id");
    	}
    	$condition = array();
    	$condition['mz_team.city_school_id'] = $city_info['city_school_id'];
    	$condition['mz_team.team_status'] = 1;
    	$team_member_list = Model()->table('mz_member,mz_team')->field("mz_member.*,mz_team.team_name")->join('left')->on("mz_member.team_id = mz_team.team_id")->where($condition)->order("integral desc")->limit(10)->select();
    	if (!empty($team_member_list)) {
    		foreach ($team_member_list as $key => $value) {
    			$team_member_list[$key]['member_name'] = Model("member")->where(array('member_id'=>$value['member_id']))->get_field('member_name');
    		}
    	}
    	output_data(array("data"=>array('team_member_list'=>$team_member_list,'city_info'=>$city_info)));
    }
}