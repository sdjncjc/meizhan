<?php
/**
 * 奖金日志管理
 *
 * @copyright  Copyright (c) 2007-2013 ShopNC Inc. (http://www.shopnc.net)
 * @license    http://www.shopnc.net
 * @link       http://www.shopnc.net
 * @since      File available since Release v1.1
 */
defined('InShopNC') or exit('Access Invalid!');

class mz_bonusModel extends Model{

	public function saveMzBonusLogAllot($team,$bonus,$bp_id){
		if (empty($team) || $bonus == 0 || $bp_id == 0){
			return false;
		}
		$pool = $this->getMzBonusPoolInfo(array('bp_id'=>$bp_id));
		if(!empty($pool)){
			$bonus = abs($bonus);
			//新增日志
			$value_array = array();
			$value_array['bonus_bp_id'] = $bp_id;
			$value_array['bonus_bp_name'] = $team['city_school'].($team['team_type'] ? '地区' : '校区');
			$value_array['bonus_team_id'] = $team['team_id'];
			$value_array['bonus_team_name'] = $team['team_name'];
			$value_array['bonus_price'] = -$bonus;
			$value_array['bonus_addtime'] = time();
			$value_array['bonus_desc'] = '向'.$team['team_name'].'分配利润';
			$value_array['bonus_stage'] = 'allot';
			$result = self::addMzbonusLog($value_array);
			if ($result && $bonus != 0){
				$result = $this->table('mz_bonus_pool')->where(array('bp_id'=>$bp_id))->update(array('bp_price'=>array('exp','bp_price - '.$bonus)));
				if ($result){
					return Model('mz_balance')->saveMzBalanceLog('bonus',array('balance_teamid'=>$team['team_id'],'balance_teamname'=>$team['team_name'],'balance_price'=>$bonus,'bp_name'=>$value_array['bonus_bp_name']));
				}
			}
		}
		return false;
	}
	public function saveMzBonusLogOrder($team,$bonus,$order_sn){
		if (empty($team) || $bonus == 0){
			return false;
		}
		$pool = $this->getMzBonusPoolInfo(array('city_school_id'=>$team['city_school_id'], 'bp_type'=>$team['team_type']));
		if(!$pool){
			$pool['bp_id'] = $this->table('mz_bonus_pool')->insert(array('provinceid'=>$team['provinceid'],'province'=>$team['province'],'city_school_id'=>$team['city_school_id'],'city_school'=>$team['city_school'],'bp_type'=>$team['team_type']));
		}
		if($pool['bp_id']){
			//新增日志
			$value_array = array();
			$value_array['bonus_bp_id'] = $pool['bp_id'];
			$value_array['bonus_bp_name'] = $team['city_school'].($team['team_type'] ? '地区' : '校区');
			$value_array['bonus_team_id'] = $team['team_id'];
			$value_array['bonus_team_name'] = $team['team_name'];
			$value_array['bonus_price'] = $bonus;
			$value_array['bonus_addtime'] = time();
			$value_array['bonus_desc'] = '订单'.$order_sn.'购物消费';
			$value_array['bonus_stage'] = 'order';
			$result = self::addMzbonusLog($value_array);
			if ($result && $bonus != 0){
				return $this->table('mz_bonus_pool')->where(array('bp_id'=>$pool['bp_id']))->update(array('bp_price'=>array('exp','bp_price + '.$bonus)));
			}
		}
		return false;
	}
	/**
	 * 添加奖金日志信息
	 *
	 * @param array $param 添加信息数组
	 */
	public function addMzbonusLog($param) {
		if(empty($param)) {
			return false;
		}
		$result = $this->table('mz_bonus_log')->insert($param);
		return $result;
	}
    /**
     * 获取单个奖金池信息
     * @param  array $condition 条件
     * @param  string $field 字段
     */
    public function getMzBonusPoolInfo($condition, $field = '*') {
        return $this->table('mz_bonus_pool')->field($field)->where($condition)->find();
    }
	
	/**
	 * 奖金池列表
	 *
	 * @param array $where 条件数组
	 * @param mixed $page   分页
	 * @param string $field   查询字段
	 * @param int $limit   查询条数
	 * @param string $order   查询条数
	 */
	public function getMzBonusPoolList($where, $field = '*', $page = 0, $limit = 0,$order = '', $group = '') {
	    if (is_array($page)){
	        if ($page[1] > 0){
	            return $this->table('mz_bonus_pool')->field($field)->where($where)->page($page[0],$page[1])->order($order)->group($group)->select();
	        } else {
	            return $this->table('mz_bonus_pool')->field($field)->where($where)->page($page[0])->order($order)->group($group)->select();
	        }
	    } else {
            return $this->table('mz_bonus_pool')->field($field)->where($where)->page($page)->order($order)->group($group)->select();
        }
	}
	
	/**
	 * 奖金日志列表
	 *
	 * @param array $where 条件数组
	 * @param mixed $page   分页
	 * @param string $field   查询字段
	 * @param int $limit   查询条数
	 * @param string $order   查询条数
	 */
	public function getMzBonusLogList($where, $field = '*', $page = 0, $limit = 0,$order = '', $group = '') {
	    if (is_array($page)){
	        if ($page[1] > 0){
	            return $this->table('mz_bonus_log')->field($field)->where($where)->page($page[0],$page[1])->order($order)->group($group)->select();
	        } else {
	            return $this->table('mz_bonus_log')->field($field)->where($where)->page($page[0])->order($order)->group($group)->select();
	        }
	    } else {
            return $this->table('mz_bonus_log')->field($field)->where($where)->page($page)->order($order)->group($group)->select();
        }
	}
	/**
	  * 获得阶段说明文字
	  */
	public function getStage(){
	    $stage_arr = array();
		$stage_arr['allot'] = '奖金池分配';
		$stage_arr['order'] = '订单消费';
		return $stage_arr;
	}	
	/**
	 * 通过地区id获得奖金池中分配信息
     * @param  int $id 奖金池id
	 */
	public function getBonusTeams($id){
		$pool = $this->getMzBonusPoolInfo(array('bp_id'=>intval($id)));
        if (empty($pool)) {
            return false;
        }
		$teams = Model('mz_team')->getBonusTeamList(array('city_school_id'=>$pool['city_school_id'],'team_type'=>$pool['bp_type']));
		if($teams){
			$mz_bonus_rule = C("mz_bonus_rule")?unserialize(C("mz_bonus_rule")):array();
			foreach($teams as $k=>$v){
				$teams[$k]['allot_price'] = sprintf('%.2f', $pool['bp_price']*$mz_bonus_rule[$k]/100);
			}
		}
		return $teams;
	}	
	/**
	 * 通过地区id获得奖金池中小组分配利润
     * @param  int $id 奖金池id
	 */
	public function getBonusTeamsAllot($id){
		$pool = $this->getMzBonusPoolInfo(array('bp_id'=>intval($id)));
        if (empty($pool)) {
            return '参数错误';
        }
		if(date('Y-m') == date('Y-m', $pool['allot_time'])){
			return '该奖金池已分配';
		}
		$teams = Model('mz_team')->getBonusTeamList(array('city_school_id'=>$pool['city_school_id'],'team_type'=>$pool['bp_type']));
		if($teams){
			$mz_bonus_rule = C("mz_bonus_rule")?unserialize(C("mz_bonus_rule")):array();
			foreach($teams as $k=>$v){
				$allot_price = sprintf('%.2f', $pool['bp_price']*$mz_bonus_rule[$k]/100);
				$this->saveMzBonusLogAllot($v,$allot_price,$pool['bp_id']);
			}
		}
		return false;
	}	
}
