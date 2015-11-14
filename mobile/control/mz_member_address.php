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
class mz_member_addressControl extends mobileMemberControl {

    public function __construct() {
        parent::__construct();
    }

    public function indexOp() {
        exit;
    }

    public function listOp(){
        $condition = array();
        $condition['member_id'] = $this->member_info['member_id'];
        $model_address = Model('address');
        $address_list = $model_address->getAddressList($condition);
        if (!empty($address_list)) {
            foreach ($address_list as $key => $value) {
                $address_list[$key]['json_data'] = json_encode($value);
            }
        }
        output_data(array('data'=>$address_list)); 
    }
    public function updateAddressOp(){
        $address_model = Model('address');
        $data = array();
        $data['member_id'] = $this->member_info['member_id'];
        $data['true_name'] = $_POST['true_name'];
        $data['area_id'] = intval($_POST['area_id']);
        $data['city_id'] = intval($_POST['city_id']);
        $data['area_info'] = $_POST['area_info'];
        $data['address'] = $_POST['address'];
        $data['mob_phone'] = $_POST['mob_phone'];
        $data['is_default'] = $_POST['is_default'] ? 1 : 0;

        if ($data['is_default'] == 1) {
            $address_model->editAddress(array('is_default'=>0), array('member_id'=>$this->member_info['member_id'],'is_default'=>1));
        }
        if (intval($_POST['id']) > 0){
            $rs = $address_model->editAddress($data, array('address_id' => intval($_POST['id']),'member_id'=>$this->member_info['member_id']));
            if (!$rs){
                output_error($lang['member_address_add_fail']);
            }
        }else {
            $count = $address_model->getAddressCount(array('member_id'=>$this->member_info['member_id']));
            if ($count >= 20) {
                output_error("最多允许添加20个有效地址");
            }
            $rs = $address_model->addAddress($data);
            if (!$rs){
                output_error($lang['member_address_add_fail']);
            }
        }
        output_data("设置成功");
    }
    public function delAddressOp(){
        $address_id = $_POST['id'];
        if (intval($address_id) > 0) {
            $address_model = Model('address');
            $condition =array();
            $condition['address_id'] = intval($address_id);
            $condition['member_id'] = $this->member_info['member_id'];
            if($address_model->delAddress($condition)){
                output_data("删除成功");
            }else{
                output_error("系统错误");
            }

        }else{
            output_error("参数错误");
        }
    }
}