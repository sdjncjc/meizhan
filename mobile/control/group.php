<?php
/**
 * 专属客服组
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
class groupControl extends mobileMemberControl{

    public function __construct() {
        parent::__construct();
    }

    public function indexOp() {
		$member_info['group_id'] = 0;
        if(!empty($this->member_info['member_id'])){
			$member_info = Model('member')->getMemberInfoByID($this->member_info['member_id'],'group_id');
		}
		$group_id = $member_info['group_id'] < 1 ? rand(1,6) : $member_info['group_id'];
		$promotion_group = Model('seller_promotion_group')->getSellerPromotionGroupInfo(array('group_id'=>$group_id));
        output_data(array('group_id' => $member_info['group_id'], 'group' => $promotion_group));
    }

    public function group_addOp(){
		$group_pass = trim($_POST['group_pass']);
		if(!$group_pass){
       		output_data(array('msg' => '暗号不能为空'));
		}
		$seller_group_info = Model('seller_promotion_group')->getSellerPromotionGroupInfo(array('group_pass' => $group_pass));
		if(!$seller_group_info){
       		output_data(array('msg' => '暗号错误'));
		}
		$update = Model('member')->editMember(array('member_id'=>$this->member_info['member_id']), array('group_id'=>$seller_group_info['group_id']));
		if($update) {
       		output_data(array('msg' => 'true'));
		} else {
       		output_data(array('msg' => '系统错误'));
		}
    }
}
