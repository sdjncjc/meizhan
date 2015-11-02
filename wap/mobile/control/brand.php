<?php
/**
 * 前台品牌分类
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
class brandControl extends mobileHomeControl {
    public function __construct() {
        parent::__construct();
    }

    public function recommend_listOp() {
        $brand_list = Model('brand')->getBrandPassedList(array('brand_recommend' => '1'), 'brand_id,brand_name,brand_pic');
        if (!empty($brand_list)) {
            foreach ($brand_list as $key => $val) {
                $brand_list[$key]['brand_pic'] = brandImage($val['brand_pic']);
            }
        }
        output_data(array('brand_list' => $brand_list));
    }
}
