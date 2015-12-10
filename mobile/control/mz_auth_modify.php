<?php
/**
 * 美站修改密码
 *
 * @copyright  Copyright (c) 2007-2013 ShopNC Inc. (http://www.shopnc.net)
 * @license    http://www.shopnc.net
 * @link       http://www.shopnc.net
 * @since      File available since Release v1.1
 */

use Shopnc\Tpl;

defined('InShopNC') or exit('Access Invalid!');
class mz_auth_modifyControl extends mobileMemberControl {

    public function __construct() {
        parent::__construct();
    }

    public function indexOp() {
        exit;
    }
    /**
     * 统一发送身份验证码
     */
    public function send_auth_codeOp() {
        if (!in_array($_GET['type'],array('email','mobile'))) exit();
        $model_member = Model('member');
        $member_info = $model_member->getMemberInfoByID($this->member_info['member_id'],'member_email,member_mobile');

        //发送频率验证
        $member_common_info = $model_member->getMemberCommonInfo(array('member_id'=>$this->member_info['member_id']));
        if (!empty($member_common_info['send_acode_time'])) {
            if (date('Ymd',$member_common_info['send_acode_time']) != date('Ymd',TIMESTAMP)) {
                $data = array();
                $data['send_acode_times'] = 0;
                $update = $model_member->editMemberCommon($data,array('member_id'=>$this->member_info['member_id']));
            } else {
                if (TIMESTAMP - $member_common_info['send_acode_time'] < 58) {
                	output_error('请60秒以后再次发送短信');
                } else {
                    if ($member_common_info['send_acode_times'] >= 15) {
                		output_error('您今天发送验证信息已超过15条，今天将无法再次发送');
                    }
                }
            }
        }

        $verify_code = rand(100,999).rand(100,999);
        $model_tpl = Model('mail_templates');
        $tpl_info = $model_tpl->getTplInfo(array('code'=>'authenticate'));

        $param = array();
        $param['send_time'] = date('Y-m-d H:i',TIMESTAMP);
        $param['verify_code'] = $verify_code;
        $param['site_name'] = C('site_name');
        $subject = ncReplaceText($tpl_info['title'],$param);
        $message = ncReplaceText($tpl_info['content'],$param);
        if ($_GET['type'] == 'email') {
            try {
                \Shopnc\Lib::messager()->send($member_info["member_email"],$subject,$message);
                $result = true;
            } catch (\Shopnc\Lib\Messager\Exception $ex) {
                $result = false;
            }
        } elseif ($_GET['type'] == 'mobile') {
        	$result = true;
            // $sms = new Sms();
            // $result = $sms->send($member_info["member_mobile"],$message);
        }
        if ($result) {
            $data = array();
            $update_data['auth_code'] = $verify_code;
            $update_data['send_acode_time'] = TIMESTAMP;
            $update_data['send_acode_times'] = array('exp','send_acode_times+1');
            $update = $model_member->editMemberCommon($update_data,array('member_id'=>$this->member_info['member_id']));
            if (!$update) {
                output_error('系统发生错误，如有疑问请与管理员联系');
            }
            output_data('验证码已发出，请注意查收');
        } else {
            output_error('验证码发送失败');
        }
    }

    /**
     * 修改手机号 - 发送验证码
     */
    public function send_modify_mobileOp() {
        $obj_validate = new Validate();
        $obj_validate->validateparam = array(
            array("input"=>$_GET["mobile"], "require"=>"true", 'validator'=>'mobile',"message"=>'请正确填写手机号码'),
        );
        $error = $obj_validate->validate();
        if ($error != ''){
            output_error($error);
        }

        $model_member = Model('member');
        //发送频率验证
        $member_common_info = $model_member->getMemberCommonInfo(array('member_id'=>$this->member_info['member_id']));
        if (!empty($member_common_info['send_mb_time'])) {
            if (date('Ymd',$member_common_info['send_mb_time']) != date('Ymd',TIMESTAMP)) {
                $data = array();
                $data['send_mb_times'] = 0;
                $update = $model_member->editMemberCommon($data,array('member_id'=>$this->member_info['member_id']));               
            } else {
                if (TIMESTAMP - $member_common_info['send_mb_time'] < 58) {
                    output_error('请60秒以后再次发送短信');
                } else {
                    if ($member_common_info['send_mb_times'] >= 15) {
                        output_error('您今天发送短信已超过15条，今天将无法再次发送');
                    }
                }                
            }
        }

        $condition = array();
        $condition['member_mobile'] = $_GET['mobile'];
        $condition['member_id'] = array('neq',$this->member_info['member_id']);
        $member_info = $model_member->getMemberInfo($condition);
        if ($member_info) {
            output_error('该手机号已被使用，请更换其它手机号');
        }

        $data = array();
        $data['member_mobile'] = $_GET['mobile'];
        $data['member_mobile_bind'] = 0;
        $update = $model_member->editMember(array('member_id'=>$this->member_info['member_id']),$data);
        if (!$update) {
            output_error('系统发生错误，如有疑问请与管理员联系');
        }

        $verify_code = rand(100,999).rand(100,999);

        $model_tpl = Model('mail_templates');
        $tpl_info = $model_tpl->getTplInfo(array('code'=>'modify_mobile'));
        $param = array();
        $param['site_name'] = C('site_name');
        $param['send_time'] = date('Y-m-d H:i',TIMESTAMP);
        $param['verify_code'] = $verify_code;
        $message    = ncReplaceText($tpl_info['content'],$param);
        $result = true;
        // $sms = new Sms();
        // $result = $sms->send($_GET["mobile"],$message);
        if ($result) {
            $data = array();
            $data['auth_code'] = $verify_code;
            $data['send_acode_time'] = TIMESTAMP;
            $data['send_mb_time'] = TIMESTAMP;
            $data['send_mb_times'] = array('exp','send_mb_times+1');
            $update = $model_member->editMemberCommon($data,array('member_id'=>$this->member_info['member_id']));
            if (!$update) {
                output_error('系统发生错误，如有疑问请与管理员联系');
            }
            output_data("发送成功");
        } else {
            output_error('发送失败');
        }
    }
    // 判断验证码是否正确
    public function checkCaptchaOp(){
        output_data(array('data'=>self::checkCode(intval($_GET['captcha']))));
    }
    /**
     * 修改登陆密码或支付密码
     * @return [type] [description]
     */
    public function modifyPwdOp(){
    	$model_member = Model("member");
        $obj_validate = new Validate();
        $obj_validate->validateparam = array(
                array("input"=>$_POST["type"],      "require"=>"true",      "message"=>'非法操作'),
                array("input"=>$_POST["captcha"],      "require"=>"true",      "message"=>'请正确输入安全验证码'),
                array("input"=>$_POST["password"],      "require"=>"true",      "message"=>'请正确输入密码'),
                array("input"=>$_POST["confirm_password"],  "require"=>"true",      "validator"=>"Compare","operator"=>"==","to"=>$_POST["password"],"message"=>'两次密码输入不一致'),
        );
        $error = $obj_validate->validate();
        if ($error != ''){
        	output_error($error);
        }
        $result_checkcode = self::checkCode(intval($_POST['captcha']));
        if ($result_checkcode['result'] != 'succ') {
        	output_error($result_checkcode['message']);
        }else{
            $data = array();
            $data['auth_code'] = '';
            $data['send_acode_time'] = 0;
            $update = $model_member->editMemberCommon($data,array('member_id'=>$this->member_info['member_id']));
            if (!$update) {
            	output_error('系统发生错误，如有疑问请与管理员联系');
            }
        }
        if ($_POST['type'] == trim('pwd')) {
        	$result = $model_member->editMember(array('member_id'=>$this->member_info['member_id']),array('member_passwd'=>md5($_POST['password'])));
        }elseif ($_POST['type'] == trim('paypwd')) {
        	$result = $model_member->editMember(array('member_id'=>$this->member_info['member_id']),array('member_paypwd'=>md5($_POST['password'])));
        }
        if ($result) {
        	output_data('密码设置成功');
        }else{
        	output_error('密码设置失败');
        }
    }

    /**
     * 绑定手机
     */
    public function modify_mobileOp() {
        $model_member = Model('member');
        $obj_validate = new Validate();
        $obj_validate->validateparam = array(
            array("input"=>$_POST["mobile"], "require"=>"true", 'validator'=>'mobile',"message"=>'请正确填写手机号'),
            array("input"=>$_POST["captcha2"], "require"=>"true", 'validator'=>'number',"message"=>'请正确填写手机验证码'),
        );
        $error = $obj_validate->validate();
        if ($error != ''){
            output_error($error);
        }

        $result_checkcode = self::checkCode(intval($_POST['captcha2']));
        if ($result_checkcode['result'] != 'succ') {
            output_error($result_checkcode['message']);
        }else{
            $data = array();
            $data['auth_code'] = '';
            $data['send_acode_time'] = 0;
            $update = $model_member->editMemberCommon($data,array('member_id'=>$this->member_info['member_id']));
            if (!$update) {
                output_error('系统发生错误，如有疑问请与管理员联系');
            }
        }
        $update = $model_member->editMember(array('member_id'=>$this->member_info['member_id']),array('member_mobile_bind'=>1));
        if (!$update) {
            output_error('系统发生错误，如有疑问请与管理员联系');
        }
        output_data('手机号绑定成功');
    }
    public function modify_emailOp(){
        $model_member = Model('member');
        $obj_validate = new Validate();
        $obj_validate->validateparam = array(
            array("input"=>$_POST["email"], "require"=>"true", 'validator'=>'email',"message"=>'请正确填写邮箱地址')
        );
        $error = $obj_validate->validate();
        if ($error != ''){
            output_error($error);
        }
        if ($this->member_info['member_email_bind'] == 1) {
            if (empty($_POST['captcha'])) {
                output_error("请正确填写手机验证码");
            }
            $result_checkcode = self::checkCode(intval($_POST['captcha']));
            if ($result_checkcode['result'] != 'succ') {
                output_error($result_checkcode['message']);
            }
        }

        //发送频率验证
        $member_common_info = $model_member->getMemberCommonInfo(array('member_id'=>$this->member_info['member_id']));
        
        if (!empty($member_common_info['send_email_time']) && TIMESTAMP - $member_common_info['send_email_time'] < 58) {
            output_error('请60秒以后再次发送邮件');
        }
        $condition = array();
        $condition['member_email'] = $_POST['email'];
        $condition['member_id'] = array('neq',$this->member_info['member_id']);
        $member_info = $model_member->getMemberInfo($condition,'member_id');
        if ($member_info) {
            output_error('该邮箱已被使用');
        }
        $data = array();
        $data['member_email'] = $_POST['email'];
        $data['member_email_bind'] = 0;
        $update = $model_member->editMember(array('member_id'=>$this->member_info['member_id']),$data);
        if (!$update) {
            output_error('系统发生错误，如有疑问请与管理员联系');
        }

        $seed = random(6);
        $data = array();
        $data['auth_code'] = $seed;
        $data['send_acode_time'] = TIMESTAMP;
        $data['send_email_time'] = TIMESTAMP;
        $data['send_acode_times'] = array('exp','send_acode_times+1');
        $update = $model_member->editMemberCommon($data,array('member_id'=>$this->member_info['member_id']));
        if (!$update) {
            output_error('系统发生错误，如有疑问请与管理员联系');
        }
        $uid = base64_encode(encrypt($this->member_info['member_id'].' '.$_POST["email"]));
        $verify_url = SHOP_SITE_URL.'/index.php?act=login&op=bind_email&uid='.$uid.'&hash='.md5($seed);

        $model_tpl = Model('mail_templates');
        $tpl_info = $model_tpl->getTplInfo(array('code'=>'bind_email'));
        $param = array();
        $param['site_name'] = C('site_name');
        $param['user_name'] = $this->member_info['member_name'];
        $param['verify_url'] = $verify_url;
        $subject    = ncReplaceText($tpl_info['title'],$param);
        $message    = ncReplaceText($tpl_info['content'],$param);

        \Shopnc\Lib::messager()->send($_POST["email"],$subject,$message);
        output_data("验证邮件已经发送至您的邮箱，请于24小时内登录邮箱并完成验证！如果您始终未收到邮件，请于60秒后重新发送".$verify_url);
    }
    /**
     * 判断验证码
     * @param  [type] $captcha [description]
     * @return [type]          [description]
     */
    private function checkCode($captcha){
        $model_member = Model('member');
    	$result = array();
        $condition = array();
        $condition['member_id'] = $this->member_info['member_id'];
        $condition['auth_code'] = $captcha;
        $member_common_info = $model_member->getMemberCommonInfo($condition,'send_acode_time');
        if (empty($member_common_info)) {
        	$result['result'] = "fail";
        	$result['message'] = '验证码错误，请重新输入';
        }else{
	        if (TIMESTAMP - $member_common_info['send_acode_time'] > 1800) {
	        	$result['result'] = "fail";
	        	$result['message'] = '验证码已过期，请重新获取验证码';
	        }else{
	        	$result['result'] = "succ";
	        }
	    }
	    return $result;
    }
}