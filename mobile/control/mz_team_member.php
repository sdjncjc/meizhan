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
    /**
     * 获取成员申请列表
     * @return [type] [description]
     */
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
    /**
     * 审核成员加入
     * @return [type] [description]
     */
    public function auditMemberOp(){
		$mz_team_lock = intval(C('mz_team_lock'));
		if ($mz_team_lock > 0 && $mz_team_lock <= date('m')){
            output_error("每月{$mz_team_lock}日后不能添加会员");
		}
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
			//更新小组积分
    		Model("mz_team")->updateTeamIntegra($team_member_info['team_id']);
    		output_data("已同意添加到本组");
    	}
    	output_error("参数错误");
    }
    /**
     * 获取小组成员列表
     * @return [type] [description]
     */
    public function getMemberListOp(){
        $team_member_info = Model("mz_member")->getMemberInfo(array('member_id'=>$this->member_info['member_id']),'*',array('mz_team'));
        if ($team_member_info['team_id'] <= 0) {
            output_error("未加入小组");
        }
        $member_list = Model("mz_member")->where(array('team_id'=>$team_member_info['team_id']))->select();
        foreach ($member_list as $key => $value) {
            $member_info = Model('member')->where(array('member_id'=>$value['member_id']))->field('member_name,member_avatar')->find();
            $member_list[$key]['member_name'] = $member_info['member_name'];
            $member_list[$key]['member_grade'] = $this->getMemberGrade($value['integral']);
            $member_list[$key]['member_avatar'] = getMemberAvatar($member_info['member_avatar']);

        }
        output_data(array('data'=>array('self_info'=>$team_member_info,'member_list'=>$member_list)));
    }
    /**
     * 移出小组
     * @return [type] [description]
     */
    public function layoffMemberOp(){
		$mz_team_lock = intval(C('mz_team_lock'));
		if ($mz_team_lock > 0 && $mz_team_lock <= date('m')){
            output_error("每月{$mz_team_lock}日后不能删除会员");
		}
        $member_id = intval($_GET['member_id']);
        $team_member_info = Model("mz_member")->getMemberInfo(array('member_id'=>$this->member_info['member_id']));
        if ($member_id >0) {
            $layoff_member_info = Model("mz_member")->getMemberInfo(array('member_id'=>$member_id));
            if ($team_member_info['type'] != 1 || ($team_member_info['team_id'] != $layoff_member_info['team_id'])) {
                output_error("参数错误或无权限");
            }
        }else{
            if ($team_member_info['type'] == 1) {
                output_error("组长无法退出小组");
                exit();
            }
            $member_id = $this->member_info['member_id'];
        }
        $result = Model("mz_member")->where(array('member_id'=>$member_id))->update(array('team_id'=>0));
        if ($result) {
            $num = Model("mz_team")->where(array('team_id'=>$team_member_info['team_id']))->get_field('num');
            Model("mz_team")->where(array('team_id'=>$team_member_info['team_id']))->update(array('num'=>$num-1));
			//更新小组积分
    		Model("mz_team")->updateTeamIntegra($team_member_info['team_id']);
            output_data("操作成功");
        }else{
            output_data("系统错误");
        }
    }
    /**
     * 解散小组
     * @return [type] [description]
     */
    public function dissolutionOp(){
        $team_member_info = Model("mz_member")->getMemberInfo(array('member_id'=>$this->member_info['member_id']));
        if ($team_member_info['type'] != 1) {
            output_error("无权限");
        }
        $team_info = Model("mz_team")->where(array('team_id'=>$team_member_info['team_id']))->find();
        if ($team_info['team_status'] != 1) {
            output_error("小组状态不正确");
        }
        if ($team_info['num'] > 1) {
            output_error("成员数大于1,无法解散小组");
        }
        $result1 = Model("mz_member")->where(array('member_id'=>$this->member_info['member_id']))->update(array('team_id'=>0,'type'=>0));
        if ($result1) {
            $result2 = Model("mz_team")->where(array('team_id'=>$team_info['team_id']))->update(array('num'=>0,'team_status'=>2));
            if($result2){
                output_data("小组解散成功");
            }else{
                Model("mz_member")->where(array('member_id'=>$this->member_info['member_id']))->update(array('team_id'=>$team_info['team_id'],'type'=>1));
                output_data("系统错误，小组解散失败");
            }
        }else{
            output_data("系统错误，小组解散失败");
        }
    }
    /**
     * 转让小组
     * @return [type] [description]
     */
    public function transferTeamOp(){
        $member_id = intval($_GET['member_id']);
        $team_member_info = Model("mz_member")->getMemberInfo(array('member_id'=>$this->member_info['member_id']));
        $team_info = Model("mz_team")->where(array('team_id'=>$team_member_info['team_id']))->find();
        if ($team_info['team_status'] !=1) {
            output_error("小组状态不正确");
        }
        $transfer_member_info = Model("mz_member")->getMemberInfo(array('member_id'=>$member_id));
        if (($team_member_info['type'] !=1) || ($team_member_info['team_id'] != $transfer_member_info['team_id'])) {
            output_error("无权限");
        }
        $result1 = Model("mz_member")->where(array('member_id'=>$team_member_info['member_id']))->update(array('type'=>0));
        if ($result1) {
            $result2 =  Model("mz_member")->where(array('member_id'=>$transfer_member_info['member_id']))->update(array('type'=>1));
            if ($result2) {
                output_data("小组转让成功");
            }else{
                Model("mz_member")->where(array('member_id'=>$team_member_info['member_id']))->update(array('type'=>1));
                output_error("小组转让失败");
            }
        }else{
            output_error("系统错误，小组转让失败");
        }
    }
    public function balanceAllotOp(){
        $params = trim($_POST['params']);
        $team_member_info = Model("mz_member")->getMemberInfo(array('member_id'=>$this->member_info['member_id']),'',array('mz_team'));
        if ($team_member_info['type'] != 1) {
            output_error("无权限");
        }
        $member_list = array();
        $total_amount = 0;
        $params_arr = explode(",", $params);
        foreach ($params_arr as $key => $value) {
            $temp = explode("|", $value);
            if (empty($temp[1])) {
                $temp[1] = 0;
            }
            $member_list[] = $temp;
            $total_amount += $temp[1];
        }
        if ($total_amount > $team_member_info["extend_team_info"]['team_balance']) {
            output_error("金额超出");
        }
        foreach ($member_list as $key => $value) {
            $member = Model('member')->where(array('member_id'=>$value['0']))->field('member_id,member_name')->find();
            if (!empty($member)) {
                $data = array();
                $data["balance_teamid"] = $team_member_info["extend_team_info"]['team_id'];
                $data["balance_teamname"] = $team_member_info["extend_team_info"]['team_name'];
                $data["balance_price"] = $value['1'];
                $data["balance_desc"] = '小组'.$team_member_info["extend_team_info"]['team_name'].'给'.$member['member_name'].'分配佣金';
                $result1 = Model("mz_balance")->saveMzBalanceLog("allot",$data);
                if ($result1) {
                    $log_array = array();
                    $log_array['member_id'] = $member['member_id'];
                    $log_array['member_name'] = $member['member_name'];
                    $log_array['lg_desc'] = '小组'.$team_member_info["extend_team_info"]['team_name'].'分配佣金';
                    $log_array['amount'] = $value["1"];
                    $result2 = Model('predeposit')->changePd('mz_divided', $log_array);
                }
            }
        }
        output_data("佣金分配完成");
    }
    public function getPromoteOp(){
        // $team_member_info = Model("mz_member")->getMemberInfo(array('member_id'=>$this->member_info['member_id']));
        $url = "http://mz.qiqin.net/index.html?pm=".$this->member_info['member_id'];
        $mobile_app = 'mb_app.png';
        require_once(BASE_RESOURCE_PATH.DS.'phpqrcode'.DS.'index.php');
        $PhpQRCode = new PhpQRCode();
        $PhpQRCode->set('pngTempDir',BASE_UPLOAD_PATH.DS.ATTACH_MOBILE.DS);
        $PhpQRCode->set('date',$url);
        $PhpQRCode->set('matrixPointSize',15);
        $PhpQRCode->set('pngTempName', $mobile_app);
        $PhpQRCode->init();
        $data = array();
        $data['url'] = $url;
        $data['qrcode'] = UPLOAD_SITE_URL.DS.ATTACH_MOBILE.DS.C('mobile_app');
        output_data(array('data'=>$data));
    }
}