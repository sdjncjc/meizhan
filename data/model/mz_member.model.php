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

class mz_memberModel extends Model {
    public function __construct() {
        parent::__construct('mz_member');
    }
    /**
     * 获取单个会员信息
     * @param  [type] $condition [description]
     * @param  array  $extend    [description]
     * @param  string $field     [description]
     * @return [type]            [description]
     */
    public function getMemberInfo($condition, $field = '*', $extend = array()) {
        $result = $this->field($field)->where($condition)->find();
        if (!empty($result)) {
	        //获取用户基本信息
	        if (in_array('member',$extend)) {
	        	$result['extend_member_info'] = Model("member")->getMemberInfo(array('member_id'=>$result['member_id']),'member_name,member_truename,member_avatar,member_sex');
	        }
	        // 获取团队信息
	        if (in_array('mz_team', $extend)) {
	        	$result['extend_team_info'] = Model("mz_team")->where(array('team_id'=>$result['team_id']))->find();
	        }
        }
        return $result;
    }
    /**
     * 获取会员列表
     * @param  [type]  $condition [description]
     * @param  string  $field     [description]
     * @param  array   $extend    [description]
     * @param  string  $order     [description]
     * @param  string  $pagesize  [description]
     * @param  string  $limit     [description]
     * @param  boolean $master    [description]
     * @return [type]             [description]
     */
    public function getAllMember($condition, $field = '*', $extend = array(), $order = 'order_id desc', $pagesize = '',$limit = '', $master = false){
        $list = $this->field($field)->where($condition)->page($pagesize)->order($order)->limit($limit)->master($master)->select();
        if (!empty($list)) {
            foreach ($list as $key => $value) {
                $value = $this->getMemberInfo($condition, $field, $extend);
                $list[$key] = $value;
            }
        }
        return $list;
    }
    /**
     * 添加团队会员
     * @param [type] $data [description]
     */
    public function addTeamUser($data){
        return $this->insert($data);
    }
    /**
     * 编辑会员
     * @param array $condition
     * @param array $data
     */
    public function editTeamUser($condition, $data) {
        $update = $this->where($condition)->update($data);
        return $update;
    }
}