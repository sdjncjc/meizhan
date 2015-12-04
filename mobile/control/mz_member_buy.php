<?php
/**
 * 购买
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

class mz_member_buyControl extends mobileMemberControl {

    public function __construct() {
        parent::__construct();
    }

    /**
     * 购物车、直接购买第一步:选择收获地址和配置方式
     */
    public function buy_step1Op() {
        $cart_id = explode(',', $_POST['cart_id']);

        $logic_buy = logic('buy');

        //得到会员等级
        $model_member = Model('member');
        $member_info = $model_member->getMemberInfoByID($this->member_info['member_id']);

        if ($member_info){
            $member_gradeinfo = $model_member->getOneMemberGrade(intval($member_info['member_exppoints']));
            $member_discount = $member_gradeinfo['orderdiscount'];
            $member_level = $member_gradeinfo['level'];
        } else {
            $member_discount = $member_level = 0;
        }

        //得到购买数据
        $result = $logic_buy->buyStep1($cart_id, $_POST['ifcart'], $this->member_info['member_id'], $this->member_info['store_id'],null,$member_discount,$member_level);
        if(!$result['state']) {
            output_error($result['msg']);
        } else {
            $result = $result['data'];
        }
		
        $buy_list = array();
		//运费
		$freight_list = $no_send_tpl_ids = array();
		if($result['address_info']){
			if (empty($result['address_info']['city_id'])) {
				$result['address_info']['city_id'] = $result['address_info']['area_id'];
			}
			$data = $logic_buy->changeAddr($result['freight_list'], $result['address_info']['city_id'], $result['address_info']['area_id'], $this->member_info['member_id']);
			if(!empty($data) && $data['state'] == 'success' ) {
				$freight_list = $data['content'];
				$no_send_tpl_ids = $data['no_send_tpl_ids'];
				$buy_list['offpay_hash'] = $data['offpay_hash'];
				$buy_list['offpay_hash_batch'] = $data['offpay_hash_batch'];
			}
		}

        //整理数据
        $buy_list['is_fcode'] = 0;
        $buy_list['no_send_tpl'] = 0;
		$total_price = 0;
        $store_cart_list = array();
        foreach ($result['store_cart_list'] as $key => $value) {
            $store_cart_list[$key]['goods_list'] = $value;
            $store_cart_list[$key]['store_goods_total'] = $result['store_goods_total'][$key];
            $store_cart_list[$key]['store_freight'] = $freight_list[$key];
            if(!empty($result['store_premiums_list'][$key])) {
                $result['store_premiums_list'][$key][0]['premiums'] = true;
                $result['store_premiums_list'][$key][0]['goods_total'] = 0.00;
                $store_cart_list[$key]['goods_list'][] = $result['store_premiums_list'][$key][0];
            }
			foreach($store_cart_list[$key]['goods_list'] as $k=>$v){
				if ($v['is_fcode'] == '1') {
					$buy_list['is_fcode'] = 1;
				}
				if(in_array($v['transport_id'],$no_send_tpl_ids)){
					$store_cart_list[$key]['goods_list'][$k]['tpl'] = '（无货）';
					$buy_list['no_send_tpl'] = 1;
				}
			}
            $store_cart_list[$key]['store_mansong_rule_list'] = $result['store_mansong_rule_list'][$key];
			$store_cart_list[$key]['sp_total'] = $store_cart_list[$key]['store_goods_total'] - $store_cart_list[$key]['store_mansong_rule_list']['discount'] + $store_cart_list[$key]['store_freight'];
			$total_price += $store_cart_list[$key]['sp_total'];
            $store_cart_list[$key]['store_voucher_list'] = $result['store_voucher_list'][$key];
			sort($store_cart_list[$key]['store_voucher_list']);
//            if(!empty($result['cancel_calc_sid_list'][$key])) {
//                $store_cart_list[$key]['freight'] = '0';
//                $store_cart_list[$key]['freight_message'] = $result['cancel_calc_sid_list'][$key]['desc'];
//            } else {
//                $store_cart_list[$key]['freight'] = '1';
//            }
            $store_cart_list[$key]['store_zk'] = $result['zk_list'][$key];
            $store_cart_list[$key]['store_name'] = $value[0]['store_name'];
        }

        $buy_list['total_price'] = $total_price;
        $buy_list['store_cart_list'] = $store_cart_list;
        $buy_list['address_info'] = $result['address_info'];
        $buy_list['vat_hash'] = $result['vat_hash'];
        $buy_list['inv_info'] = $result['inv_info'];
        $buy_list['available_predeposit'] = $result['available_predeposit'];
        $buy_list['available_rc_balance'] = $result['available_rc_balance'];
        if (is_array($result['rpt_list']) && !empty($result['rpt_list'])) {
            foreach ($result['rpt_list'] as $k => $v) {
                unset($result['rpt_list'][$k]['rpacket_id']);
                unset($result['rpt_list'][$k]['rpacket_end_date']);
                unset($result['rpt_list'][$k]['rpacket_owner_id']);
                unset($result['rpt_list'][$k]['rpacket_code']);
            }
        }
        $buy_list['rpt_list'] = $result['rpt_list'] ? $result['rpt_list'] : array();
		
		//判断是否需要实名认证
		$verified = 0;
		if($result['store_cart_list']){
			$ids = array();
			foreach($result['store_cart_list'] as $v){
				foreach($v as $vv){
					$ids[] = $vv['goods_id'];
				}
			}
			$count = Model('goods')->getGoodsCount(array('goods_type'=>'1','goods_id'=>array('in',$ids)));
			if($count > 0){
				$verified = 1;
			}
		}
        $buy_list['verified'] = $verified;

        output_data($buy_list);
    }

    /**
     * 购物车、直接购买第二步:保存订单入库，产生订单号，开始选择支付方式
     *
     */
    public function buy_step2Op() {
        $param = array();
        $param['ifcart'] = $_POST['ifcart'];
        $param['cart_id'] = explode(',', $_POST['cart_id']);
        $param['address_id'] = $_POST['address_id'];
        $param['vat_hash'] = $_POST['vat_hash'];
        $param['offpay_hash'] = $_POST['offpay_hash'];
        $param['offpay_hash_batch'] = $_POST['offpay_hash_batch'];
        $param['pay_name'] = $_POST['pay_name'];
        $param['invoice_id'] = $_POST['invoice_id'];
        $param['rpt'] = $_POST['rpt'];

        //处理代金券
        $voucher = array();
        $post_voucher = explode(',', $_POST['voucher']);
        if(!empty($post_voucher)) {
            foreach ($post_voucher as $value) {
                list($voucher_t_id, $store_id, $voucher_price) = explode('|', $value);
                $voucher[$store_id] = $value;
            }
        }
        $param['voucher'] = $voucher;
        //处理留言
        $pay_message = array();
        $post_pay_message = explode('%fenge%', $_POST['pay_message']);
        if(!empty($post_pay_message)) {
            foreach ($post_pay_message as $value) {
                list($store_id, $content) = explode('%sid%', $value);
                $pay_message[$store_id] = $content;
            }
        }
        $param['pay_message'] = $pay_message;
        $param['pd_pay'] = $_POST['pd_pay'];
        $param['rcb_pay'] = $_POST['rcb_pay'];
        $param['password'] = $_POST['password'];
        $param['fcode'] = $_POST['fcode'];
        $param['order_from'] = 2;
        $logic_buy = logic('buy');

        //得到会员等级
        $model_member = Model('member');
        $member_info = $model_member->getMemberInfoByID($this->member_info['member_id']);
        if ($member_info){
            $member_gradeinfo = $model_member->getOneMemberGrade(intval($member_info['member_exppoints']));
            $member_discount = $member_gradeinfo['orderdiscount'];
            $member_level = $member_gradeinfo['level'];
        } else {
            $member_discount = $member_level = 0;
        }
        $result = $logic_buy->buyStep2($param, $this->member_info['member_id'], $this->member_info['member_name'], $this->member_info['member_email'],$member_discount,$member_level);
        if(!$result['state']) {
            output_error($result['msg']);
        }
		
		//处理推广信息
		$pm = $_POST['pm'];
		if($pm){//验证pm
			$pm_arr = explode('.',$pm);
			if(!Model('mz_member')->where(array('member_id'=>$pm_arr[0]))->count()){
				unset($pm);
			}elseif($pm_arr[1] && !Model('mz_team')->where(array('team_id'=>$pm_arr[1],'team_status'=>1))->count()){
				unset($pm);
			}
		}
		if(!$pm){
			$member = Model('mz_member')->field('team_id')->where(array('member_id'=>$this->member_info['member_id']))->find();
			if($member['team_id'] && !Model('mz_team')->where(array('team_id'=>$member['team_id'],'team_status'=>1))->count())unset($member['team_id']);
			$pm = $member['team_id'] ? '0.'.$member['team_id'] : ''; 
		}
		Model('order')->editOrder(array('pm' => $pm),array('order_id' => array('in',array_keys($result['data']['order_list']))));
        output_data(array('pay_sn' => $result['data']['pay_sn']));
    }

    /**
     * 验证密码
     */
    public function check_passwordOp() {
        if(empty($_POST['password'])) {
            output_error('参数错误');
        }

        $model_member = Model('member');

        $member_info = $model_member->getMemberInfoByID($this->member_info['member_id']);
        if($member_info['member_paypwd'] == md5($_POST['password'])) {
            output_data('1');
        } else {
            output_error('密码错误');
        }
    }
}
