<?php defined('InShopNC') or exit('Access Invalid!');?>
<!DOCTYPE html>
<html>
<head>
<meta http-equiv="content-type" content="text/html; charset=UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="black">
<meta name="format-detection" content="telephone=no">
<title><?php echo $output['brand']['brand_name']; ?></title>
<link rel="stylesheet" type="text/css" href="<?php echo RESOURCE_SITE_URL; ?>/wap/css/reset.css">
<link rel="stylesheet" type="text/css" href="<?php echo RESOURCE_SITE_URL; ?>/wap/css/main.css">
<link rel="stylesheet" type="text/css" href="<?php echo RESOURCE_SITE_URL; ?>/wap/css/index.css">
</head>
<body>
<div class="main" id="main-container">
	<div class="index_block home1" style="margin-top:0;border:0px;">
		<div class="content">
			<div class="item">
				<img src="<?php echo $output['brand']['brand_image_url']; ?>" alt="">
			</div>
		</div>
	</div>
	<?php if(!empty($output['brand']['goods'])){ ?>
	<div class="index_block goods">
		<div class="content">
		<?php foreach($output['brand']['goods'] as $item){ ?>
			<div class="goods-item">
				<a nctype="btn_item" href="javascript:;" data-type="goods" data-data="<?php echo $item['goods_id']; ?>">
					<?php if ($item['goods_state'] == 0 || $item['goods_storage'] <= 0) { ?>
					<img class="no_stock" src="http://www.qinqin.net/templates/default/images/no_stock.png" />
					<?php }?>
					<div class="goods-item-pic"><img src="<?php echo $item['goods_image_url']; ?>" alt=""></div>
					<div class="goods-item-name"><?php echo $item['goods_name']; ?></div>
					<div class="goods-item-price">￥<?php echo $item['goods_promotion_price']; ?></div>
				</a>
			</div>
		<?php } ?>
		</div>
	</div>
	<?php } ?>
</div>
<script type="text/javascript" src="<?php echo RESOURCE_SITE_URL; ?>/js/mobile/zepto.min.js" charset="utf-8"></script>
<script type="text/javascript" src="<?php echo RESOURCE_SITE_URL; ?>/js/mobile/swipe.js" charset="utf-8"></script>
<script type="text/javascript">
$(function() {
	$('[nctype="btn_item"]').on('click', function() {
		var type = $(this).attr('data-type');
		var data = $(this).attr('data-data');
		if(typeof window.android != 'undefined') {
			window.android.mb_special_item_click(type, data);
		}
		return false;
	});
});
</script>
</body>
</html>
