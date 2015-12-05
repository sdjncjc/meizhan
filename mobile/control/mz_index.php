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

    /**
     * 获取广告
     */
    public function get_adOp() {
		$ad_side = intval($_GET['ad_side']);
        $ad_list = Model('mz_ad')->where(array('ad_side'=>$ad_side,'ad_close'=>0))->order('ad_sort desc, ad_id desc')->select();
		if($ad_list){
			foreach($ad_list as $k=>$v){
				$ad_list[$k]['ad_img_url'] = UPLOAD_SITE_URL.'/mz/ad/'.$v['ad_img'];
				if($v['ad_type_id']){
					$ad_list[$k]['ad_url'] = '/home/'.($v['ad_type'] ? 'detail' : 'article').'.html?id='.$v['ad_type_id'];
				}else{
					$ad_list[$k]['ad_url'] = 'javascript:void(0);';
				}
			}
		}
        output_data(array('ad_list' => $ad_list));
    }

    /**
     * 获取文章
     */
    public function get_articleOp() {
		$article_id = intval($_GET['article_id']);
        $article = Model('mz_article')->where(array('article_id'=>$article_id))->find();
        output_data(array('article' => $article));
    }

    /**
     * 获取推荐商品列表
     */
    public function get_recommend_goods_listOp() {
		$recommend_goods_list = Model('goods_class')->getGoodsClassListByParentId(0);
		if($recommend_goods_list){
			$model_goods = Model('goods');
			foreach($recommend_goods_list as $k=>$v){
				$goods_list = $model_goods->getGeneralGoodsOnlineList(array('gc_id_1'=>$v['gc_id']), 'goods_id,goods_name,goods_price,goods_promotion_price,goods_promotion_type,distribution_price,goods_storage,goods_marketprice,goods_image', 6, 'goods_salenum desc');
				if($goods_list){
					foreach($goods_list as $kk=>$vv){
						if($vv['goods_promotion_type'] > 0){
							$goods_list[$kk]['goods_price'] = $vv['goods_promotion_price'];
						}elseif($vv['distribution_price'] > 0){
							$goods_list[$kk]['goods_price'] = $vv['distribution_price'];
						}
						$goods_list[$kk]['img_url'] = thumb($vv, 360);
						$goods_list[$kk]['discount'] = sprintf('%0.1f', $goods_list[$kk]['goods_price']/$vv['goods_marketprice']*10);
					}
					$recommend_goods_list[$k]['list'] = $goods_list;
				}else{
					unset($recommend_goods_list[$k]);
				}
			}
		}

        output_data(array('recommend_goods_list' => $recommend_goods_list));
    }
}
