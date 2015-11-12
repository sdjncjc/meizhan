<?php
/**
 * 商品分类
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
class mz_categoryControl extends mobileHomeControl{

	public function __construct() {
        parent::__construct();
    }

	public function indexOp() {
		$class_list = Model('goods_class')->get_all_category();
		if($class_list){
			foreach($class_list as $k=>$v){
				foreach($v['class2'] as $kk=>$vv){
					$class_list[$k]['class2'][$kk]['img'] = UPLOAD_SITE_URL.'/'.ATTACH_COMMON.'/category-pic-'.$kk.'.jpg';
				}
				$class_list[$k]['yu'] = count($v['class2'])%3;
			}
		}
        output_data(array('class_list' => $class_list));
	}

    /**
     * 获取列表页
     */
    public function get_category_listOp() {
		$cate = intval($_GET['cate']);
		$title = $key = trim($_GET['key']);
		$condition = array();
		$condition['goods_state']   = 1;
		$condition['goods_verify']  = 1;
		if($cate){
			$class = Model('goods_class')->getGoodsClassInfoById($cate);
		}
		$list = array();
		if($class){
			$title = $class['gc_name'];
			$condition['gc_id_2'] = $cate;
			$list = Model('goods')->table('goods_common')->field('gc_id,gc_name,brand_id,brand_name')->where($condition)->group('gc_id,brand_id')->limit(false)->select();
		}elseif($key != ''){
			$condition['goods_name'] = array('like','%'.$key.'%');
			$list = Model('goods')->table('goods_common')->field('gc_id,gc_name,brand_id,brand_name')->where($condition)->group('gc_id,brand_id')->limit(false)->select();
		}

		$class_list = $brand_list = array();
		if($list){
			foreach($list as $k=>$v){
				if(!$class_list[$v['gc_id']]){
					$gc_name = explode(" &gt;",$v['gc_name']);
					$class_list[$v['gc_id']] = end($gc_name);
				}
				if(!$brand_list[$v['brand_id']])$brand_list[$v['brand_id']] = $v['brand_name'];
			}
			unset($class_list[0],$brand_list[0]);
		}

        output_data(array('class_list' => $class_list,'brand_list' => $brand_list,'title' => $title));
    }

    /**
     * 获取商品
     */
    public function get_goodsOp() {
		$cate = intval($_GET['cate']);
		$key = trim($_GET['key']);
		$cates = trim($_GET['cates']);
		$brands = trim($_GET['brands']);
		$sort = intval($_GET['sort']);
		$size = 10;
		$condition = array();
		$condition['goods_state']   = 1;
		$condition['goods_verify']  = 1;
		if($cates)$condition['gc_id'] = array('in', $cates);
		if($brands)$condition['brand_id'] = array('in', $brands);
		if($sort){
			$order = "goods_promotion_price asc";
		}else{
			$order = "goods_salenum desc";
		}
		$page = intval($_GET['page']);
		$page = $page <= 0 ? 1 : $page;
		$goods_list = array();
		if($cate){
			$condition['gc_id_2'] = $cate;
			$goods_list = Model('goods')->field('goods_id,goods_storage,goods_name,goods_image,goods_type,goods_marketprice,goods_promotion_price,goods_addtime,goods_salenum')->where($condition)->group('goods_commonid')->order($order)->limit((($page-1)*$size).','.$size)->select();
		}elseif($key != ''){
			$condition['goods_name'] = array('like','%'.$key.'%');
			$goods_list = Model('goods')->field('goods_id,goods_storage,goods_name,goods_image,goods_type,goods_marketprice,goods_promotion_price,goods_addtime,goods_salenum')->where($condition)->group('goods_commonid')->order($order)->limit((($page-1)*$size).','.$size)->select();
		}

		if($goods_list){
			foreach($goods_list as $k=>$v){
				if($v['goods_addtime']>=$t){
					$goods_list[$k]['tag'] = 'is_new';
				}elseif($v['goods_salenum']>=100){
					$goods_list[$k]['tag'] = 'is_hot';
				}
				$goods_list[$k]['img_url'] = thumb($v, 360);
				$goods_list[$k]['discount'] = sprintf('%0.1f', $v['goods_promotion_price']/$v['goods_marketprice']*10);
			}
		}

        output_data(array('goods_list' => $goods_list));
    }
}
