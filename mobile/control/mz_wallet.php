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
class mz_walletControl extends mobileMemberControl {

    public function __construct() {
        parent::__construct();
    }

    public function indexOp() {
        exit;
    }

    public function couponListOp(){
        $model_voucher = Model('voucher');
        $voucher_list = $model_voucher->getMemberVoucherList($this->member_info['member_id']);

        if (!empty($voucher_list)) {
            foreach ($voucher_list as $key => $value) {
                $voucher_list[$key]['voucher_start_date'] = date("Y-m-d",$value['voucher_start_date']);
                $voucher_list[$key]['voucher_end_date'] = date("Y-m-d",$value['voucher_end_date']);
                switch ($voucher_list[$key]['voucher_state']) {
                    case 1:
                        $voucher_list[$key]['voucher_state'] = "stamp";
                        break;
                    case 2:
                        $voucher_list[$key]['voucher_state'] = "used";
                        break;
                    case 3:
                        $voucher_list[$key]['voucher_state'] = "expired";
                        break;
                    case 4:
                        $voucher_list[$key]['voucher_state'] = "expired";
                        break;
                }
                $voucher_list[$key]['voucher_state'] = date("Y-m-d H:i:s",$value['voucher_end_date']);
            }
        }
        output_data(array('data'=>$voucher_list));
    }
    public function getMyPointOp(){
        output_data(array('data'=>$this->member_info['member_points']));
    }
    public function pointListOp(){
        $size = 8;     
        $page = intval($_GET['page']);
        $page = $page <= 0 ? 1 : $page;
        //查询积分日志列表
        $points_model = Model('points');
        $condition['pl_memberid'] = $this->member_info['member_id'];
        $condition['limit'] = (($page-1)*$size).','.$size;
        $point_log = $points_model->getPointsLogList($condition,true);
        if (!empty($point_log)) {
            foreach ($point_log as $key => $value) {
                $point_log[$key]['pl_addtime'] = date("Y-m-d",$value['pl_addtime']);
            }
        }
        $point_count = model()->table('points_log')->where(array('pl_memberid'=>$this->member_info['member_id']))->count();
        $data_info['thispage'] = $page;
        $data_info['count'] = $point_count;
        $data_info['totalpage'] = ceil($point_count / $size);

        output_data(array('data'=>$point_log,'data_info'=>$data_info));
    }
    /**
     * 调试
     * @param  [type]  $arr  [description]
     * @param  boolean $stop [description]
     * @return [type]        [description]
     */
    private function debuger($arr,$stop = true){
        header("Access-Control-Allow-Origin:*");
        echo "<pre>";
        print_r($arr);
        if ($stop) {
            die();
        }
    }
}