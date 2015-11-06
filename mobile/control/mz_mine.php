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
class mz_mineControl extends mobileMemberControl {

    public function __construct() {
        parent::__construct();
    }

    public function indexOp() {
        exit;
    }

    /**
     * 获取用户信息
     */
    public function getUserInfoOp() {
        $member_info['member_avatar'] = getMemberAvatar($this->member_info['member_avatar']);
        $member_info['member_name'] = $this->member_info['member_name'];
        $member_info['member_points'] = $this->member_info['member_points'];
        $member_info['available_rc_balance'] = $this->member_info['available_rc_balance'];
        $member_info['available_predeposit'] = $this->member_info['available_predeposit'];
        output_data($member_info);
    }
    /**
     * 获取订单概况
     * @return [type] [description]
     */
    public function getSimpleOrderInfoOp(){
        $simpleOrderInfo = array();
        $orders = Model('orders')->field('order_id,evaluation_again_state,order_state')->where(array('buyer_id'=>$this->member_info['member_id'],'delete_state'=>0))->select();
        $simpleOrderInfo['unpay'] = $simpleOrderInfo['unpost'] = $simpleOrderInfo['unget'] = $simpleOrderInfo['unjudge'] = 0;
        if (!empty($orders)) {
            foreach ($orders as $key => $value) {
                if ($value['order_state'] == 10) {
                    $simpleOrderInfo['unpay']++;
                }elseif ($value['order_state'] == 20) {
                    $simpleOrderInfo['unpost']++;
                }elseif ($value['order_state'] == 30) {
                    $simpleOrderInfo['unget']++;
                }elseif ($value['order_state'] == 40 && $value['evaluation_again_state'] == 0) {
                    $simpleOrderInfo['unget']++;
                }
            }
        }
        output_data($simpleOrderInfo);

    }
    /**
     * 获取订单列表
     * @return [type] [description]
     */
    public function getOrdersOp(){
        $size = 10;     
        $page = intval($_GET['page']);
        $page = $page <= 0 ? 1 : $page;
        $orders = Model('order')->getOrderList(array('buyer_id'=>$this->member_info['member_id'],'delete_state'=>0),'',"*",'order_id desc', (($page-1)*$size).','.$size, array('order_goods'));
        if (!empty($orders)) {
            foreach ($orders as $key => $value) {
                $orders[$key]['add_time'] = date("Y-m-d H:i:s",$value['add_time']);
                if (!empty($value['extend_order_goods'])) {
                    foreach ($value['extend_order_goods'] as $k => $v) {
                        $orders[$key]['extend_order_goods'][$k]['img_url'] = thumb($v, 360);
                    }
                }
            }
        }
        output_data($orders);
    }
    /**
     * 订单放入回收站
     */
    public function recycleOrderOp(){
        $order_id = $_GET['order_id'];
        $order = Model("orders");
        $order_info = $order->find($order_id);
        $this->debuger($order_info);

    }
    /**
     * 调试
     * @param  [type]  $arr  [description]
     * @param  boolean $stop [description]
     * @return [type]        [description]
     */
    private function debuger($arr,$stop = true){
        echo "<pre>";
        print_r($arr);
        if ($stop) {
            die();
        }
    }
}