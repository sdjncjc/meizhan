<?php
/**
 * 美站充值中心
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
class mz_member_predepositControl extends mobileMemberControl {

    public function __construct() {
        parent::__construct();
    }

    public function indexOp() {
        exit;
    }
    /**
     * 充值记录
     * @return [type] [description]
     */
    public function recharge_listOp(){
        $pagesize = 10;
        $predeposit = Model('predeposit');
        $condition = array();
        $condition['pdr_member_id'] = $this->member_info['member_id'];
        $recharge_list = $predeposit->getPdRechargeList($condition,'',"*",'pdr_add_time desc',($this->page -1)*$pagesize.','.$pagesize);
        if (!empty($recharge_list)) {
            foreach ($recharge_list as $key => $value) {
                $recharge_list[$key]['pdr_add_time'] = date("Y-m-d H:i",$value['pdr_add_time']);
            }
        }
        $count = $predeposit->getPdRechargeCount($condition);
        $data_info = array();
        $data_info['thispage'] = $this->page;
        $data_info['totalpage'] = ceil($count / $pagesize);
        output_data(array('data'=>$recharge_list,'data_info'=>$data_info));
    }
    /**
     * 删除充值记录
     * @return [type] [description]
     */
    public function recharge_delOp(){
        $predeposit = Model('predeposit');
        $condition = array();
        $condition['pdr_sn'] = $_GET['pdr_sn'];
        $condition['pdr_member_id'] = $this->member_info['member_id'];
        $result = $predeposit->delPdRecharge($condition);
        if ($result) {
            output_data("删除成功");
        }else{
            output_error("删除失败");
        }
    }
    public function applycash_listOp(){
        $pagesize = 10;
        $predeposit = Model('predeposit');
        $condition = array();
        $condition['pdc_member_id'] = $this->member_info['member_id'];
        $pdcash_list = $predeposit->getPdCashList($condition,'',"*",'pdc_add_time desc',($this->page -1)*$pagesize.','.$pagesize);
        if (!empty($pdcash_list)) {
            foreach ($pdcash_list as $key => $value) {
                $pdcash_list[$key]['pdc_add_time'] = date("Y-m-d H:i",$value['pdc_add_time']);
            }
        }
        $count = $predeposit->getPdCashCount($condition);
        $data_info = array();
        $data_info['thispage'] = $this->page;
        $data_info['totalpage'] = ceil($count / $pagesize);
        output_data(array('data'=>$pdcash_list,'data_info'=>$data_info));
    }
	/**
	 * 充值添加
	 */
	public function recharge_addOp(){
		$pdr_amount = abs(floatval($_GET['pdr_amount']));
		if ($pdr_amount <= 0) {
			output_error(Language::get('predeposit_recharge_add_pricemin_error'));
		}
        $model_pdr = Model('predeposit');
        $data = array();
        $data['pdr_sn'] = $pdr_sn = $model_pdr->makeSn();
        $data['pdr_member_id'] = $this->member_info['member_id'];
        $data['pdr_member_name'] = $this->member_info['member_name'];
        $data['pdr_amount'] = $pdr_amount;
        $data['pdr_add_time'] = TIMESTAMP;
        $insert = $model_pdr->addPdRecharge($data);
        if ($insert) {
        	output_data(array('data'=>array('pdr_sn'=>$pdr_sn,'pdr_amount'=>$pdr_amount)));
        }else{
        	output_error("系统错误");
        }
	}

    /**
     * 预存款充值
     */
    public function pd_orderOp(){
        $pdr_sn = $_GET['pdr_sn'];
        $payment_code = $_GET['payment_code'];
        $url = urlMember('predeposit');
    
        if(!preg_match('/^\d{18}$/',$pdr_sn)){
            output_error('参数错误');
        }
    
        $logic_payment = Logic('payment');
        $result = $logic_payment->getPaymentInfo($payment_code);
        if(!$result['state']) {
            output_error($result['msg']);
        }
        $payment_info = $result['data'];
    
        $result = $logic_payment->getPdOrderInfo($pdr_sn,$_SESSION['member_id']);
        if(!$result['state']) {
            output_error($result['msg']);
        }
        if ($result['data']['pdr_payment_state'] || empty($result['data']['api_pay_amount'])) {
            output_error('该充值单不需要支付');
        }
        // 转到第三方API支付
        $this->_api_pay($result['data'], $payment_info);
    }

    /**
     * 第三方在线支付接口
     *
     */
    private function _api_pay($order_pay_info, $payment_info) {
        $param = $payment_info['payment_config'];
        // wxpay_jsapi
        if ($payment_info['payment_code'] == 'wxpay_jsapi') {
            $param['orderSn'] = $order_pay_info['pay_sn'];
            $param['orderFee'] = (int) (100 * $order_pay_info['api_pay_amount']);
            $param['orderInfo'] = C('site_name') . $order_pay_info['subject'];
            $param['orderAttach'] = 'p';
		    if($_GET['from'] == 'mz'){
		            $param['finishedUrl'] = 'http://mz.qinqin.net/trade/payment_pd_result.html?_=2&attach=_attach_';
		            $param['undoneUrl'] = 'http://mz.qinqin.net/trade/payment_pd_result_failed.html?_=2&attach=_attach_';
		    }
            $api = new wxpay_jsapi();
            $api->setConfigs($param);
            try {
                echo $api->paymentHtml($this);
            } catch (Exception $ex) {
                if (C('debug')) {
                    header('Content-type: text/plain; charset=utf-8');
                    echo $ex, PHP_EOL;
                } else {
                    Tpl::output('msg', $ex->getMessage());
                    Tpl::showpage('payment_result');
                }
            }
            exit;
        }

        $param['order_sn'] = $order_pay_info['pay_sn'];
        $param['order_amount'] = $order_pay_info['api_pay_amount'];
        $param['order_type'] = 'p';
        $payment_api = new $payment_info['payment_code']();
        $return = $payment_api->submit($param);
        echo $return;
        exit;
    }
}
