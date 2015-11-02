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

    public function indexOp() {
        exit;
    }

    /**
     * 获取分类
     */
    public function get_classOp() {
		$brandsale_class = Model('goods_class')->field('gc_id,gc_name')->where(array('gc_parent_id'=>0))->order('gc_sort asc')->select();
        output_data(array('brandsale_class' => $brandsale_class));
    }

    /**
     * 获取列表
     */
    public function get_listOp() {
		$size = 10;
        $condition = array();
		$condition['is_open'] = 1;
		$condition['start_time'] = array('lt', TIMESTAMP);
		$condition['end_time'] = array('gt', TIMESTAMP);
		$t = strtotime("today");
		if($_GET['category'] == 'index'){
			$condition['start_time'] = array('gt', $t);
		}elseif($_GET['category'] == 'lastminute'){
			$condition['end_time'] = array('lt', $t+86400);
		}elseif($_GET['category'] > 0){
			$condition['gc_id'] = intval($_GET['category']);
		}else{
			$condition['recommended'] = 1;
			$size = 5;
		}
        $type = intval($_GET['type']);
		if($type)$condition['is_oversea'] = 1;
        $page = intval($_GET['page']);
        $page = $page <= 0 ? 1 : $page;
        $brandsale_list = Model('brandsale')->where($condition)->order('sort desc')->limit((($page-1)*$size).','.$size)->select();
		if($brandsale_list){
			foreach($brandsale_list as $k=>$v){
				$brandsale_list[$k]['img_url'] = UPLOAD_SITE_URL."/shop/brandsale/".$v['image'];
				$brandsale_list[$k]['isnew'] = $v['start_time']>=$t ? 1 : 0;
				$lest = $v['end_time']-TIMESTAMP;
				$brandsale_list[$k]['timer']['day'] = floor($lest/86400);
				$brandsale_list[$k]['timer']['hour'] = floor($lest%86400/3600);
				$brandsale_list[$k]['timer']['minute'] = floor($lest%3600/60);
			}
		}

        output_data(array('brandsale_list' => $brandsale_list));
    }

}
