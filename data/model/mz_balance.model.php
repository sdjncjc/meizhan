<?php
/**
 * 余额日志管理
 *
 * @copyright  Copyright (c) 2007-2013 ShopNC Inc. (http://www.shopnc.net)
 * @license    http://www.shopnc.net
 * @link       http://www.shopnc.net
 * @since      File available since Release v1.1
 */
defined('InShopNC') or exit('Access Invalid!');

class mz_balanceModel extends Model{
	/**
	 * 操作余额
	 * @author ShopNC Develop Team
	 * @param  string $stage 操作阶段 login(登录),comments(评论),order(下单)
	 * @param  array $insertarr 该数组可能包含信息 array('balance_memberid'=>'会员编号','balance_membername'=>'会员名称','balance_points'=>'余额','balance_desc'=>'描述','orderprice'=>'订单金额','order_sn'=>'订单编号','order_id'=>'订单序号');
	 * @return bool
	 */
	function saveMzBalanceLog($stage,$insertarr){
		if (!$insertarr['balance_teamid']){
			return false;
		}
		//记录原因文字
		switch ($stage){
			case 'allot':
				if (!$insertarr['balance_desc']){
					$insertarr['balance_desc'] = '小组'.$insertarr['balance_teamname'].'分配佣金';
				}
				if($insertarr['balance_price']>0)$insertarr['balance_price'] = -$insertarr['balance_price'];
				break;
			case 'order':
				if (!$insertarr['balance_desc']){
					$insertarr['balance_desc'] = '订单'.$insertarr['order_sn'].'购物消费';
				}
				break;
		}
		//新增日志
		$value_array = array();
		$value_array['balance_memberid'] = $insertarr['balance_teamid'];
		$value_array['balance_membername'] = $insertarr['balance_teamname'];
		$value_array['balance_points'] = $insertarr['balance_price'];
		$value_array['balance_addtime'] = time();
		$value_array['balance_desc'] = $insertarr['balance_desc'];
		$value_array['balance_stage'] = $stage;
		$result = false;
		if($value_array['balance_price'] != '0'){
			$result = self::addMzBalanceLog($value_array);
		}
		if ($result){
			//更新mz_team内容
			Model('mz_team')->where(array('team_id'=>$insertarr['balance_teamid']))->update(array('team_balance'=>array('exp','team_balance + '.$value_array['balance_price'])));
			return true;
		}else {
			return false;
		}
	}
	/**
	 * 添加余额日志信息
	 *
	 * @param array $param 添加信息数组
	 */
	public function addMzBalanceLog($param) {
		if(empty($param)) {
			return false;
		}
		$result = $this->table('mz_balance_log')->insert($param);
		return $result;
	}
	
	/**
	 * 余额日志列表
	 *
	 * @param array $where 条件数组
	 * @param mixed $page   分页
	 * @param string $field   查询字段
	 * @param int $limit   查询条数
	 * @param string $order   查询条数
	 */
	public function getMzBalanceLogList($where, $field = '*', $page = 0, $limit = 0,$order = '', $group = '') {
	    if (is_array($page)){
	        if ($page[1] > 0){
	            return $this->table('mz_balance_log')->field($field)->where($where)->page($page[0],$page[1])->order($order)->group($group)->select();
	        } else {
	            return $this->table('mz_balance_log')->field($field)->where($where)->page($page[0])->order($order)->group($group)->select();
	        }
	    } else {
            return $this->table('mz_balance_log')->field($field)->where($where)->page($page)->order($order)->group($group)->select();
        }
	}
	/**
	  * 获得阶段说明文字
	  */
	public function getStage(){
	    $stage_arr = array();
		$stage_arr['allot'] = '小组分配';
		$stage_arr['order'] = '订单消费';
		return $stage_arr;
	}	
}
