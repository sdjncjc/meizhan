<?php
/**
 * 美站首页
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
class mz_indexControl extends mobileHomeControl{

    public function __construct() {
        parent::__construct();
    }

    public function indexOp() {
        exit();
    }

    /**
     * 获取头图
     */
    public function get_sliderOp() {
		$pic = '/img/index.png';
        output_data(array('pic' => $pic));
    }

    /**
     * 获取中部广告
     */
    public function get_adOp() {
		$ad = array();
		$ad['ad_target1'] = '';
		$ad['ad_img1'] = '/img/zad_img1.png';//375x360
		$ad['ad_target2'] = '';
		$ad['ad_img2'] = '/img/zad_img2.png';//374x144
		$ad['ad_target3'] = '';
		$ad['ad_img3'] = '/img/zad_img3.png';//157x187
		$ad['ad_target4'] = '';
		$ad['ad_img4'] = '/img/zad_img4.png';//157x187
        output_data(array('ad' => $ad));
    }

    /**
     * 获取推荐商品列表
     */
    public function get_goods_listOp() {
		$class = Model('goods_class')->field('gc_id,gc_name')->where(array('gc_parent_id'=>0))->order('gc_sort asc')->select();
		if($class){
			$model_goods = Model('goods');
			foreach($class as $k=>$v){
				$list = $model_goods->getGeneralGoodsOnlineList(array('gc_id_1'=>$v['gc_id']), 'goods_id,goods_name,goods_price,goods_storage,goods_marketprice,goods_image', 6, 'goods_salenum desc');
				if($list){
					foreach($list as $kk=>$vv){
						$list[$kk]['img_url'] = thumb($vv, 360);
						$list[$kk]['goods_price'] *= 1;
						$list[$kk]['goods_marketprice'] *= 1;
						$list[$kk]['discount'] = sprintf('%0.1f', $vv['goods_price']/$vv['goods_marketprice']*10);
					}
					$class[$k]['list'] = $list;
				}else{
					unset($class[$k]);
				}
			}
		}

        output_data(array('goods_list' => $class));
    }

}
