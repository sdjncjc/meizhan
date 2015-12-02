<?php
/**
 * 前台登录 退出操作
 *
 *
 *
 *
 * @copyright  Copyright (c) 2007-2015 ShopNC Inc. (http://www.shopnc.net)
 * @license    http://www.shopnc.net
 * @link       http://www.shopnc.net
 * @since      File available since Release v1.1
 */

use Shopnc\Tpl;

defined('InShopNC') or exit('Access Invalid!');

class mz_loginControl extends mobileHomeControl {

    public function __construct(){
        parent::__construct();
    }

    /**
     * 登录
     */
    public function indexOp(){
        if(empty($_POST['username']) || empty($_POST['password']) || !in_array($_POST['client'], $this->client_type_array)) {
            output_error('登录失败');
        }

        $model_member = Model('member');

        $array = array();
        $array['member_name']   = $_POST['username'];
        $array['member_passwd'] = md5($_POST['password']);
        $member_info = $model_member->getMemberInfo($array);
        if(empty($member_info) && preg_match('/^0?(13|15|17|18|14)[0-9]{9}$/i', $_POST['username'])) {//根据会员名没找到时查手机号
            $array = array();
            $array['member_mobile']   = $_POST['username'];
            $array['member_passwd'] = md5($_POST['password']);
            $member_info = $model_member->getMemberInfo($array);
        }
        if(empty($member_info) && (strpos($_POST['username'], '@') > 0)) {//按邮箱和密码查询会员
            $array = array();
            $array['member_email']   = $_POST['username'];
            $array['member_passwd'] = md5($_POST['password']);
            $member_info = $model_member->getMemberInfo($array);
        }

        if(!empty($member_info)) {
            $token = $this->_get_token($member_info['member_id'], $member_info['member_name'], $_POST['client']);
            if($token) {
        		$mz_member = Model('mz_member')->where(array('member_id'=>$member_info['member_id']))->find();
				if(!$mz_member)Model('mz_member')->insert(array('member_id'=>$member_info['member_id']));
                output_data(array('username' => $member_info['member_name'], 'userid' => $member_info['member_id'], 'key' => $token));
            } else {
                output_error('登录失败');
            }
        } else {
            output_error('用户名密码错误');
        }
    }

    /**
     * 登录生成token
     */
    private function _get_token($member_id, $member_name, $client) {
        $model_mb_user_token = Model('mb_user_token');

        //重新登录后以前的令牌失效
        //暂时停用
        //$condition = array();
        //$condition['member_id'] = $member_id;
        //$condition['client_type'] = $client;
        //$model_mb_user_token->delMbUserToken($condition);

        //生成新的token
        $mb_user_token_info = array();
        $token = md5($member_name . strval(TIMESTAMP) . strval(rand(0,999999)));
        $mb_user_token_info['member_id'] = $member_id;
        $mb_user_token_info['member_name'] = $member_name;
        $mb_user_token_info['token'] = $token;
        $mb_user_token_info['login_time'] = TIMESTAMP;
        $mb_user_token_info['client_type'] = $client;

        $result = $model_mb_user_token->addMbUserToken($mb_user_token_info);

        if($result) {
            return $token;
        } else {
            return null;
        }

    }

	
    /**
     * 短信动态码
     */
    public function get_captchaOp(){
        $state = '发送失败';
        $phone = $_GET['phone'];
		if(preg_match('/^0?(13|15|17|18|14)[0-9]{9}$/i', $phone)) {//判断手机号
            $log_type = $_GET['type'];//短信类型:1为注册,2为登录,3为找回密码
            $state = 'true';
            $model_sms_log = Model('sms_log');
            $condition = array();
            $condition['log_ip'] = getIp();
            $condition['log_type'] = $log_type;
            $sms_log = $model_sms_log->getSmsInfo($condition);
            if(!empty($sms_log) && ($sms_log['add_time'] > TIMESTAMP-600)) {//同一IP十分钟内只能发一条短信
                $state = '同一IP地址十分钟内，请勿多次获取动态码！';
            }
            $condition = array();
            $condition['log_phone'] = $phone;
            $condition['log_type'] = $log_type;
            $sms_log = $model_sms_log->getSmsInfo($condition);
            if($state == 'true' && !empty($sms_log) && ($sms_log['add_time'] > TIMESTAMP-600)) {//同一手机号十分钟内只能发一条短信
                $state = '同一手机号十分钟内，请勿多次获取动态码！';
            }
            $time24 = TIMESTAMP-60*60*24;
            $condition = array();
            $condition['log_phone'] = $phone;
            $condition['add_time'] = array('egt',$time24);
            $num = $model_sms_log->getSmsCount($condition);
            if($state == 'true' && $num >= 5) {//同一手机号24小时内只能发5条短信
                $state = '同一手机号24小时内，请勿多次获取动态码！';
            }
            $condition = array();
            $condition['log_ip'] = getIp();
            $condition['add_time'] = array('egt',$time24);
            $num = $model_sms_log->getSmsCount($condition);
            if($state == 'true' && $num >= 20) {//同一IP24小时内只能发20条短信
                $state = '同一IP24小时内，请勿多次获取动态码！';
            }
            if($state == 'true') {
                $log_array = array();
                $model_member = Model('member');
                $member = $model_member->getMemberInfo(array('member_mobile'=> $phone));
                $captcha = rand(100000, 999999);
                $log_msg = '【'.C('site_name').'】您于'.date("Y-m-d");
                switch ($log_type) {
                    case '1':
                        if(C('sms_register') != 1) {
                            $state = '系统没有开启手机注册功能';
                        }
                        if(!empty($member)) {//检查手机号是否已被注册
                            $state = '当前手机号已被注册，请更换其他号码。';
                        }
                        $log_msg .= '申请注册会员，动态码：'.$captcha.'。';
                        break;
                    case '2':
                        if(C('sms_login') != 1) {
                            $state = '系统没有开启手机登录功能';
                        }
                        if(empty($member)) {//检查手机号是否已绑定会员
                            $state = '当前手机号未注册，请检查号码是否正确。';
                        }
                        $log_msg .= '申请登录，动态码：'.$captcha.'。';
                        $log_array['member_id'] = $member['member_id'];
                        $log_array['member_name'] = $member['member_name'];
                        break;
                    case '3':
                        if(C('sms_password') != 1) {
                            $state = '系统没有开启手机找回密码功能';
                        }
                        if(empty($member)) {//检查手机号是否已绑定会员
                            $state = '当前手机号未注册，请检查号码是否正确。';
                        }
                        $log_msg .= '申请重置登录密码，动态码：'.$captcha.'。';
                        $log_array['member_id'] = $member['member_id'];
                        $log_array['member_name'] = $member['member_name'];
                        break;
                    default:
                        $state = '参数错误';
                        break;
                }
                if($state == 'true'){
                    $sms = new Sms();
                    $result = $sms->send($phone,$log_msg);
                    if($result){
                        $log_array['log_phone'] = $phone;
                        $log_array['log_captcha'] = $captcha;
                        $log_array['log_ip'] = getIp();
                        $log_array['log_msg'] = $log_msg;
                        $log_array['log_type'] = $log_type;
                        $log_array['add_time'] = time();
                        $model_sms_log->addSms($log_array);
                    } else {
                        $state = '手机短信发送失败';
                    }
                }
            }
        } else {
            $state = '手机号码错误';
        }
		if($state == 'true'){
			output_data('获取成功');
		}else{
			output_error($state);
		}
    }

    /**
     * 注册
     */
    public function registerOp(){
        $model_member   = Model('member');
        $phone = $_POST['phone'];
        $captcha = $_POST['captcha'];
        if (strlen($phone) == 11 && strlen($captcha) == 6){
            if(C('sms_register') != 1) {
                output_error('系统没有开启手机注册功能');
            }
			if(!preg_match('/^0?(13|15|17|18|14)[0-9]{9}$/i', $phone)) {
			   output_error('手机号码填写错误');
			}
			if(!preg_match('/^[a-zA-Z0-9`~@!#$%^&*()-=_+]{6,20}$/i', $_POST['password'])) {
			   output_error('密码为6-20位字母、数字或符号');
			}
            $member = $model_member->getMemberInfo(array('member_mobile'=> $phone));//检查手机号是否已被注册
            if(!empty($member)) {
                output_error('手机号已被注册');
            }
            $member_name = 'm_'.$phone;
			//检查重名
			$i = 0;
            while($model_member->getMemberInfo(array('member_name'=> $member_name))){
				$member_name = 'm'.$i.'_'.$phone;
				$i++;
			}
            $condition = array();
            $condition['log_phone'] = $phone;
            $condition['log_captcha'] = $captcha;
            $condition['log_type'] = 1;
            $model_sms_log = Model('sms_log');
            $sms_log = $model_sms_log->getSmsInfo($condition);
            if(empty($sms_log) || ($sms_log['add_time'] < TIMESTAMP-1800)) {//半小时内进行验证为有效
                output_error('动态码错误或已过期，重新输入');
            }
            
            $member = array();
            $member['member_name'] = $member_name;
            $member['member_passwd'] = $_POST['password'];
            $member['member_email'] = '';
            $member['member_mobile'] = $phone;
            $member['member_mobile_bind'] = 1;
            $result = $model_member->addMember($member);
            if($result) {
				output_data('注册成功，请重新登录。');
            } else {
				output_error('注册失败');
            }
		}
		output_error('参数错误');
    }

    /**
     * 找回密码的发邮件处理
     */
    public function send_emailOp(){
		$email = $_POST['email'];
        if(empty($email)){
            output_error('邮箱不能为空');
        }
        $model_member   = Model('member');
        $member = $model_member->getMemberInfo(array('member_email'=>$email));
        if(empty($member) or !is_array($member)){
            output_error('该邮箱未被注册');
        }
        //发送频率验证
        $member_common_info = $model_member->getMemberCommonInfo(array('member_id'=>$member['member_id']));
        if (!empty($member_common_info['send_email_time']) && TIMESTAMP - $member_common_info['send_email_time'] < 58) {
            output_error('请60秒以后再次发送邮件');
        }
        $seed = random(6);
        $data = array();
        $data['auth_code'] = $seed;
        $data['send_acode_time'] = TIMESTAMP;
        $data['send_email_time'] = TIMESTAMP;
        $data['send_acode_times'] = array('exp','send_acode_times+1');
        $update = $model_member->editMemberCommon($data,array('member_id'=>$member['member_id']));
        if (!$update) {
            output_error('系统发生错误，如有疑问请与管理员联系');
        }

        $model_tpl = Model('mail_templates');
        $tpl_info = $model_tpl->getTplInfo(array('code'=>'reset_pwd'));
        $param = array();
        $param['site_name'] = C('site_name');
        $param['user_name'] = $member['member_name'];
        $param['site_url'] = $_POST['site_url'];
        $param['verify_url'] = $param['site_url'].'/login/find_password_email.html?uid='.base64_encode(encrypt($member['member_id'].' '.$email)).'&hash='.md5($seed);
        $subject    = ncReplaceText($tpl_info['title'],$param);
        $message    = ncReplaceText($tpl_info['content'],$param);

        \Shopnc\Lib::messager()->send($email,$subject,$message);
        output_data('成功发送找回密码邮件,请登录邮箱及时查收',true);
    }

    /**
     * 设置密码
     */
    public function email_set_passwordOp() {
		if(!preg_match('/^[a-zA-Z0-9`~@!#$%^&*()-=_+]{6,20}$/i', $_GET['password'])) {
		   output_error('密码为6-20位字母、数字或符号');
		}
		$model_member = Model('member');
		$uid = @base64_decode($_GET['uid']);
		$uid = decrypt($uid,'');
		list($member_id,$member_email) = explode(' ', $uid);
		
		if (!is_numeric($member_id)) {
		   output_error('验证失败');
		}
		
		$member_info = $model_member->getMemberInfo(array('member_id'=>$member_id));
		if ($member_info['member_email'] != $member_email) {
		   output_error('验证失败');
		}
		
		$member_common_info = $model_member->getMemberCommonInfo(array('member_id'=>$member_id));
		if (empty($member_common_info) || !is_array($member_common_info)) {
		   output_error('验证失败');
		}
		if (md5($member_common_info['auth_code']) != $_GET['hash'] || TIMESTAMP - $member_common_info['send_acode_time'] > 24*3600) {
		   output_error('验证失败');
		}
		
		$update = $model_member->editMember(array('member_id'=>$member_id),array('member_email_bind'=>1,'member_passwd'=>md5($_GET['password'])));
		if (!$update) {
		   output_error('系统发生错误，如有疑问请与管理员联系');
		}
		
		$data = array();
		$data['auth_code'] = '';
		$data['send_acode_time'] = 0;
		$update = $model_member->editMemberCommon($data,array('member_id'=>$member_id));
		if (!$update) {
		   output_error('系统发生错误，如有疑问请与管理员联系');
		}
		$model_member->createSession($member_info);//自动登录
		output_data('密码修改成功，请重新登录。');
    }
	
    public function phone_set_passwordOp() {
		if(C('sms_password') != 1) {
			output_error('系统没有开启手机找回密码功能');
		}
		if(!preg_match('/^[a-zA-Z0-9`~@!#$%^&*()-=_+]{6,20}$/i', $_POST['password'])) {
		   output_error('密码为6-20位字母、数字或符号');
		}
		$phone = $_POST['phone'];
		$captcha = $_POST['captcha'];
		$condition = array();
		$condition['log_phone'] = $phone;
		$condition['log_captcha'] = $captcha;
		$condition['log_type'] = 3;
		$model_sms_log = Model('sms_log');
		$sms_log = $model_sms_log->getSmsInfo($condition);
		if(empty($sms_log) || ($sms_log['add_time'] < TIMESTAMP-1800)) {//半小时内进行验证为有效
			output_error('动态码错误或已过期，重新输入');
		}
		$model_member = Model('member');
		$member = $model_member->getMemberInfo(array('member_mobile'=> $phone));//检查手机号是否已被注册
		if(!empty($member)) {
			$result = $model_member->editMember(array('member_id'=> $member['member_id']),array('member_phone_bind'=>1,'member_passwd'=> md5($_GET['password'])));
			if($result) {
				output_data('密码修改成功，请重新登录。');
			} else {
				output_error('密码修改失败');
			}
		}
		output_error('该手机号码未被注册');
	}
}
