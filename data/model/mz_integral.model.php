<?php
/**
 * 积分日志管理
 *
 * @copyright  Copyright (c) 2007-2013 ShopNC Inc. (http://www.shopnc.net)
 * @license    http://www.shopnc.net
 * @link       http://www.shopnc.net
 * @since      File available since Release v1.1
 */
defined('InShopNC') or exit('Access Invalid!');

class mz_integralModel extends Model{
	/**
	 * 操作积分
	 * @author ShopNC Develop Team
	 * @param  string $stage 操作阶段 login(登录),comments(评论),order(下单)
	 * @param  array $insertarr 该数组可能包含信息 array('integral_memberid'=>'会员编号','integral_membername'=>'会员名称','integral_points'=>'积分','integral_desc'=>'描述','orderprice'=>'订单金额','order_sn'=>'订单编号','order_id'=>'订单序号');
	 * @return bool
	 */
	function saveMzIntegralLog($stage,$insertarr){
		if (!$insertarr['integral_memberid']){
			return false;
		}
		$mz_integral_rule = C("mz_integral_rule")?unserialize(C("mz_integral_rule")):array();
		//记录原因文字
		switch ($stage){
			case 'login':
				if (!$insertarr['integral_desc']){
					$insertarr['integral_desc'] = '会员登录';
				}
				$insertarr['integral_points'] = 0;
				if (intval($mz_integral_rule['integral_login']) > 0){
				    $insertarr['integral_points'] = intval($mz_integral_rule['integral_login']);
				}
				break;
			case 'comments':
				if (!$insertarr['integral_desc']){
					$insertarr['integral_desc'] = '评论商品';
				}
				$insertarr['integral_points'] = 0;
				if (intval($mz_integral_rule['integral_comments']) > 0){
				    $insertarr['integral_points'] = intval($mz_integral_rule['integral_comments']);
				}
				break;
			case 'order':
				if (!$insertarr['integral_desc']){
					$insertarr['integral_desc'] = '订单'.$insertarr['order_sn'].'购物消费';
				}
				$insertarr['integral_points'] = 0;
				$mz_integral_rule['integral_orderrate'] = floatval($mz_integral_rule['integral_orderrate']);
				if ($insertarr['orderprice'] && $mz_integral_rule['integral_orderrate'] > 0){
					$insertarr['integral_points'] = @intval($insertarr['orderprice']/$mz_integral_rule['integral_orderrate']);
					$integral_ordermax = intval($mz_integral_rule['integral_ordermax']);
					if ($integral_ordermax > 0 && $insertarr['integral_points'] > $integral_ordermax){
						$insertarr['integral_points'] = $integral_ordermax;
					}
				}
				break;
		}
		//新增日志
		$value_array = array();
		$value_array['integral_memberid'] = $insertarr['integral_memberid'];
		$value_array['integral_membername'] = $insertarr['integral_membername'];
		$value_array['integral_points'] = $insertarr['integral_points'];
		$value_array['integral_addtime'] = time();
		$value_array['integral_desc'] = $insertarr['integral_desc'];
		$value_array['integral_stage'] = $stage;
		$result = false;
		if($value_array['integral_points'] != '0'){
			$result = self::addMzIntegralLog($value_array);
		}
		if ($result){
			//更新mz_member内容
			Model('mz_member')->where(array('member_id'=>$insertarr['integral_memberid']))->update(array('integral'=>array('exp','integral + '.$insertarr['integral_points'])));
			return true;
		}else {
			return false;
		}
	}
	/**
	 * 添加积分日志信息
	 *
	 * @param array $param 添加信息数组
	 */
	public function addMzIntegralLog($param) {
		if(empty($param)) {
			return false;
		}
		$result = $this->table('mz_integral_log')->insert($param);
		return $result;
	}
	
	/**
	 * 积分日志列表
	 *
	 * @param array $where 条件数组
	 * @param mixed $page   分页
	 * @param string $field   查询字段
	 * @param int $limit   查询条数
	 * @param string $order   查询条数
	 */
	public function getMzIntegralLogList($where, $field = '*', $page = 0, $limit = 0,$order = '', $group = '') {
	    if (is_array($page)){
	        if ($page[1] > 0){
	            return $this->table('mz_integral_log')->field($field)->where($where)->page($page[0],$page[1])->order($order)->group($group)->select();
	        } else {
	            return $this->table('mz_integral_log')->field($field)->where($where)->page($page[0])->order($order)->group($group)->select();
	        }
	    } else {
            return $this->table('mz_integral_log')->field($field)->where($where)->page($page)->order($order)->group($group)->select();
        }
	}
	/**
	  * 获得阶段说明文字
	  */
	public function getStage(){
	    $stage_arr = array();
		$stage_arr['login'] = '会员登录';
		$stage_arr['comments'] = '商品评论';
		$stage_arr['order'] = '订单消费';
		return $stage_arr;
	}	
}
