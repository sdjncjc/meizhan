<?php
/**
 * 我的购物车
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

class mz_member_cartControl extends mobileMemberControl {

    public function __construct() {
        parent::__construct();
    }

    /**
     * 购物车列表
     */
    public function cart_listOp() {
        $model_cart = Model('cart');
        $logic_buy_1 = logic('buy_1');
			
		$del_ids = $_POST['del_ids'];
        //购物车列表
        $cart_list  = $model_cart->listCart('db',array('buyer_id'=>$this->member_info['member_id']));

        // 购物车列表 [得到最新商品属性及促销信息]
        $cart_list = $logic_buy_1->getGoodsCartList($cart_list, $jjgObj);

        //购物车商品以店铺ID分组显示,并计算商品小计,店铺小计与总价由JS计算得出
        $store_cart_list = array();
        $total_price = 0;
        $total_save = 0;
        foreach ($cart_list as $cart) {
            $cart['goods_image_url'] = cthumb($cart['goods_image'], $cart['store_id']);
            $cart['goods_total'] = $cart['goods_price'] * $cart['goods_num'];
			if(in_array($cart['cart_id'], $del_ids)){
				$cart['is_selected'] = 0;
			}else{
				$cart['is_selected'] = 1;
				$total_price += $cart['goods_total'];
				$total_save += ($cart['goods_marketprice']-$cart['goods_price']) * $cart['goods_num'];
				$store_cart_list[$cart['store_id']]['cart_count'] += $cart['goods_num'];
				$store_cart_list[$cart['store_id']]['cart_price'] += $cart['goods_total'];
			}
            $store_cart_list[$cart['store_id']]['store_id'] = $cart['store_id'];
            $store_cart_list[$cart['store_id']]['store_name'] = $cart['store_name'];
            $store_cart_list[$cart['store_id']]['cart_list'][] = $cart;
        }

        // 店铺优惠券
//        $condition = array();
//        $condition['voucher_t_gettype'] = 3;
//        $condition['voucher_t_state'] = 1;
//        $condition['voucher_t_end_date'] = array('gt', time());
//        $condition['voucher_t_mgradelimit'] = array('elt', $this->member_info['level']);
//        $condition['voucher_t_store_id'] = array('in', array_keys($store_cart_list));
//        $voucher_template = Model('voucher')->getVoucherTemplateList($condition);
//        $voucher_template = array_under_reset($voucher_template, 'voucher_t_store_id', 2);
//        Tpl::output('voucher_template', $voucher_template);

        //取得店铺级活动 - 可用的满即送活动
        $mansong_rule_list = $logic_buy_1->getMansongRuleList(array_keys($store_cart_list));
        //取得哪些店铺有满免运费活动
        $free_freight_list = $logic_buy_1->getFreeFreightActiveList(array_keys($store_cart_list));
		$is_selected = 1;
        foreach ($store_cart_list as $k=>$v) {
            $store_cart_list[$k]['mansong'] = $mansong_rule_list[$k]['desc'];
            $store_cart_list[$k]['free_freight'] = $free_freight_list[$k];
            $store_cart_list[$k]['is_selected'] = 1;
			foreach($v['cart_list'] as $kk=>$vv){
				if($vv['is_selected'] == 0){
					$store_cart_list[$k]['is_selected'] = 0;
					$is_selected = 0;
				}
			}
        }

        output_data(array('store_cart_list' => $store_cart_list, 'total_price' => $total_price, 'total_save' => $total_save, 'is_selected' => $is_selected));
    }

    /**
     * 购物车添加
     */
    public function cart_addOp() {
        $goods_id = intval($_POST['goods_id']);
        $quantity = intval($_POST['quantity']);
        if($goods_id <= 0 || $quantity <= 0) {
            output_error('参数错误');
        }

        $model_goods = Model('goods');
        $model_cart = Model('cart');
        $logic_buy_1 = Logic('buy_1');

        $goods_info = $model_goods->getGoodsOnlineInfoAndPromotionById($goods_id);

        //验证是否可以购买
        if(empty($goods_info)) {
            output_error('商品已下架或不存在');
        }

        if ($goods_info['store_id'] == $this->member_info['store_id']) {
            output_error('不能购买自己发布的商品');
        }
		
		//判断购物车中是否已有该商品
        $model_cart = Model('cart');
        $cart_info = $model_cart->getCartInfo(array('goods_id'=>$goods_info['goods_id'], 'buyer_id' => $this->member_info['member_id']));
		if($cart_info){
			$quantity = $cart_info['goods_num']+1;
	
			//检查库存是否充足
			if(!$this->_check_goods_storage($cart_info, $quantity, $this->member_info['member_id'])) {
				output_error('超出限购数或库存不足');
			}
	
			$data = array();
			$data['goods_num'] = $quantity;
			$update = $model_cart->editCart($data, array('cart_id'=>$cart_info['cart_id']));
			if ($update) {
				output_data('添加购物车成功');
			} else {
				output_error('添加购物车失败');
			}
		}

        //团购
        $logic_buy_1->getGroupbuyInfo($goods_info);

        //限时折扣
        $logic_buy_1->getXianshiInfo($goods_info,$quantity);
		
        if(intval($goods_info['goods_storage']) < 1 || intval($goods_info['goods_storage']) < $quantity) {
            output_error('库存不足');
        }

        $param = array();
        $param['buyer_id']  = $this->member_info['member_id'];
        $param['store_id']  = $goods_info['store_id'];
        $param['goods_id']  = $goods_info['goods_id'];
        $param['goods_name'] = $goods_info['goods_name'];
        $param['goods_price'] = $goods_info['goods_price'];
        $param['goods_image'] = $goods_info['goods_image'];
        $param['store_name'] = $goods_info['store_name'];

        $result = $model_cart->addCart($param, 'db', $quantity);
        if($result) {
            output_data('添加购物车成功');
        } else {
            output_error('添加购物车失败');
        }
    }

    /**
     * 购物车删除
     */
    public function cart_delOp() {
        $cart_id = intval($_POST['cart_id']);

        $model_cart = Model('cart');

        if($cart_id > 0) {
            $condition = array();
            $condition['buyer_id'] = $this->member_info['member_id'];
            $condition['cart_id'] = $cart_id;

            $model_cart->delCart('db', $condition);
        }

        output_data('1');
    }

    /**
     * 更新购物车购买数量
     */
    public function cart_edit_quantityOp() {
        $cart_id = intval(abs($_POST['cart_id']));
        $quantity = intval(abs($_POST['quantity']));
        if(empty($cart_id) || empty($quantity)) {
            output_error('参数错误');
        }

        $model_cart = Model('cart');

        $cart_info = $model_cart->getCartInfo(array('cart_id'=>$cart_id, 'buyer_id' => $this->member_info['member_id']));

        //检查是否为本人购物车
        if($cart_info['buyer_id'] != $this->member_info['member_id']) {
            output_error('参数错误');
        }

        //检查库存是否充足
        if(!$this->_check_goods_storage($cart_info, $quantity, $this->member_info['member_id'])) {
            output_error('超出限购数或库存不足');
        }

        $data = array();
        $data['goods_num'] = $quantity;
        $update = $model_cart->editCart($data, array('cart_id'=>$cart_id));
        if ($update) {
            output_data('修改成功');
        } else {
            output_error('修改失败');
        }
    }

    /**
     * 检查库存是否充足
     */
    private function _check_goods_storage(& $cart_info, $quantity, $member_id) {
        $model_goods= Model('goods');
        $model_bl = Model('p_bundling');
        $logic_buy_1 = Logic('buy_1');

        if ($cart_info['bl_id'] == '0') {
            //普通商品
            $goods_info = $model_goods->getGoodsOnlineInfoAndPromotionById($cart_info['goods_id']);

            //团购
            $logic_buy_1->getGroupbuyInfo($goods_info);
            if ($goods_info['ifgroupbuy']) {
                if ($goods_info['upper_limit'] && $quantity > $goods_info['upper_limit']) {
                    return false;
                }
            }

            //限时折扣
            $logic_buy_1->getXianshiInfo($goods_info,$quantity);

            if(intval($goods_info['goods_storage']) < $quantity) {
                return false;
            }
            $goods_info['cart_id'] = $cart_info['cart_id'];
            $cart_info = $goods_info;
        } else {
            //优惠套装商品
            $bl_goods_list = $model_bl->getBundlingGoodsList(array('bl_id' => $cart_info['bl_id']));
            $goods_id_array = array();
            foreach ($bl_goods_list as $goods) {
                $goods_id_array[] = $goods['goods_id'];
            }
            $bl_goods_list = $model_goods->getGoodsOnlineListAndPromotionByIdArray($goods_id_array);

            //如果有商品库存不足，更新购买数量到目前最大库存
            foreach ($bl_goods_list as $goods_info) {
                if (intval($goods_info['goods_storage']) < $quantity) {
                    return false;
                }
            }
        }
        return true;
    }

}
