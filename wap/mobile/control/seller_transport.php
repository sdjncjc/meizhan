<?php
/**
 * 商家运费模板
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
class seller_transportControl extends mobileSellerControl{

    public function __construct() {
        parent::__construct();
    }

    public function indexOp() {
        $this->transport_listOp();
    }

    /**
     * 返回商家店铺商品分类列表
     */
    public function transport_listOp() {
        $model_transport = Model('transport');
        $transport_list = $model_transport->getTransportList(array('store_id'=>$this->store_info['store_id']));
        output_data(array('transport_list' => $transport_list));
    }
}
