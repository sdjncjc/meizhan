<?php
/**
 * 小组会员模型
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
     * 获取单个团队信息
     * @param  [type] $condition [description]
     * @param  string $field     [description]
     * @return [type]            [description]
     */
    public function getTeamInfo($condition, $field = '*') {
        $result = $this->field($field)->where($condition)->find();
        return $result;
    }
    /**
     * 获取团队信息
     * @param  [type]  $condition [description]
     * @param  string  $field     [description]
     * @param  string  $order     [description]
     * @param  string  $pagesize  [description]
     * @param  string  $limit     [description]
     * @param  boolean $master    [description]
     * @return [type]             [description]
     */
    public function getAllTeam($condition, $field = '*', $order = 'order_id desc', $pagesize = '',$limit = '', $master = false){
        $list = $this->field($field)->where($condition)->page($pagesize)->order($order)->limit($limit)->master($master)->select();
        return $list;
    }
    public function addTeam($data){
        $insert = $this->insert($data);
        return $insert;
    }
}