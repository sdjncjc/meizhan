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
        $goods_list = Model('goods')->field('goods_id,goods_name,goods_promotion_price,goods_storage,goods_marketprice,goods_image')->where($condition)->order('goods_salenum desc')->limit((($page-1)*$size).','.$size)->select();
		if($goods_list){
			foreach($goods_list as $k=>$v){
				$goods_list[$k]['img_url'] = thumb($v, 360);
				$goods_list[$k]['discount'] = sprintf('%0.1f', $v['goods_promotion_price']/$v['goods_marketprice']*10);
			}
		}

        output_data(array('goods_list' => $goods_list));
    }

    /**
     * 商品列表
     */
    public function goods_listOp() {
        $model_goods = Model('goods');
        $model_search = Model('search');
        $_GET['is_book'] = 0;

        //查询条件
        $condition = array();
        // ==== 暂时不显示定金预售商品，手机端未做。  ====
        $condition['is_book'] = 0;
        // ==== 暂时不显示定金预售商品，手机端未做。  ====
        if(!empty($_GET['gc_id']) && intval($_GET['gc_id']) > 0) {
            $condition['gc_id'] = $_GET['gc_id'];
        } elseif (!empty($_GET['keyword'])) {
            $condition['goods_name|goods_jingle'] = array('like', '%' . $_GET['keyword'] . '%');
        } elseif (!empty($_GET['barcode'])) {
            $condition['goods_barcode'] = $_GET['barcode'];
        } elseif (!empty($_GET['b_id']) && intval($_GET['b_id'] > 0)) {
            $condition['brand_id'] = intval($_GET['b_id']);
        }

        //所需字段
        $fieldstr = "goods_id,goods_commonid,store_id,goods_name,goods_price,goods_promotion_price,goods_promotion_type,goods_marketprice,goods_image,goods_salenum,evaluation_good_star,evaluation_count";

        // 添加3个状态字段
        $fieldstr .= ',is_virtual,is_presell,is_fcode,have_gift';

        //排序方式
        $order = $this->_goods_list_order($_GET['key'], $_GET['order']);

        //优先从全文索引库里查找
        list($goods_list,$indexer_count) = $model_search->indexerSearch($_GET,$this->page);
        if (!is_null($goods_list)) {
            $goods_list = array_values($goods_list);
            pagecmd('setEachNum',$this->page);
            pagecmd('setTotalNum',$indexer_count);
        } else {
            $goods_list = $model_goods->getGoodsListByColorDistinct($condition, $fieldstr, $order, $this->page);
        }
        $page_count = $model_goods->gettotalpage();
        //处理商品列表(团购、限时折扣、商品图片)
        $goods_list = $this->_goods_list_extend($goods_list);
        output_data(array('goods_list' => $goods_list), mobile_page($page_count));
    }

    /**
     * 商品列表排序方式
     */
    private function _goods_list_order($key, $order) {
        $result = 'is_own_shop desc,goods_id desc';
        if (!empty($key)) {

            $sequence = 'desc';
            if($order == 1) {
                $sequence = 'asc';
            }

            switch ($key) {
                //销量
                case '1' :
                    $result = 'goods_salenum' . ' ' . $sequence;
                    break;
                //浏览量
                case '2' :
                    $result = 'goods_click' . ' ' . $sequence;
                    break;
                //价格
                case '3' :
                    $result = 'goods_price' . ' ' . $sequence;
                    break;
            }
        }
        return $result;
    }

    /**
     * 处理商品列表(团购、限时折扣、商品图片)
     */
    private function _goods_list_extend($goods_list) {
        //获取商品列表编号数组
        $goodsid_array = array();
        foreach($goods_list as $key => $value) {
            $goodsid_array[] = $value['goods_id'];
        }
        
        $sole_array = Model('p_sole')->getSoleGoodsList(array('goods_id' => array('in', $goodsid_array)));
        $sole_array = array_under_reset($sole_array, 'goods_id');

        foreach ($goods_list as $key => $value) {
            $goods_list[$key]['sole_flag']      = false;
            $goods_list[$key]['group_flag']     = false;
            $goods_list[$key]['xianshi_flag']   = false;
            if (!empty($sole_array[$value['goods_id']])) {
                $goods_list[$key]['goods_price'] = $sole_array[$value['goods_id']]['sole_price'];
                $goods_list[$key]['sole_flag'] = true;
            } else {
                $goods_list[$key]['goods_price'] = $value['goods_promotion_price'];
                switch ($value['goods_promotion_type']) {
                    case 1:
                        $goods_list[$key]['group_flag'] = true;
                        break;
                    case 2:
                        $goods_list[$key]['xianshi_flag'] = true;
                        break;
                }
                
            }

            //商品图片url
            $goods_list[$key]['goods_image_url'] = cthumb($value['goods_image'], 360, $value['store_id']);

            unset($goods_list[$key]['goods_promotion_type']);
            unset($goods_list[$key]['goods_promotion_price']);
            unset($goods_list[$key]['store_id']);
            unset($goods_list[$key]['goods_commonid']);
            unset($goods_list[$key]['nc_distinct']);
        }

        return $goods_list;
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
            $goods_detail['cart_count'] = Model('cart')->countCartByMemberId($memberId);
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
     * 商品详细页
     */
    public function goods_bodyOp() {
        header("Access-Control-Allow-Origin:*");
        $goods_id = intval($_GET ['goods_id']);

        $model_goods = Model('goods');

        $goods_info = $model_goods->getGoodsInfoByID($goods_id, 'goods_commonid');
        $goods_common_info = $model_goods->getGoodsCommonInfoByID($goods_info['goods_commonid']);

        Tpl::output('goods_common_info', $goods_common_info);
        Tpl::showpage('goods_body');
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
