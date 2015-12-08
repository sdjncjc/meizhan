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
class mz_member_orderControl extends mobileMemberControl {

    public function __construct() {
        parent::__construct();
    }

    public function indexOp() {
        exit;
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
        if (in_array($status, array(10,20,30,40))) {
            if ($status == 40) {
                $condition['evaluation_state'] = 0;
            }
            $condition['order_state'] = $status;
        }
        $condition['delete_state'] = 0;
        $condition['buyer_id'] = $this->member_info['member_id'];
        $order_count = Model('order')->getOrderCount($condition);
        $data_info['thispage'] = $page;
        $data_info['totalpage'] = ceil($order_count / $size);
        $model_order = Model('order');
        $order_list_array = $model_order->getNormalOrderList($condition,'',"*",'order_id desc',(($page-1)*$size).','.$size, array('order_goods'));

        $order_group_list = array();
        $order_pay_sn_array = array();
        foreach ($order_list_array as $value) {
            //显示放入回收站
            $value['if_delete'] = $model_order->getOrderOperateState('delete',$value);
            //显示取消订单
            $value['if_cancel'] = $model_order->getOrderOperateState('buyer_cancel',$value);
            //显示收货
            $value['if_receive'] = $model_order->getOrderOperateState('receive',$value);
            //显示评价
            $value['if_evaluation'] = $model_order->getOrderOperateState('evaluation',$value);
            // 商品数量
            $value['goods_num'] = 0;
            //商品图
            foreach ($value['extend_order_goods'] as $k => $goods_info) {
                $value['extend_order_goods'][$k]['goods_image_url'] = cthumb($goods_info['goods_image'], 240, $value['store_id']);
                $value['goods_num'] += $goods_info['goods_num'];
            }

            $order_group_list[$value['pay_sn']]['order_list'][] = $value;

            //如果有在线支付且未付款的订单则显示合并付款链接
            if ($value['order_state'] == ORDER_STATE_NEW) {
                $order_group_list[$value['pay_sn']]['pay_amount'] += $value['order_amount'] - $value['rcb_amount'] - $value['pd_amount'];
            }
            $order_group_list[$value['pay_sn']]['add_time'] = date("Y-m-d H:i:s",$value['add_time']);

            //记录一下pay_sn，后面需要查询支付单表
            $order_pay_sn_array[] = $value['pay_sn'];
        }

        $new_order_group_list = array();
        foreach ($order_group_list as $key => $value) {
            $value['pay_sn'] = strval($key);
            $new_order_group_list[] = $value;
        }

        output_data(array('data'=>$new_order_group_list,'data_info'=>$data_info));
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
           output_data("操作成功");
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
           output_data("操作成功");
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
     * 确认收货
     * @return [type] [description]
     */
    public function orderReceiveOp(){
        $model_order = Model('order');
        $logic_order = Logic('order');
        // 查询条件
        $condition = array();
        $condition['order_id'] = $order_id;
        $condition['buyer_id'] = $this->member_info['member_id'];
        $order_info = $order_model->getOrderInfo($condition);
        $if_allow = $model_order->getOrderOperateState('receive',$order_info);
        if (!$if_allow) {
            output_error("无权操作");
        }
        if ($logic_order->changeOrderStateReceive($order_info,"buyer",$this->member_info['member_name'],'签收了货物')) {
            output_data("确认收货成功");
        }else{
            output_error("系统错误");
        }
    }
    /**
     * 获取售后列表
     * @return [type] [description]
     */
    public function getSaleSupportOp(){
        $size = 10;     
        $model_refund = Model('refund_return');
        $condition = array();
        $condition['member_id'] = $this->member_info['member_id'];
        $refundReturnList = $model_refund->getRefundReturnList($condition,'','*',(($this->page-1)*$size).','.$size);
        if (!empty($refundReturnList)) {
            foreach ($refundReturnList as $key => $value) {
                $refundReturnList[$key]['goods_image'] = cthumb($value['goods_image'], 240);
            }
        }
        $sum = $model_refund->getRefundReturnCount($condition);
        $data_info['thispage'] = $this->page;
        $data_info['totalpage'] = ceil($sum / $size);

        output_data(array('data'=>$refundReturnList,'data_info'=>$data_info));
    }
    /**
     * 售后详情
     * @return [type] [description]
     */
    public function getSaleSupportInfoOp(){
        $order_id = intval($_GET['order_id']);
        $model_refund = Model('refund_return');
        $condition = array();
        $condition['order_id'] = $order_id;
        $condition['buyer_id'] = $this->member_info['member_id'];
        $refundreturn_info = $model_refund->getRefundReturnInfo($condition);
        if (empty($refundreturn_info)) {
            output_error("参数错误");
        }
        if (!empty($refundreturn_info)) {
            $refundreturn_info['goods_image'] =  cthumb($refundreturn_info['goods_image'], 240);
            $refundreturn_info['add_time'] =  date("Y-m-d H:i:s",$refundreturn_info['add_time']);
        }
        output_data(array('data'=>$refundreturn_info));
    }
    /**
     * 获取售后原因
     * @return [type] [description]
     */
    public function getReasonInfoOp(){
        $order_id = intval($_GET['order_id']);
        $goods_id = intval($_GET['goods_id']);
        $model_order = Model("order");
        $model_refund = Model('refund_return');
        $reason_list = $model_refund->getReasonList();
        // 查询条件
        $condition = array();
        $condition['order_id'] = $order_id;
        $condition['buyer_id'] = $this->member_info['member_id'];

        $order = $model_refund->getRightOrderList($condition, $goods_id);
        $is_return = $model_refund->getRefundState($order);
        if ($is_return == 0) {
            output_error("当前订单无法申请售后");
        }
        if ($order['order_amount'] < ($order['goods_list'][0]['goods_pay_price'] + $order['refund_amount'])) {
            $order['goods_list'][0]['goods_pay_price'] = $order['order_amount'] - $order['refund_amount'];
        }
        if ($goods_id == 0) {
            //禁止退款金额
            $lock_amount = Logic('order_book')->getDepositAmount($order);
            $order['allow_refund_amount'] = $order['order_amount'] - $lock_amount;
            unset($order['goods_list']);
            $order['goods_list']['rec_id'] = 0;
        }else{
            $temp =  $order['goods_list'][0];
            unset($order['goods_list']);
            $order['goods_list'] = $temp;
        }
        output_data(array('data'=>array('reason'=>$reason_list,'order_info'=>$order)));
    }
    /**
     * 申请售后
     */
    public function addRefundOp(){
        $goods = array();
        $order_id = intval($_GET['order_id']);
        $goods_id = intval($_GET['goods_id']);
        $refund_amount = floatval($_POST['refund_amount']);//退款金额
        $reason_id = intval($_POST['reason_id']);//退货退款原因
        $buyer_message = trim($_POST['buyer_message']);//退货退款原因
        $model_order = Model("order");
        $model_refund = Model('refund_return');
        $condition = array();
        $reason_list = $model_refund->getReasonList($condition);//退款退货原因

        // 退货原因
        $refund_array = array();
        $reason_array = array();
        $refund_array['reason_info'] = '';
        $refund_array['reason_id'] = $reason_id;
        $reason_array['reason_info'] = '其他';
        if (!empty($reason_list[$reason_id])) {
            $reason_array = $reason_list[$reason_id];
        }
        $refund_array['reason_info'] = $reason_array['reason_info'];

        $order = $model_refund->getRightOrderList($condition, $goods_id);

        // 退款金额
        $order_amount = $order['order_amount'];//订单金额
        if ($goods_id > 0 ) {
            $order_refund_amount = $order['refund_amount'];//订单退款金额
            $goods_list = $order['goods_list'];
            $goods = $goods_list[0];
            $goods_pay_price = $goods['goods_pay_price'];//商品实际成交价

            if ($order_amount < ($goods_pay_price + $order_refund_amount)) {
                $goods_pay_price = $order_amount - $order_refund_amount;
            }
            if (($refund_amount < 0) || ($refund_amount > $goods_pay_price)) {
                $refund_amount = $goods_pay_price;
            }
            $refund_array['goods_num'] = 1;
        }elseif ($goods_id == 0 ) {
            if (($refund_amount < 0) || ($refund_amount > $order_amount)) {
                $refund_amount = $order_amount;
            }
            $refund_array['goods_num'] = 0;
            $refund_array['goods_id'] = '0';
            $refund_array['order_goods_id'] = '0';
            $refund_array['reason_id'] = '0';
            $refund_array['reason_info'] = '取消订单，全部退款';
            $refund_array['goods_name'] = '订单商品全部退款';
        }else{
            output_error("参数错误");
        }
        $refund_array['refund_amount'] = ncPriceFormat($refund_amount);

        // 上传凭证
        $pic_array = array();
        $pic_array['buyer'] = $this->upload_pic();//上传凭证
        $info = serialize($pic_array);
        $refund_array['pic_info'] = $info;

        // 设定订单状态
        $model_trade = Model('trade');
        $order_shipped = $model_trade->getOrderState('order_shipped');//订单状态30:已发货

        // 全部退款或已发货订单退款锁定
        if ($order['order_state'] == $order_shipped || $goods_id == 0) {
            $refund_array['order_lock'] = '2';//锁定类型:1为不用锁定,2为需要锁定
        }
        $order_state_arr = $model_trade->getOrderState();
        if (in_array($order['order_state'], array($order_state_arr['order_shipped'],$order_state_arr['order_completed']))) {
            $refund_array['refund_type'] = '2';//类型:1为退款,2为退货
            $refund_array['return_type'] = '2';//退货类型:1为不用退货,2为需要退货
        }else{
            $refund_array['refund_type'] = '1';//类型:1为退款,2为退货
            $refund_array['return_type'] = '1';//退货类型:1为不用退货,2为需要退货
        }
        $refund_array['seller_state'] = '1';//状态:1为待审核,2为同意,3为不同意
        $refund_array['buyer_message'] = $buyer_message;
        $refund_array['add_time'] = time();
        $state = $model_refund->addRefundReturn($refund_array,$order,$goods);

        if ($state) {
            if ($order['order_state'] == $order_shipped) {
                $model_refund->editOrderLock($order_id);
            }
            output_data("提交售后成功");
        } else {
            output_data("系统错误，提交售后失败");
        }
    }
}