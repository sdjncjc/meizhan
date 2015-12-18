<?php
/**
 * 品牌特卖
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
class mz_brandsaleControl extends mobileHomeControl{

    public function __construct() {
        parent::__construct();
    }


    /**
     * 获取列表
     */
    public function get_listOp() {

			//国家
        	$country_list = rkcache('country');
			$t = strtotime("today");
			foreach($oversea_list as $k=>$v){
				$oversea_list[$k]['img_url'] = UPLOAD_SITE_URL."/shop/brandsale/".$v['image'];
				$oversea_list[$k]['isnew'] = $v['start_time']>=$t ? 1 : 0;
				$info = unserialize($v['info']);
				$oversea_list[$k]['count'] = $info['over_cate'][0]['num'];
				if(!$v['area_id'])$v['area_id'] = 32;
				$oversea_list[$k]['country_icon'] = $country_list[$v['country_id']]['country_img_url'];
				$oversea_list[$k]['country_name'] = $country_list[$v['country_id']]['country_name'];
			}

        output_data(array('oversea_list' => $oversea_list));
    }
    /**
     * 获取特卖列表
     */
    public function get_brandsale_listOp() {
		$size = 10;
        $condition = array();
		$condition['is_open'] = 1;
		$condition['start_time'] = array('lt', TIMESTAMP);
		$condition['end_time'] = array('gt', TIMESTAMP);
		$t = strtotime("today");
        $type = intval($_GET['type']);
		if($_GET['cate'] == 'new'){
			$condition['start_time'] = array('between', array($t, TIMESTAMP));
		}elseif($_GET['cate'] == 'lastminute'){
			$condition['end_time'] = array('between', array(TIMESTAMP,$t+86400));
		}elseif($_GET['cate'] > 0){
			$condition['gc_id'] = intval($_GET['cate']);
		}elseif($type){
			$condition['is_oversea'] = 1;
		}else{
			$condition['recommended'] = 1;
			$size = 10;
		}
        $page = intval($_GET['page']);
        $page = $page <= 0 ? 1 : $page;
        $brandsale_list = Model('brandsale')->where($condition)->order('sort desc')->limit((($page-1)*$size).','.$size)->select();
		if($brandsale_list){
			if($type){
				//国家
				$country_list = rkcache('country');
			}
        	$goods_common_model = Model('goods_common');
			foreach($brandsale_list as $k=>$v){
				$brandsale_list[$k]['img_url'] = UPLOAD_SITE_URL."/shop/brandsale/".$v['image'];
				$brandsale_list[$k]['isnew'] = $v['start_time']>=$t ? 1 : 0;
				if($type){
					$info = unserialize($v['info']);
					$goods = $goods_common_model->field('count(*) as num')->where(array('goods_state'=>1,'goods_verify'=>1,'brand_id'=>$v['brand_id'],'gc_id_1'=>$v['gc_id']))->find();
					$brandsale_list[$k]['count'] = $goods['num'];
					$brandsale_list[$k]['country_icon'] = $country_list[$v['country_id']]['country_img_url'];
					$brandsale_list[$k]['country_name'] = $country_list[$v['country_id']]['country_name'];
				}else{
					$lest = $v['end_time']-TIMESTAMP;
					$brandsale_list[$k]['timer']['day'] = floor($lest/86400);
					$brandsale_list[$k]['timer']['hour'] = floor($lest%86400/3600);
					$brandsale_list[$k]['timer']['minute'] = floor($lest%3600/60);
				}
			}
		}

        output_data(array('brandsale_list' => $brandsale_list));
    }

    /**
     * 获取特卖详情
     */
    public function get_brandsale_infoOp() {
		$rec_id = intval($_GET['rec_id']);
        $brandsale_model = Model('brandsale');
        $brandsale = $brandsale_model->where(array('is_open'=>1,'rec_id'=>$rec_id))->find();
		if(empty($brandsale)){
            output_error('该品牌特卖未开启或不存在！');
		}
		$brandsale['image_url'] = UPLOAD_SITE_URL."/shop/brandsale/".$brandsale['image'];
		$type = intval($_GET['type']);
		if($type){
			//国家
			$country_list = rkcache('country');
			$brandsale['area_name'] = $country_list[$brandsale['country_id']]['country_name'];
		}else{
			if($brandsale['start_time']<=TIMESTAMP && $brandsale['end_time']>TIMESTAMP){
				$brandsale['remaining_time'] = $brandsale['end_time']-TIMESTAMP;
			}
	
			$condition = array();
			$condition['goods_state'] = 1;
			$condition['goods_verify'] = 1;
			$condition['brand_id'] = $brandsale['brand_id'];
			if(!$brandsale['recommended'])$condition['gc_id_1'] = $brandsale['gc_id'];
			$goods_list = Model('goods_common')->field('gc_id,gc_name,count(*) as num')->where($condition)->group('gc_id')->limit(false)->select();
			$brandsale_cate = array();
			$brandsale_cate[0] = array('gc_name'=>'全部','num'=>0);
			if($goods_list){
				foreach($goods_list as $v){
					$v['gc_name'] = end(explode(' &gt;',$v['gc_name']));
					$brandsale_cate[$v['gc_id']] = $v;
					$brandsale_cate[0]['num'] += $v['num'];
				}
			}
	
			$brandsale['brandsale_cate'] = $brandsale_cate;
		}

        output_data(array('brandsale' => $brandsale));
    }

    /**
     * 获取特卖商品
     */
    public function get_brandsale_goodsOp() {
		$rec_id = intval($_GET['rec_id']);
        $brandsale_model = Model('brandsale');
        $brandsale = $brandsale_model->where(array('is_open'=>1,'rec_id'=>$rec_id))->find();
		$goods_list = array();
		if($brandsale){
			$size = 10;
			$condition = array();
			$condition['goods_state'] = 1;
			$condition['goods_verify'] = 1;
			$condition['brand_id'] = $brandsale['brand_id'];
			if(!$brandsale['recommended'])$condition['gc_id_1'] = $brandsale['gc_id'];
			$stock = intval($_GET['stock']);
			if($stock)$condition['goods_storage'] = array('gt', 0);
			$sort = intval($_GET['sort']);
			switch($sort){
				case '1':
					$order = "goods_salenum desc";
					break;
				case '2':
					$order = "goods_promotion_price asc";
					break;
				default:
					$order = "goods_id desc";
			}
			$cate = intval($_GET['cate']);
			if($cate)$condition['gc_id'] = $cate;
			
			$page = intval($_GET['page']);
			$page = $page <= 0 ? 1 : $page;
	
			$goods_list = Model('goods')->field('goods_id,goods_storage,goods_name,goods_image,goods_marketprice,goods_price,goods_promotion_price,goods_promotion_type,distribution_price,country_id')->where($condition)->group('goods_commonid')->order($order)->limit((($page-1)*$size).','.$size)->select();
			if($goods_list){
				//国家
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
		}

        output_data(array('goods_list' => $goods_list));
    }

    /**
     * 获取海外购指定分类下品牌
     */
    public function get_oversea_brandOp() {
        $condition = array();
		$condition['is_open'] = 1;
		$condition['is_oversea'] = 1;
		$condition['gc_id'] = intval($_GET['gc_id']);
		$condition['start_time'] = array('lt', TIMESTAMP);
		$condition['end_time'] = array('gt', TIMESTAMP);

        $brand_list = Model('brandsale')->field('rec_id,brand_name,brand_pic')->where($condition)->order('sort desc')->limit(8)->select();
		if($brand_list){
			foreach($brand_list as $k=>$v){
                $brand_list[$k]['brand_pic'] = brandImage($v['brand_pic']);
			}
		}

        output_data(array('brand_list' => $brand_list));
    }
    /**
     * 小组品牌特卖列表
     * @return [type] [description]
     */
    public function get_team_brand_listOp() {
		$size = 10;

		$team_user_info = Model("mz_member")->where(array('member_id'=>$this->getMemberIdIfExists()))->find();
		$hasadd_brands_json = Model("mz_team_sale")->where(array('team_id'=>$team_user_info['team_id']))->get_field("brandsale");
		$hasadd_brands_arr = json_decode($hasadd_brands_json);

        $condition = array();
		$condition['is_open'] = 1;
		$condition['start_time'] = array('lt', TIMESTAMP);
		$condition['end_time'] = array('gt', TIMESTAMP);
		$type = isset($_GET['type'])?trim($_GET['type']):'no_add';
		if ($type == 'no_add') {
			$condition['rec_id'] = array('not in',$hasadd_brands_arr);
		}else{
			$condition['rec_id'] = array('in',$hasadd_brands_arr);
		}

        $page = intval($_GET['page']);
        $page = $page <= 0 ? 1 : $page;
        $brandsale_list = Model('brandsale')->where($condition)->order('sort desc')->limit((($page-1)*$size).','.$size)->select();
		if($brandsale_list){
        	$goods_common_model = Model('goods_common');
			foreach($brandsale_list as $k=>$v){
				$brandsale_list[$k]['img_url'] = UPLOAD_SITE_URL."/shop/brandsale/".$v['image'];
				$brandsale_list[$k]['end_time'] = date("m-d",$v['end_time']);
				if ($type == 'no_add') {
					$brandsale_list[$k]['is_add'] = false;
				}else{
					$brandsale_list[$k]['is_add'] = true;
				}
			}
		}
		$data_info = array();
        $count = Model('brandsale')->where($condition)->count();
        $data_info['thispage'] = $page;
        $data_info['totalpage'] = ceil($count / $size);
        output_data(array('data' => $brandsale_list,'data_info'=>$data_info));
    }
    public function addTeamBrandSaleOp(){
    	$rec_id = intval($_POST['id']);
    	if ($rec_id <= 0) {
    		output_error("参数错误");
    	}
		$team_user_info = Model("mz_member")->where(array('member_id'=>$this->getMemberIdIfExists()))->find();
		if ($team_user_info['type'] == 0) {
			output_error("无权限");
		}
		$team_count = Model("mz_team_sale")->where(array('team_id'=>$team_user_info['team_id']))->count();
		if ($team_count > 0) {
			$hasadd_brands_json = Model("mz_team_sale")->where(array('team_id'=>$team_user_info['team_id']))->get_field("brandsale");
			$hasadd_brands_arr = @json_decode($hasadd_brands_json);
			if (!empty($hasadd_brands_arr)) {
				if (count($hasadd_brands_arr) >=10) {
					output_error("已达最大添加数");
				}
				if (!in_array($rec_id, $hasadd_brands_arr)) {
					$hasadd_brands_arr[] = $rec_id;
					$result = Model("mz_team_sale")->where(array('team_id'=>$team_user_info['team_id']))->update(array('brandsale'=>json_encode($hasadd_brands_arr)));
				}else{
					output_error("已添加");
				}
			}else{
				$hasadd_brands_arr[] = $rec_id;
				$result = Model("mz_team_sale")->where(array('team_id'=>$team_user_info['team_id']))->update(array('brandsale'=>json_encode($hasadd_brands_arr)));
			}
		}else{
			$result = Model("mz_team_sale")->insert(array('team_id'=>$team_user_info['team_id'],'brandsale'=>json_encode(array($rec_id))));
		}
		if ($result) {
			output_data("添加成功");
		}else{
			output_error("系统错误，添加失败");
		}
    }
    public function removeTeamBrandSaleOp(){
    	$rec_id = intval($_POST['id']);
    	if ($rec_id <= 0) {
    		output_error("参数错误");
    	}
		$team_user_info = Model("mz_member")->where(array('member_id'=>$this->getMemberIdIfExists()))->find();
		if ($team_user_info['type'] == 0) {
			output_error("无权限");
		}
		$hasadd_brands_json = Model("mz_team_sale")->where(array('team_id'=>$team_user_info['team_id']))->get_field("brandsale");
		if (!empty($hasadd_brands_json)) {
			$hasadd_brands_arr = json_decode($hasadd_brands_json);
			if (!empty($hasadd_brands_arr)) {
				$key = array_search($rec_id,$hasadd_brands_arr);
				if ($key !== false) {
					array_splice($hasadd_brands_arr, $key, 1);
				}
				$result = Model("mz_team_sale")->where(array('team_id'=>$team_user_info['team_id']))->update(array('brandsale'=>json_encode($hasadd_brands_arr)));
			}
		}
		output_data("删除成功");
    }
}
