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
        $member_info = $this->member_info;
        $member_info['member_avatar'] = getMemberAvatar($this->member_info['member_avatar']);
        // $member_info['member_name'] = $this->member_info['member_name'];
        // $member_info['member_points'] = $this->member_info['member_points'];
        // $member_info['available_rc_balance'] = $this->member_info['available_rc_balance'];
        // $member_info['available_predeposit'] = $this->member_info['available_predeposit'];
        output_data(array('data'=>$member_info));
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
        output_data(array('data'=>$simpleOrderInfo));        
    }
    /**
     * 获取订单列表
     * @return [type] [description]
     */
    public function getOrdersOp(){
        $status = (isset($_GET['type'])&&!empty($_GET['type']))?$_GET['type']:'all';
        $size = 10;     
        $page = intval($_GET['page']);
        $page = $page <= 0 ? 1 : $page;

        //查询条件
        $condition = array();
        if ($status == 'sold') {
            // 获取售后信息
            $refund_order_ids = array();
            $model_refund = Model('refund_return');
            $refundOrderList = $model_refund->getRefundReturnList(array('buyer_id'=>$this->member_info['member_id']),'','order_id');
            if (!empty($refundOrderList)) {
                foreach ($refundOrderList as $key => $value) {
                    $refund_order_ids[] = $value['order_id'];
                }
            }
            $condition['order_id'] = array('in',$refund_order_ids);
        }else{
            if (in_array($status, array(10,20,30,40))) {
                if ($status == 40) {
                    $condition['evaluation_state'] = 0;
                }
                $condition['order_state'] = $status;
            }
        }
        $condition['delete_state'] = 0;
        $condition['buyer_id'] = $this->member_info['member_id'];
        $order_count = Model('order')->getOrderCount($condition);
        $data_info['thispage'] = $page;
        $data_info['totalpage'] = ceil($order_count / $size);

        $orders = Model('order')->getNormalOrderList($condition,'',"*",'order_id desc',(($page-1)*$size).','.$size, array('order_goods'));
        if (!empty($orders)) {
            foreach ($orders as $key => $value) {
                $orders[$key]['add_time'] = date("Y-m-d H:i:s",$value['add_time']);
                if (!empty($value['extend_order_goods'])) {
                    foreach ($value['extend_order_goods'] as $k => $v) {
                        $orders[$key]['extend_order_goods'][$k]['img_url'] = thumb($v, 360);
                    }
                }
                $orders['a' .$key] = $orders[$key];
                unset($orders[$key]);

            }
        }
        output_data(array('data'=>$orders,'data_info'=>$data_info));
    }
    /**
     * 取消订单
     */
    public function cancelOrderOp(){
        $order_id = intval($_GET['order_id']);
        $reason = strip_tags($_GET['reason']);
        $condition = array();
        $condition['order_id'] = $order_id;
        $order_model = Model('order');
        $order_logic = Logic('order');
        $order_info = $order_model->getOrderInfo($condition);
        if (empty($order_info)) {
            output_error('订单不存在！');
        }
        if (!$order_model->getOrderOperateState('buyer_cancel',$order_info)) {
            output_error("无权操作");
        }
        if (TIMESTAMP - 86400 < $order_info['api_pay_time']) {
            $_hour = ceil(($order_info['api_pay_time']+86400-TIMESTAMP)/3600);
            output_error('该订单曾尝试使用第三方支付平台支付，须在'.$_hour.'小时以后才可取消');
        }

        if ($order_info['order_type'] != 2) {
            $cancel_condition = array();
            if ($order_info['payment_code'] != 'offline') {
                $cancel_condition['order_state'] = ORDER_STATE_NEW;
            }
            $result = $order_logic->changeOrderStateCancel($order_info,'buyer', $_SESSION['member_name'], $reason,true,$cancel_condition);
        } else {
            //取消预定订单
            $result = Logic('order_book')->changeOrderStateCancel($order_info,'buyer', $_SESSION['member_name'], $reason);
        }
        if ($result) {
           output_data(array());
        }else{
            output_error("系统错误");
        }
    }
    /**
     * 订单放入回收站
     */
    public function recycleOrderOp(){
        $order_id = $_GET['order_id'];
        $condition = array();
        $condition['order_id'] = $order_id;
        $order_model = Model('order');
        $order_logic = Logic('order');
        $order_info = $order_model->getOrderInfo($condition);
        if (empty($order_info)) {
            output_error('订单不存在！');
        }
        if (!$order_model->getOrderOperateState('delete',$order_info)) {
            output_error("无权操作");
        }
        $result = $order_logic->changeOrderStateRecycle($order_info,'buyer',"delete");
        if ($result) {
           output_data(array());
        }else{
            output_error("系统错误");
        }

    }
    /**
     * 获取订单详情
     */
    public function getOrderInfoOp(){
        $order_id = intval($_GET['order_id']);
        // 查询条件
        $condition = array();
        $condition['order_id'] = $order_id;
        $condition['buyer_id'] = $this->member_info['member_id'];

        $order_model = Model('order');
        $order_info = $order_model->getOrderInfo($condition,array('order_common','order_goods','member'));
        if (empty($order_info)) {
            output_error("订单不存在！");
        }
        if (!empty($order_info['extend_order_common']['invoice_info'])) {
            $order_info['extend_order_common']['has_invoice'] = true;
        }else{
            $order_info['extend_order_common']['has_invoice'] = false;
        }
        if (!empty($order_info['extend_order_goods'])) {
            foreach ($order_info['extend_order_goods'] as $k => $v) {
                $order_info['extend_order_goods'][$k]['img_url'] = thumb($v, 360);
            }
        }
        // 获取售后信息
        $model_refund = Model('refund_return');
        $order_info['aftersale'] = $model_refund->getRefundState($order_info);
        // $this->debuger($order_info);
        output_data(array('data'=>$order_info));
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