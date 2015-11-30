<?php
/**
 * 美站团队
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
    public function getUserTeamOp(){
    	$condition = array();
    	$condition['member_id'] = $this->member_info['member_id'];
    	$model_mz_member =  Model('mz_member');
    	$userteam = $model_mz_member->getMemberInfo($condition,"*",array("mz_team"));
    	if (empty($userteam)) {
    		output_error("请先创建或加入团队");
    	}else{
    		if (empty($userteam['team_id'])) {
    			output_error("请先创建或加入团队");
    		}
    	}
    	output_data(array('data'=>$userteam));
    }

    public function createTeamOp(){
        $data = array();
        $data['team_type'] = intval($_POST['team_type']);
        $data['team_name'] = trim($_POST['team_name']);
        $data['provinceid'] = intval($_POST['provinceid']);
        $data['cityid'] = ($data['team_type']==0)?intval($_POST['cityid']):0;
        $data['schoolid'] = ($data['team_type']==0)?0:intval($_POST['schoolid']);
        $data['team_domain_name'] = trim($_POST['team_domain_name']);
        $data['team_intro'] = trim($_POST['team_intro']);
        $data['team_status'] = 0;
        $data['createtime'] = TIMESTAMP;

        $team_user_info = Model('mz_member')->getMemberInfo(array('member_id'=>$this->member_info['member_id']));
        if (!empty($team_user_info)) {
            if ($team_user_info['team_id'] > 0) {
                output_error("已加入团队，无法创建新团队");
                exit();
            }
            $team_id = Model('mz_team')->addTeam($data);
            if ($team_id) {
                $result = Model('mz_member')->editTeamUser(array('member_id'=>$this->member_info['member_id']),array('team_id'=>$team_id,'type'=>2));
                if ($result) {
                    output_data("创建成功，请等待管理员审核");
                }else{
                    output_error("系统错误");   
                }
            }else{
                output_error("系统错误");
            }
        }else{
            $team_id = Model('mz_team')->addTeam($data);
            if ($team_id) {
                $data_user = array();
                $data_user['member_id'] = $this->member_info['member_id'];
                $data_user['team_id'] = $team_id;
                $data_user['integral'] = 0;
                $data_user['type'] = 2;
                $result = Model("mz_member")->addTeamUser($data_user);
                if ($result) {
                    output_data("创建成功，请等待管理员审核");
                }else{
                    output_error("系统错误");   
                }
            }else{
                output_error("系统错误");
            }
        }
    }
    // public function addSchoolOp(){
    //     $model_school = Model('school');
    //     $model_area = Model('area');
    //     $result = array();
    //     $area_info = $model_area->getAreaInfo(array('area_name'=>$_POST['a']['name']),'area_id,area_name');
    //     $data = array();
    //     $data['province_id'] = $area_info['area_id'];
    //     foreach ($_POST['a']['univs'] as $k => $v) {
    //         $data['school_name'] = $v['name'];
    //         $model_school->addSchool($data);
    //     }
    //     output_data($data);
    // }

    public function getSchoollistOp(){
        $province_id = intval($_GET['province_id']);
        $model_school = Model('school');
        $school_list = $model_school->getSchoolList(array('province_id'=>$province_id));
        output_data(array('data'=>$school_list));
    }
}