<?php
/**
 * 小组模型
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

class mz_teamModel extends Model {
    public function __construct() {
        parent::__construct('mz_team');
    }
    /**
     * 获取奖金池小组列表
     * @param  array $condition 条件
     * @param  string $field    字段
     */
    public function getBonusTeamList($condition, $field = '*') {
        return $this->field($field)->where($condition)->order('team_integral desc')->limit(10)->select();
    }
    /**
     * 通过id获取单个小组信息
     * @param  int $id 小组id
     * @param  string $field 字段
     */
    public function getTeamInfoByID($id, $field = '*') {
        return $this->field($field)->where(array('team_id'=>$id))->find();
    }
    /**
     * 更新小组积分
     * @param  int $id 小组id
     */
    public function updateTeamIntegra($id) {
		$team = Model('mz_member')->field('SUM(integral) as sumintegral')->where(array('team_id'=>$id))->find();
        $this->where(array('team_id'=>$id))->update(array('team_integral'=>$team['sumintegral']));
    }
    /**
	 * 订单完成后，为小组分配利润
	 * @param array $order_info 订单信息
     */
    public function groupForProfitDistribution($order_info) {
		if($order_info['team_id'] && $order_info['distribution_amount'] > $order_info['wholesale_amount'] && $order_info['order_amount'] > $order_info['refund_amount']){
			$team = $this->getTeamInfoByID($order_info['team_id']);
			if($team){
				$profit = ($order_info['distribution_amount'] - $order_info['wholesale_amount'])*($order_info['order_amount'] - $order_info['refund_amount'])/$order_info['order_amount'];
				$mz_profit_rule = C("mz_profit_rule")?unserialize(C("mz_profit_rule")):array();
				//小组分配利润
				$balance_price = sprintf('%.2f', $profit*$mz_profit_rule['team']/100);
				if($balance_price > 0){
					Model('mz_balance')->saveMzBalanceLog('order',array('balance_teamid'=>$team['team_id'],'balance_teamname'=>$team['team_name'],'balance_price'=>$balance_price,'order_sn'=>$order_info['order_sn']));
					//更新小组统计
					Model('mz_statistical')->updateTeamStatistical($team['team_id'],$balance_price);
				}
				//小组推荐人分配利润
				$divided = sprintf('%.2f', $profit*$mz_profit_rule['recommend']/100);
				if($team['recommend_id'] && $divided > 0){
					$member = Model('member')->getMemberInfoByID($team['recommend_id'],'member_id,member_name');
					if($member){
						Model('predeposit')->changePd('mz_divided', array('member_id'=>$member['member_id'], 'member_name'=>$member['member_name'], 'order_sn'=>$order_info['order_sn'], 'amount'=>$divided));
					}
				}
				//奖金池分配利润
				$bonus = sprintf('%.2f', $profit*$mz_profit_rule['bonus']/100);
				if($bonus > 0){
					Model('mz_bonus')->saveMzBonusLogOrder($team,$bonus,$order_info['order_sn']);
				}
			}
		}
    }
}