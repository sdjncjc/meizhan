<?php
/**
 * 实名认证
 *
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

class member_verifiedControl extends mobileMemberControl {

	public function __construct(){
		parent::__construct();
	}

    /**
     * 实名认证
     */
	public function indexOp() {
        output_data(array('member_info' => $this->member_info));
	}

	/**
	 * 提交认证
	 */
	public function verified_submitOp(){
        if(empty($_POST['member_truename']) || empty($_POST['member_idnum']) || empty($_POST['member_mobile']) || empty($_POST['vcode'])) {
            output_data(array('msg'=>1,'error'=>'认证失败'));
        }
		$model_member = Model('member');
		$member_info = $this->member_info;
		$member_array = array();
		$member_array['member_truename']	= trim($_POST['member_truename']);
		$member_array['member_idnum']	    = strtoupper($_POST['member_idnum']);
		if($member_info['member_mobile_bind'] != '1'){
			$condition = array();
			$condition['member_id'] = $member_info['member_id'];
			$condition['auth_code'] = intval($_POST['vcode']);
			$member_common_info = $model_member->getMemberCommonInfo($condition,'send_acode_time');
			if (!$member_common_info) {
				output_data(array('msg'=>1,'error'=>'手机验证码错误，请重新输入'));
			}
			if (TIMESTAMP - $member_common_info['send_acode_time'] > 1800) {
				output_data(array('msg'=>1,'error'=>'手机验证码已过期，请重新获取验证码'));
			}
			$data = array();
			$data['auth_code'] = '';
			$data['send_acode_time'] = 0;
			$update = $model_member->editMemberCommon($data,array('member_id'=>$member_info['member_id']));
			if (!$update) {
				output_data(array('msg'=>1,'error'=>'系统发生错误，如有疑问请与管理员联系'));
			}
			$model_member->editMember(array('member_mobile'=>$_POST['member_mobile'],'member_id'=>array('neq',$member_info['member_id'])),array('member_mobile'=>'','member_mobile_bind'=>0));
			$member_array['member_mobile']	= trim($_POST['member_mobile']);
			$member_array['member_mobile_bind'] = 1;
		}
		$update = $model_member->editMember(array('member_id'=>$member_info['member_id']),$member_array);
		if (!$update) {
			output_data(array('msg'=>1,'error'=>'系统发生错误，如有疑问请与管理员联系'));
		}
		output_data(array('msg'=>0,'error'=>'认证成功'));
    }


    /**
     * 手机号发送验证码
     */
    public function send_modify_mobileOp() {
        if ($_POST['mobile'] == ''){
            output_data(array('state'=>'false','msg'=>'手机号错误'));
        }

        $model_member = Model('member');
        $condition = array();
        $condition['member_mobile'] = $_POST['mobile'];
        $condition['member_mobile_bind'] = 1;
        $condition['member_id'] = array('neq',$this->member_info['member_id']);
        $member_info = $model_member->getMemberInfo($condition,'member_id');
        if ($member_info) {
            output_data(array('state'=>'false','msg'=>'该手机号已被使用，请更换其它手机号'));
        }

        $verify_code = rand(100,999).rand(100,999);
        $data = array();
        $data['auth_code'] = $verify_code;
        $data['send_acode_time'] = TIMESTAMP;
        $update = $model_member->editMemberCommon($data,array('member_id'=>$this->member_info['member_id']));
        if (!$update) {
            output_data(array('state'=>'false','msg'=>'系统发生错误，如有疑问请与管理员联系'));
        }

        $model_tpl = Model('mail_templates');
        $tpl_info = $model_tpl->getTplInfo(array('code'=>'modify_mobile'));
        $param = array();
        $param['site_name']	= C('site_name');
        $param['send_time'] = date('Y-m-d H:i',TIMESTAMP);
        $param['verify_code'] = $verify_code;
        $message	= ncReplaceText($tpl_info['content'],$param);
        $sms = new Sms();
        $result = $sms->send($_POST["mobile"],$message);
        if ($result) {
            output_data(array('state'=>'true','msg'=>'发送成功'));
        } else {
            output_data(array('state'=>'false','msg'=>'发送失败'));
        }
    }
}
