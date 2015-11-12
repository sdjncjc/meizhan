<?php
/**
 * top排行榜
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
class mz_topControl extends mobileHomeControl{

    public function __construct() {
        parent::__construct();
    }

    /**
     * 获取列表
     */
    public function get_listOp() {
		$size = 10;
        $condition = array();
		$condition['goods_state'] = 1;
		$condition['goods_verify'] = 1;
		$gc_id_1 = intval($_GET['cate']);
		if($gc_id_1)$condition['gc_id_1'] = $gc_id_1;
		$t = strtotime("today");

        $page = intval($_GET['page']);
        $page = $page <= 0 ? 1 : $page;
		
        $goods_list = Model('goods')->field('goods_id,goods_name,goods_promotion_price,goods_storage,goods_marketprice,goods_image')->where($condition)->order('goods_salenum desc')->limit((($page-1)*$size).','.$size)->select();
		if($goods_list){
			foreach($goods_list as $k=>$v){
				$goods_list[$k]['img_url'] = thumb($v, 360);
				$goods_list[$k]['discount'] = sprintf('%0.1f', $v['goods_promotion_price']/$v['goods_marketprice']*10);
			}
		}

        output_data(array('goods_list' => $goods_list));
    }

}
