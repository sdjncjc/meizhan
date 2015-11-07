<?php
/**
 * 海外购
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
class mz_overseaControl extends mobileHomeControl{

    public function __construct() {
        parent::__construct();
    }

    public function indexOp() {
        exit;
    }

    /**
     * 获取分类
     */
    public function get_classOp() {
		$top_class = Model('goods_class')->field('gc_id,gc_name')->where(array('gc_parent_id'=>0))->order('gc_sort asc')->select();
        output_data(array('oversea_class' => $top_class));
    }

    /**
     * 获取列表
     */
    public function get_listOp() {
		$size = 10;
        $condition = array();
		$condition['is_open'] = 1;
		$condition['is_oversea'] = 1;
		$condition['start_time'] = array('lt', TIMESTAMP);
		$condition['end_time'] = array('gt', TIMESTAMP);

        $page = intval($_GET['page']);
        $page = $page <= 0 ? 1 : $page;
        $oversea_list = Model('brandsale')->where($condition)->order('sort desc')->limit((($page-1)*$size).','.$size)->select();
		if($oversea_list){
			//特卖国家
			$brandsale_area = Model('brandsale_area')->limit(false)->select();
			$brandsale_area_list = array();
			if($brandsale_area){
				foreach($brandsale_area as $v){
					$v['area_img_url'] = UPLOAD_SITE_URL."/shop/oversea/".$v['area_img'];
					$brandsale_area_list[$v['area_id']] = $v;
				}
			}
			$t = strtotime("today");
			foreach($oversea_list as $k=>$v){
				$oversea_list[$k]['img_url'] = UPLOAD_SITE_URL."/shop/brandsale/".$v['image'];
				$oversea_list[$k]['isnew'] = $v['start_time']>=$t ? 1 : 0;
				$info = unserialize($v['info']);
				$oversea_list[$k]['count'] = $info['over_cate'][0]['num'];
				if(!$v['area_id'])$v['area_id'] = 32;
				$oversea_list[$k]['country_icon'] = $brandsale_area_list[$v['area_id']]['area_img_url'];
				$oversea_list[$k]['country_name'] = $brandsale_area_list[$v['area_id']]['area_name'];
			}
		}

        output_data(array('oversea_list' => $oversea_list));
    }

}
