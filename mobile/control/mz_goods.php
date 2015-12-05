<?php
/**
 * 商品
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
class mz_goodsControl extends mobileHomeControl{

    public function __construct() {
        parent::__construct();
    }

    /**
     * 获取一级商品分类
     */
    public function get_gc_id_1_listOp() {
		$gc_id_1_list = Model('goods_class')->getGoodsClassListByParentId(0);
        output_data(array('gc_id_1_list' => $gc_id_1_list));
    }

    /**
     * 获取指定分类下的二级分类
     */
    public function get_gc_id_2_listOp() {
		$gc_id = intval($_GET['gc_id']);
        $goods_class_model = Model('goods_class');
		$class = $goods_class_model->getGoodsClassInfoById($gc_id);
		$goods_class = $goods_class_model->getGoodsClassListByParentId($gc_id);
        output_data(array('class_name' => $class['gc_name'],'goods_class' => $goods_class));
    }

    /**
     * 获取top列表
     */
    public function get_top_listOp() {
		$size = 10;
        $condition = array();
		$condition['goods_state'] = 1;
		$condition['goods_verify'] = 1;

        $page = intval($_GET['page']);
        $page = $page <= 0 ? 1 : $page;
		$gc_id_1 = intval($_GET['cate']);
		if($gc_id_1){
			$condition['gc_id_1'] = $gc_id_1;
			if($page > 5)output_data(array('goods_list' => array()));
		}else{
			if($page > 10)output_data(array('goods_list' => array()));
		}
        $goods_list = Model('goods')->field('goods_id,goods_name,goods_price,goods_promotion_price,goods_promotion_type,distribution_price,goods_storage,goods_marketprice,goods_image')->where($condition)->order('goods_salenum desc')->limit((($page-1)*$size).','.$size)->select();
		if($goods_list){
			foreach($goods_list as $k=>$v){
				if($v['goods_promotion_type'] > 0){
					$goods_list[$k]['goods_price'] = $v['goods_promotion_price'];
				}elseif($v['distribution_price'] > 0){
					$goods_list[$k]['goods_price'] = $v['distribution_price'];
				}
				$goods_list[$k]['img_url'] = thumb($v, 360);
				$goods_list[$k]['discount'] = sprintf('%0.1f', $goods_list[$k]['goods_price']/$v['goods_marketprice']*10);
			}
		}

        output_data(array('goods_list' => $goods_list));
    }

    /**
     * 获取商品分类列表
     */
	public function get_goods_classOp() {
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
     * 获取商品分类列表页
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
				if(!$class_list[$v['gc_id']])$class_list[$v['gc_id']] = end(explode(" &gt;",$v['gc_name']));
				if(!$brand_list[$v['brand_id']])$brand_list[$v['brand_id']] = $v['brand_name'];
			}
			unset($class_list[0],$brand_list[0]);
		}

        output_data(array('class_list' => $class_list,'brand_list' => $brand_list,'title' => $title));
    }

    /**
     * 获取分类商品列表
     */
    public function get_category_goodsOp() {
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
			$goods_list = Model('goods')->field('goods_id,goods_storage,goods_name,goods_image,goods_type,goods_marketprice,goods_price,goods_promotion_price,goods_promotion_type,distribution_price')->where($condition)->group('goods_commonid')->order($order)->limit((($page-1)*$size).','.$size)->select();
		}elseif($key != ''){
			$condition['goods_name'] = array('like','%'.$key.'%');
			$goods_list = Model('goods')->field('goods_id,goods_storage,goods_name,goods_image,goods_type,goods_marketprice,goods_price,goods_promotion_price,goods_promotion_type,distribution_price')->where($condition)->group('goods_commonid')->order($order)->limit((($page-1)*$size).','.$size)->select();
		}

		if($goods_list){
			foreach($goods_list as $k=>$v){
				if($v['goods_promotion_type'] > 0){
					$goods_list[$k]['goods_price'] = $v['goods_promotion_price'];
				}elseif($v['distribution_price'] > 0){
					$goods_list[$k]['goods_price'] = $v['distribution_price'];
				}
				$goods_list[$k]['img_url'] = thumb($v, 360);
				$goods_list[$k]['discount'] = sprintf('%0.1f', $goods_list[$k]['goods_price']/$v['goods_marketprice']*10);
			}
		}

        output_data(array('goods_list' => $goods_list));
    }
    /**
     * 获取海外购商品列表
     */
    public function get_oversea_goods_listOp() {
		$size = 10;
		$condition = array();
		$condition['gc_id_1'] = intval($_GET['gc_id_1']);
		$condition['goods_type'] = array('gt', 0);
		$gc_id_2 = intval($_GET['gc_id_2']);
		if($gc_id_2)$condition['gc_id_2'] = $gc_id_2;

		$page = intval($_GET['page']);
		$page = $page <= 0 ? 1 : $page;

		$goods_list = Model()->table('goods')->field('country_id,goods_id,goods_storage,goods_name,goods_image,goods_marketprice,goods_price,goods_promotion_price,goods_promotion_type,distribution_price')->where($condition)->group('goods_commonid')->order('goods_id desc')->limit((($page-1)*$size).','.$size)->select();
		if($goods_list){
			//特卖国家
        	$country_list = rkcache('country');
			foreach($goods_list as $k=>$v){
				if($v['goods_promotion_type'] > 0){
					$goods_list[$k]['goods_price'] = $v['goods_promotion_price'];
				}elseif($v['distribution_price'] > 0){
					$goods_list[$k]['goods_price'] = $v['distribution_price'];
				}
				$goods_list[$k]['img_url'] = thumb($v, 360);
				$goods_list[$k]['discount'] = sprintf('%0.1f', $goods_list[$k]['goods_price']/$v['goods_marketprice']*10);
				$goods_list[$k]['country_icon'] = $country_list[$v['country_id']]['country_img_url'];
			}
		}

        output_data(array('goods_list' => $goods_list));
    }

    /**
     * 商品详细页
     */
    public function goods_detailOp() {
        $goods_id = intval($_GET['goods_id']);

        // 商品详细信息
        $model_goods = Model('goods');
        $goods_detail = $model_goods->getGoodsDetail($goods_id);
        if (empty($goods_detail)) {
            output_error('商品不存在');
        }

        // 默认预订商品不支持手机端显示
        if ($goods_detail['is_book']) {
            output_error('预订商品不支持手机端显示');
        }

        //特卖
        $goods_detail['brandsale'] = Model('brandsale')->field('rec_id, brand_name, start_time, end_time, brand_pic, info, is_oversea')->where(array('is_open'=>1,'brand_id'=>$goods_detail['goods_info']['brand_id'],'gc_id'=>$goods_detail['goods_info']['gc_id_1']))->find();
		if($goods_detail['brandsale']['start_time']<=TIMESTAMP && $goods_detail['brandsale']['end_time']>TIMESTAMP){
			$goods_detail['goods_info']['remaining_time'] = $goods_detail['brandsale']['end_time']-TIMESTAMP;
		}
		if($goods_detail['brandsale']){
			$goods_detail['brandsale']['brand_pic_url'] = brandImage($goods_detail['brandsale']['brand_pic']);
			$info = unserialize($goods_detail['brandsale']['info']);
			$goods_detail['brandsale']['special_content'] = '上新'.$info['data_cate'][0]['num'].'款';
		}
		
        //国家
        $country_list = rkcache('country');
        $goods_detail['country'] = $country_list[$goods_detail['goods_info']['country_id']];

		//发货地区
		if($goods_detail['goods_info']['country_id'] > 0 && $goods_detail['goods_info']['areaid_2']){
			$area = Model('area')->field('area_name')->where(array('area_id'=>$goods_detail['goods_info']['areaid_2']))->find();
			$goods_detail['goods_info']['send_area_name'] = $area['area_name'];
		}
		
		if($goods_detail['goods_info']['goods_promotion_type'] == 0 && $goods_detail['goods_info']['distribution_price'] > 0){
			$goods_detail['goods_info']['goods_promotion_price'] = $goods_detail['goods_info']['distribution_price'];
		}
		
        //折扣
		$goods_detail['goods_info']['discount'] = sprintf('%0.1f', $goods_detail['goods_info']['goods_promotion_price']/$goods_detail['goods_info']['goods_marketprice']*10);
        //口碑
        $goods_detail['goodsevallist'] = Model('evaluate_goods')->field('geval_frommembername,geval_scores,geval_content')->where(array('geval_goodsid'=>$goods_id))->order('geval_scores desc, geval_id desc')->limit(2)->select();

        //店铺
        $model_store = Model('store');
        $store_info = $model_store->getStoreInfoByID($goods_detail['goods_info']['store_id']);

        $goods_detail['store_info']['store_id'] = $store_info['store_id'];
        $goods_detail['store_info']['store_name'] = $store_info['store_name'];
        $goods_detail['store_info']['member_id'] = $store_info['member_id'];
        $goods_detail['store_info']['member_name'] = $store_info['member_name'];
        $goods_detail['store_info']['avatar'] = getMemberAvatarForID($store_info['member_id']);

        $goods_detail['store_info']['goods_count'] = $store_info['goods_count'];

        if ($store_info['is_own_shop']) {
            $goods_detail['store_info']['store_credit'] = array(
                'store_desccredit' => array (
                    'text' => '描述',
                    'credit' => 5,
                    'percent' => '----',
                    'percent_class' => 'equal',
                    'percent_text' => '平',
                ),
                'store_servicecredit' => array (
                    'text' => '服务',
                    'credit' => 5,
                    'percent' => '----',
                    'percent_class' => 'equal',
                    'percent_text' => '平',
                ),
                'store_deliverycredit' => array (
                    'text' => '物流',
                    'credit' => 5,
                    'percent' => '----',
                    'percent_class' => 'equal',
                    'percent_text' => '平',
                ),
            );
        } else {
            $storeCredit = array();
            $percentClassTextMap = array(
                'equal' => '平',
                'high' => '高',
                'low' => '低',
            );
            foreach ((array) $store_info['store_credit'] as $k => $v) {
                $v['percent_text'] = $percentClassTextMap[$v['percent_class']];
                $storeCredit[$k] = $v;
            }
            $goods_detail['store_info']['store_credit'] = $storeCredit;
        }

        //商品详细信息处理
        $goods_detail = $this->_goods_detail_extend($goods_detail);

        // 如果已登录 判断该商品是否已被收藏
        if ($memberId = $this->getMemberIdIfExists()) {
            $c = (int) Model('favorites')->getGoodsFavoritesCountByGoodsId($goods_id, $memberId);
            $goods_detail['is_favorate'] = $c > 0;
            $cart_goods = Model('cart')->listCart('db',array('buyer_id'=>$memberId));
            $cart_count = 0;
            if(!empty($cart_goods) && is_array($cart_goods)) {
                foreach ($cart_goods as $val) {
                    $cart_count += $val['goods_num'];
                }
            }
            $goods_detail['cart_count'] = $cart_count;
        }
//print_r($goods_detail);
        output_data($goods_detail);
    }

    /**
     * 商品详细信息处理
     */
    private function _goods_detail_extend($goods_detail) {
        //整理商品规格
        unset($goods_detail['spec_list']);
        $goods_detail['spec_list'] = $goods_detail['spec_list_mobile'];
        unset($goods_detail['spec_list_mobile']);

        //整理商品图片
        unset($goods_detail['goods_image']);
        //unset($goods_detail['goods_image_mobile']);

        //商品链接
        $goods_detail['goods_info']['goods_url'] = urlShop('goods', 'index', array('goods_id' => $goods_detail['goods_info']['goods_id']));

        //整理数据
        unset($goods_detail['goods_info']['goods_commonid']);
        unset($goods_detail['goods_info']['gc_id']);
        unset($goods_detail['goods_info']['gc_name']);
        unset($goods_detail['goods_info']['store_id']);
        unset($goods_detail['goods_info']['store_name']);
        unset($goods_detail['goods_info']['brand_id']);
        //unset($goods_detail['goods_info']['brand_name']);
        unset($goods_detail['goods_info']['type_id']);
        unset($goods_detail['goods_info']['goods_image']);
        //unset($goods_detail['goods_info']['goods_body']);
        unset($goods_detail['goods_info']['goods_state']);
        unset($goods_detail['goods_info']['goods_stateremark']);
        unset($goods_detail['goods_info']['goods_verify']);
        unset($goods_detail['goods_info']['goods_verifyremark']);
        unset($goods_detail['goods_info']['goods_lock']);
        unset($goods_detail['goods_info']['goods_addtime']);
        unset($goods_detail['goods_info']['goods_edittime']);
        unset($goods_detail['goods_info']['goods_selltime']);
        unset($goods_detail['goods_info']['goods_show']);
        unset($goods_detail['goods_info']['goods_commend']);
        unset($goods_detail['goods_info']['explain']);
        unset($goods_detail['goods_info']['cart']);
        unset($goods_detail['goods_info']['buynow_text']);
        unset($goods_detail['groupbuy_info']);
        unset($goods_detail['xianshi_info']);

        return $goods_detail;
    }

    /**
     * 商品评论
     */
    public function get_commentsOp() {
		$size = 10;
        $goods_id = intval($_GET['goods_id']);
        $condition = array();
        $condition['geval_goodsid'] = $goods_id;
        $page = intval($_GET['page']);
        $page = $page <= 0 ? 1 : $page;
        $type = intval($_GET['type']);
        switch ($type) {
            case '1':
                $condition['geval_scores'] = array('in', '5,4');
                break;
            case '2':
                $condition['geval_scores'] = array('in', '3,2');
                break;
            case '3':
                $condition['geval_scores'] = array('in', '1');
                break;
        }

        $goodsevallist = Model('evaluate_goods')->field('geval_scores,geval_frommembername,geval_content,geval_image')->where($condition)->order('geval_id desc')->limit((($page-1)*$size).','.$size)->select();
		if($goodsevallist){
			foreach($goodsevallist as $k=>$v){
				if($v['geval_image']){
					$geval_image = explode(",",$v['geval_image']);
					foreach($geval_image as $vv){
						$goodsevallist[$k]['geval_image_arr'][] = snsThumb($vv);
					}
				}
			}
		}
        output_data(array('goodsevallist' => $goodsevallist));
    }
}
