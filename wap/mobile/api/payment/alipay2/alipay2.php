<?php 
/**
 * 支付接口
 *
 */
defined('InShopNC') or exit('Access Invalid!');


class alipay2 {
    /**
     * 获取notify信息
     */
    public function getNotifyInfo($payment_config) {
		$alipay_config['partner'] = $payment_config['alipay_partner'];
		$alipay_config['private_key_path'] = 'key/rsa_private_key.pem';
		$alipay_config['ali_public_key_path'] = 'key/alipay_public_key.pem';
		$alipay_config['sign_type'] = strtoupper('RSA');
		$alipay_config['input_charset'] = strtolower('utf-8');
		$alipay_config['cacert'] = getcwd().'\\cacert.pem';
		$alipay_config['transport'] = 'http';
        
		require_once(BASE_PATH.DS.'api/payment/alipay2/lib/alipay_notify.class.php');
		
		//计算得出通知验证结果
		$alipayNotify = new AlipayNotify($alipay_config);
		$verify_result = $alipayNotify->verifyNotify();
		if($verify_result) {//验证成功
			if($_POST['trade_status'] == 'TRADE_FINISHED' || $_POST['trade_status'] == 'TRADE_SUCCESS') {
				return array(
					//商户订单号
					'out_trade_no' => $_POST['out_trade_no'],
					//支付宝交易号
					'trade_no' => $_POST['trade_no'],
				);
			}
		}
		return false;
    }
}
?>