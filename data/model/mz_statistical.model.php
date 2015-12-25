<?php
/**
 * 统计模型
 *
 * 
 *
 *
 * @copyright  Copyright (c) 2007-2013 ShopNC Inc. (http://www.shopnc.net)
 * @license    http://www.shopnc.net
 * @link       http://www.shopnc.net
 * @since      File available since Release v1.1
 */
defined('InShopNC') or exit('Access Invalid!');

class mz_statisticalModel extends Model {
    public function __construct() {
        parent::__construct('mz_statistical');
    }
    /**
     * 获取单个统计信息
     * @param  array $condition 条件
     * @param  string $field 字段
     */
    public function getStatisticalInfo($condition, $field = '*') {
        return $this->field($field)->where($condition)->find();
    }
    /**
     * 更新统计信息
     * @param  int $stat_id 信息id
     * @param  int $stat_amount 金额
     */
    public function updateStatistical($stat_id, $stat_amount) {
		if($stat_amount > 0)$this->where(array('stat_id'=>$stat_id))->update(array('stat_amount'=>array('exp','stat_amount + '.$stat_amount)));
    }
    /**
     * 新增统计信息
     * @param  int $stat_team_id 小组id
     * @param  int $stat_amount 金额
     */
    public function insertStatistical($stat_team_id, $stat_amount) {
		if($stat_team_id && $stat_amount > 0)$this->insert(array('stat_team_id'=>$stat_team_id,'stat_time'=>date('Y-m'),'stat_amount'=>$stat_amount));
    }
    /**
     * 订单完成后更新小组统计
     * @param  int $team_id 小组id
     * @param  int  $stat_amount   金额
     */
    public function updateTeamStatistical($team_id,$stat_amount) {
		$mz_statistical = $this->getStatisticalInfo(array('stat_team_id'=>$team_id,'stat_time'=>date('Y-m')), 'stat_id');
		if($mz_statistical){
			$this->updateStatistical($mz_statistical['stat_id'], $stat_amount);
		}else{
			$this->insertStatistical($team_id, $stat_amount);
		}
    }
}