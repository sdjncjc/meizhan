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
        output_data(array('member_info' => $this->member_info));
    }
    public function getSimpleOrderInfoOp(){
        $simpleOrderInfo = array();
        $orders = Model('orders')->field('order_id,evaluation_again_state,order_state')->where(array('delete_state'=>0))->limit(false)->select();
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
        output_data(array('simpleOrderInfo'=>$simpleOrderInfo));

    }
    private function debuger($arr,$stop = true){
        echo "<pre>";
        print_r($arr);
        if ($stop) {
            die();
        }
    }
}