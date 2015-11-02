<?php defined('InShopNC') or exit('Access Invalid!');?>
<style>
.product-infor{margin-bottom:0px;}
.com_info{height:24px;width:90%;padding:12px 5%;line-height:28px;background-color:#F3F4F8;border-top:#ddd solid 1px;border-bottom:#ddd solid 1px;color:#333333;}
.com_info em{color:#FF4E88;font-size:1.5em;margin-right:10px;}
.evaluation-star, .evaluation-star-gray{width:24px;height:24px;-moz-background-size:24px 24px;background-size:24px 24px;}
.ncs-commend-floor{width:90%;padding:10px 5% 5px;border-bottom:#ddd solid 1px;color:#333333;}
.ncs-commend-floor div{padding:5px 0;}
.ncs-commend-floor .user{height:28px;line-height:28px;}
.ncs-commend-floor .user img{max-width:28px;max-height:28px;margin-top:expression(28-this.height/2);*margin-top:expression(14-this.height/2);border-radius:14px;float:left;margin-right:5px;}
.ncs-commend-floor .user time{color:#FF4E88;float:right;}
.ncs-commend-floor .raty{line-height:20px;height:20px;}
.ncs-commend-floor .raty em{color:#FF4E88;margin-left:10px;}
.raty .evaluation-star, .raty .evaluation-star-gray{width:20px;height:20px;-moz-background-size:20px 20px;background-size:20px 20px;}
.ncs-commend-floor .content{line-height:20px;}
.ncs-commend-floor .explain{line-height:20px;padding-left:10%;color:#999999;}
.ncs-commend-floor .image{float:left;}
.ncs-commend-floor .image a{width:50px;height:50px;margin-right:5px;float:left;display:inline-block;}
.ncs-commend-floor .image img{max-width:50px;max-height:50px;margin-top:expression(50-this.height/2);*margin-top:expression(25-this.height/2)/*IE6,7*/;}
.ncs-norecord{text-align:center;line-height:50px;}
</style>
<div class="com_info">
	<span class="fleft">
		<em class="fleft"><?php echo sprintf("%.1f", $output['goods_info']['evaluation_good_star']);?></em>
		<?php for($s = 1;$s<=5;$s++){?>
		<span class="evaluation-star<?php echo $s<=$output['goods_info']['evaluation_good_star'] ? '' : '-gray';?> fleft"></span>
		<?php }?>
	</span>
	<span class="fright"><?php echo $output['goods_info']['evaluation_count'];?>人综合评价</span>
</div>
<?php if(!empty($output['goodsevallist']) && is_array($output['goodsevallist'])){?>
<?php foreach($output['goodsevallist'] as $k=>$v){?>
<div class="ncs-commend-floor">
    <div class="user">
		<img src="<?php echo getMemberAvatarForID($v['geval_frommemberid']);?>">
		<span>
		<?php if($v['geval_isanonymous'] == 1){?>
		<?php echo str_cut($v['geval_frommembername'],2).'***';?>
		<?php }else{?>
		<?php echo $v['geval_frommembername'];?>
		<?php }?>
		</span>
		<time pubdate="pubdate">[<?php echo @date('Y-m-d',$v['geval_addtime']);?>]</time>
    </div>
    <div class="raty">
		<?php for($s = 1;$s<=5;$s++){?>
		<span class="evaluation-star<?php echo $s<=$v['geval_scores'] ? '' : '-gray';?> fleft"></span>
		<?php }?>
		<em><?php echo sprintf("%.1f", $v['geval_scores']);?></em>
	</div>
    <div class="content"><?php echo $v['geval_content'];?></div>
    <?php if (!empty($v['geval_explain'])){?>
    <div class="explain">回复：<?php echo $v['geval_explain'];?></div>
    <?php } ?>
    <?php if(!empty($v['geval_image'])) {?>
    <div class="image">
    	<?php $image_array = explode(',', $v['geval_image']);?>
        <?php foreach ($image_array as $value) { ?>
        <a target="_blank" href="<?php echo snsThumb($value, 1024);?>">
            <img src="<?php echo snsThumb($value);?>">
        </a>
        <?php } ?>
    </div>
    <?php } ?>
	<div style="clear:both;"></div>
</div>
<?php }?>
<?php }else{?>
<div class="ncs-norecord">暂无评论</div>
<?php }?>
