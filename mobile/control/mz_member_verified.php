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
class mz_member_verifiedControl extends mobileMemberControl {

    public function __construct() {
        parent::__construct();
    }

    public function indexOp() {
        exit;
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
        	print_r($member_info);
            output_error('该手机号已被使用，请更换其它手机号');
        }
        if ($this->member_info['member_mobile_bind'] == 1) {
        	if ($this->member_info['member_mobile'] != $_GET['oldmobile']) {
        		output_error("原手机号码不正确");
        	}
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
        $sms = new Sms();
        $result = $sms->send($_GET["mobile"],$message);
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

    /**
     * 绑定手机
     */
    public function modify_mobileOp() {
        $model_member = Model('member');
        $obj_validate = new Validate();
        $obj_validate->validateparam = array(
            array("input"=>$_POST["mobile"], "require"=>"true", 'validator'=>'mobile',"message"=>'请正确填写手机号'),
            array("input"=>$_POST["pin_code"], "require"=>"true", 'validator'=>'number',"message"=>'请正确填写手机验证码'),
        );
        $error = $obj_validate->validate();
        if ($error != ''){
        	output_error($error);
        }

        $condition = array();
        $condition['member_id'] = $this->member_info['member_id'];
        $condition['auth_code'] = intval($_POST['pin_code']);
        $member_common_info = $model_member->getMemberCommonInfo($condition,'send_acode_time');
        if (!$member_common_info) {
        	output_error('手机验证码错误，请重新输入');
        }
        if (TIMESTAMP - $member_common_info['send_acode_time'] > 1800) {
        	output_error('手机验证码已过期，请重新获取验证码');
        }
        $data = array();
        $data['auth_code'] = '';
        $data['send_acode_time'] = 0;
        $update = $model_member->editMemberCommon($data,array('member_id'=>$this->member_info['member_id']));
        if (!$update) {
        	output_error('系统发生错误，如有疑问请与管理员联系');
        }
        $update = $model_member->editMember(array('member_id'=>$this->member_info['member_id']),array('member_mobile_bind'=>1));
        if (!$update) {
            output_error('系统发生错误，如有疑问请与管理员联系');
        }
        output_data('手机号绑定成功');
    }
    /**
     * 绑定身份证号码
     * @return [type] [description]
     */
    public function bind_idcardOp(){
        $model_member = Model('member');
        $obj_validate = new Validate();
        $obj_validate->validateparam = array(
            array("input"=>$_POST["truename"], "require"=>"true", "message"=>'真实姓名不能为空'),
            array("input"=>$_POST["idcard"], "require"=>"true", "message"=>'身份证号不能为空'),
        );
        $error = $obj_validate->validate();
        if ($error != ''){
        	output_error($error);
        }
        if (!$this->checkIdCard($_POST['idcard'])) {
        	output_error("身份证号码不正确");
        }
        $data = array();
		$data['member_truename']	= $_POST['truename'];
		$data['member_idnum']	    = strtoupper($_POST['idcardencryptShowencryptShow']);
        $update = $model_member->editMember(array('member_id'=>$this->member_info['member_id']), $data);
        if ($update) {
        	output_data("认证成功");
        }else{
        	output_error("系统错误，认证失败");
        }
    }

    /**
     * 验证身份证号码
     * @param  [type] $idcard [description]
     * @return [type]         [description]
     */
	private function checkIdCard($idcard){       
 		$City = array(11=>"北京",12=>"天津",13=>"河北",14=>"山西",15=>"内蒙古",21=>"辽宁",22=>"吉林",23=>"黑龙江"
,31=>"上海",32=>"江苏",33=>"浙江",34=>"安徽",35=>"福建",36=>"江西",37=>"山东",41=>"河南",42=>"湖北",
43=>"湖南",44=>"广东",45=>"广西",46=>"海南",50=>"重庆",51=>"四川",52=>"贵州",53=>"云南",54=>"西藏",
61=>"陕西",62=>"甘肃",63=>"青海",64=>"宁夏",65=>"新疆",71=>"台湾",81=>"香港",82=>"澳门",91=>"国外");
        $iSum = 0;
        $idCardLength = strlen($idcard);
        //长度验证
        if(!preg_match('/^\d{17}(\d|x)$/i',$idcard) and !preg_match('/^\d{15}$/i',$idcard))
        {
            return false;
        }
        //地区验证
        if(!array_key_exists(intval(substr($idcard,0,2)),$City))
        {
           return false;
        }
        // 15位身份证验证生日，转换为18位
        if ($idCardLength == 15)
        {
            $sBirthday = '19'.substr($idcard,6,2).'-'.substr($idcard,8,2).'-'.substr($idcard,10,2);
            $d = new DateTime($sBirthday);
            $dd = $d->format('Y-m-d');
            if($sBirthday != $dd)
            {
                return false;
            }
            $idcard = substr($idcard,0,6)."19".substr($idcard,6,9);//15to18
            $Bit18 = getVerifyBit($idcard);//算出第18位校验码
            $idcard = $idcard.$Bit18;
        }
        // 判断是否大于2078年，小于1900年
        $year = substr($idcard,6,4);
        if ($year<1900 || $year>2078 )
        {
            return false;
        }

        //18位身份证处理
        $sBirthday = substr($idcard,6,4).'-'.substr($idcard,10,2).'-'.substr($idcard,12,2);
        $d = new DateTime($sBirthday);
        $dd = $d->format('Y-m-d');
        if($sBirthday != $dd)
         {
            return false;
         }
        //身份证编码规范验证
        $idcard_base = substr($idcard,0,17);
        if(strtoupper(substr($idcard,17,1)) != $this->getVerifyBit($idcard_base))
        {
           return false;
        }
        return true;
    }

    // 计算身份证校验码，根据国家标准GB 11643-1999
    private function getVerifyBit($idcard_base)
    {
        if(strlen($idcard_base) != 17)
        {
            return false;
        }
        //加权因子
        $factor = array(7, 9, 10, 5, 8, 4, 2, 1, 6, 3, 7, 9, 10, 5, 8, 4, 2);
        //校验码对应值
        $verify_number_list = array('1', '0', 'X', '9', '8', '7', '6', '5', '4', '3', '2');
        $checksum = 0;
        for ($i = 0; $i < strlen($idcard_base); $i++)
        {
            $checksum += substr($idcard_base, $i, 1) * $factor[$i];
        }
        $mod = $checksum % 11;
        $verify_number = $verify_number_list[$mod];
        return $verify_number;
    }
}