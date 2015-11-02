<?php defined('InShopNC') or exit('Access Invalid!');?>
<script src="http://src.qinqin.net/js/jquery.js"></script>
<style>
.clear{clear:both;}
.new_center{padding:0 5%;}
.new_center .flow_step{padding:20px 0;border-bottom:1px dotted #bfbfbf;height:24px;line-height:24px;}
.new_center .flow_step span{width:24px;height:24px;float:left;border-radius:12px;background-color:#f69;text-align:center;color:#FFFFFF;}
.new_center .flow_step p{float:left;color:#f69;padding-left:5px;margin:0;}

.find_pwd_form{padding:20px 0;}
.find_pwd_form .item{margin:0 auto 20px;width:90%;}
.find_pwd_form .item input{height:32px;width:90%;line-height:32px;font-size:14px;color:#999;-webkit-border-radius:2px;border-radius:2px;padding:0 10px;border:1px solid #ccc;}
.find_pwd_form .item .checkcode{float:left;width:40%;}
.find_pwd_form .item .checkImage{float:left;margin-left:10px;}
.find_pwd_form .errorMessage{margin:-15px 5% 10px;display:none;color:#f69;}
.find_pwd_form .item .submit{background:#f69;cursor:pointer;border:1px solid #f69;color:#fff;width:40%;}
.find_pwd_form .success{text-align:center;line-height:50px;}
.find_pwd_form .success span{font-size:18px;padding:11px 0 11px 60px;background:url(http://www.qinqin.net/templates/default/images/auditing.png) no-repeat;}
.find_pwd_form .success a{color:#f69;text-decoration:underline;}
.big_btn, .big_btnr, .small_btn, .small_btnr{background:url(http://www.qinqin.net/templates/default/images/btn_pic121201.png) no-repeat;display:inline-block;position:relative;}
.big_btn{background-position:0 0;height:40px;line-height:38px;text-align:center;cursor:pointer;color:#fff;font-size:16px;font-weight:bold;padding-left:6px;}
.big_btn .big_btnr{background-position:-394px 0;height:40px;right:-6px;top:0;width:6px;position:absolute;}
.find_pwd_form .big_btn{width:172px;}
.find_pwd_form .big_btn a{color:#fff;text-decoration:none;}
.find_pwd_form .msgcode input{float:left;}
.find_pwd_form .msgcode .codetxt{width:140px;}
.find_pwd_form .msgcode .submit{margin-left:10px;margin-right:10px;}
.find_pwd_form .msgcode span{height:32px;line-height:32px;float:left;cursor:pointer;color:#f69;}
.find_pwd_form .errorMessageSendsms{margin-top:10px;}

.find_pwd_form .resetPwd{margin-bottom:30px;}
.find_pwd_form .pw_safe{margin-top:8px;height:22px;display:none;margin-bottom:-30px;}
.find_pwd_form .pw_safe .txt{float:left;width:58px;color:#999;line-height:14px;}
.find_pwd_form .pw_safe .pw_strength{float:left;width:129px;background-color:#ccc;height:14px;}
.find_pwd_form .pw_safe .pw_strength span{color:#fff;display:inline;float:left;height:14px;line-height:14px;text-align:center;width:42px;border-right:1px solid #FFF;}
.find_pwd_form .pw_safe .pw_strength_color{background:#f69;}
.find_pwd_form .resetPwd input{width:210px;}
.find_pwd_form label{float:left;line-height:32px;width:90px;text-align:right;}
.find_pwd_form .resetPwd input{width:210px;}
.find_pwd_form .resetPwd .big_btn input{outline:0;color:#fff;font-size:16px;font-weight:bold;height:40px;line-height:38px;width:140px;padding:0;margin:0;cursor:pointer;background:0;border:0;}
</style>
<div class="new_center">
	<div class="flow_step">
		<?php if($output['step'] == 1){ ?>
		<span>1</span><p>找回密码</p>
		<?php }elseif($output['step'] == 2){ ?>
		<span>2</span><p>发送成功</p>
		<?php }elseif($output['step'] == 3){ ?>
		<span>3</span><p>重置密码</p>
		<?php }elseif($output['step'] == 4){ ?>
		<span>4</span><p>完成</p>
		<?php } ?>
	</div>
	<div class="find_pwd_form">
		<?php if($output['step'] == 1){ ?>
		<form action="index.php?act=login&op=forget_password&step=2" method="POST" id="forget_password_form">
		<?php Security::getToken();?>
		<input type="hidden" name="form_submit" value="ok" />
		<input name="nchash" type="hidden" value="<?php echo getNchash();?>" />
		<div class="item">
			<input id="username" name="username" class="username" type="text" placeholder="邮箱或手机号">
		</div>
		<div class="item">
			<input id="captcha" name="captcha" class="checkcode" type="text" maxlength="4" placeholder="验证码">
			<a href="javascript:void(0);" class="checkImage" onclick="javascript:document.getElementById('codeimage').src='index.php?act=seccode&op=makecode&nchash=<?php echo getNchash();?>&t=' + Math.random();"><img src="index.php?act=seccode&op=makecode&nchash=<?php echo getNchash();?>" title="<?php echo $lang['login_index_change_checkcode'];?>" name="codeimage" border="0" id="codeimage"></a>
			<div class="clear"></div>
		</div>
		<div class="errorMessage"></div>
		<div class="item">
			<input type="button" class="submit" value="下一步" name="Submit" id="Submit">
		</div>
		<input type="hidden" value="<?php echo $output['ref_url']?>" name="ref_url">
		</form>
		<script>
		function r_check_captcha(){
			$.ajax({
				type:'get',
				data:{'captcha':$('#captcha').val()},
				url:'index.php?act=seccode&op=check&nchash=<?php echo getNchash();?>',
				success:function(data){
					if(data == 'false'){
						r_err('验证码错误');
					}else{
						r_ok();
						$("#forget_password_form").submit();
					}
				},
				complete:function(data){
					if(data.responseText == 'false'){
						document.getElementById('codeimage').src='index.php?act=seccode&op=makecode&nchash=<?php echo getNchash();?>&t=' + Math.random();
					}
				}
			});
		}
		$(function(){
			$('#Submit').click(function(){
				var username = $('#username').val();
				var captcha = $('#captcha').val();
				if(username == ''){
					r_err('请输入手机号或邮箱！');
				}else if(captcha == ''){
					r_err('请输入验证码！');
				}else{
					var is_mobile = /^1\d{10}$/.test(username);
					var is_email = /^([a-zA-Z0-9]+[_|\_|\.]?)*[a-zA-Z0-9]+@([a-zA-Z0-9]+[_|\_|\.]?)*[a-zA-Z0-9]+\.[a-zA-Z]{2,3}$/.test(username);
					if(!is_mobile && !is_email){
						r_err('请填写正确的手机号或邮箱！');
					}else if(is_email){
						$.ajax({
							type:'get',
							data:{'email':username},
							url:'index.php?act=login&op=check_email',
							success:function(data){
								if(data == 'true'){
									r_err('你的邮箱还没有在亲亲网注册！');
								}else{
									r_check_captcha();
								}
							}
						});
					}else if(is_mobile){
						$.ajax({
							type:'get',
							data:{'mobile':username},
							url:'index.php?act=login&op=check_mobile',
							success:function(data){
								if(data == 'true'){
									r_err('你的手机还没有在亲亲网注册！');
								}else{
									r_check_captcha();
								}
							}
						});
					}else{
						r_err('请填写正确的手机号或邮箱！');
					}
				}
			});
		});
		</script>
		<?php }elseif($output['step'] == 2){ ?>
		<?php if($output['type'] == 'email'){ ?>
		<div class="success">
			<span>操作成功！</span><br>
			一封邮件已发送至你的邮箱 <a href="<?php echo $output['to_email']?>" target="_parent"><?php echo $output['email']?></a>，请点击邮件中的链接重置你的密码。<br>
			<div class="big_btn">
				<em class="big_btnr"></em><a href="<?php echo $output['to_email']?>" target="_parent">去邮箱查看</a>
			</div>
		</div>
		<?php }else{ ?>
		<div class="item msgcode">
			<input id="captcha" class="codetxt" type="text" maxlength="6" placeholder="短信验证码">
			<input id="submit_btn" type="button" class="submit" value="确认">
			<span class="time_box"><b>60</b>秒后可重新发送</span>
			<div class="clear"></div>
		</div>
		<div class="errorMessage errorMessageSendsms"></div>
		<script>
		var ALLOW_SEND = false;
		$(function(){
			function StepTimes() {
				$num = parseInt($('.msgcode .time_box b').text());
				$num = $num - 1;
				$('.msgcode .time_box b').text($num);
				if ($num <= 0) {
					ALLOW_SEND = !ALLOW_SEND;
					$('.msgcode .time_box').text('重新发送');
				} else {
					setTimeout(StepTimes,1000);
				}
			}
			StepTimes();
			$('.msgcode .time_box').click(function(){
				if (!ALLOW_SEND) return;
				ALLOW_SEND = !ALLOW_SEND;
				$.ajax({
					type:'get',
					data:{'uid':'<?php echo $output['uid']?>','type':'send'},
					url:'index.php?act=login&op=forget_password_mobile',
					success:function(data){
						if(data){
							ALLOW_SEND = !ALLOW_SEND;
							r_err(data);
						}else{
							$('.msgcode .time_box').html('<b>60</b>秒后可重新发送');
							StepTimes();
						}
					}
				});
			});
		});
		$(function(){
			$('#submit_btn').click(function(){
				var captcha = $('#captcha').val();
				if(captcha == ''){
					r_err('验证码为空！');
				}else{
					$.ajax({
						type:'get',
						data:{'uid':'<?php echo $output['uid']?>','captcha':captcha,'type':'check'},
						url:'index.php?act=login&op=forget_password_mobile',
						success:function(data){
							if(data){
								r_err(data);
							}else{
								window.location.href="index.php?act=login&op=forget_password&step=3&uid=<?php echo $output['uid']?>";
							}
						}
					});
				}
			});
		});
		</script>
		<?php } ?>
		<?php }elseif($output['step'] == 3){ ?>
		<form action="index.php?act=login&op=forget_password&step=4" method="POST" id="forget_password_form">
		<?php Security::getToken();?>
		<input type="hidden" name="form_submit" value="ok" />
		<input name="nchash" type="hidden" value="<?php echo getNchash();?>" />
		<input name="uid" type="hidden" value="<?php echo $output['uid']?>" />
		<div class="item resetPwd">
			<input id="new_password" name="new_password" class="newPwd" type="password" placeholder="新密码">
			<div class="pw_safe">
				<div class="pw_strength">
					<span class="strength_l">弱</span>	
					<span class="strength_m">中</span>
					<span class="strength_h">强</span>
				</div>
			</div>
		</div>
		<div class="item resetPwd">
			<input id="confirm_password" name="confirm_password" class="newPwd" type="password" placeholder="请确认密码">
		</div>
		<div class="errorMessage"></div>
		<div class="item resetPwd">
			<div class="big_btn">
				<em class="big_btnr"></em>
				<input id="submit_btn" type="button" value="修改密码">
			</div>
		</div>
		<input type="hidden" value="<?php echo $output['ref_url']?>" name="ref_url">
		</form>
		<script>
		$(function(){
			$('#new_password').on('input',function(){
				var value = $(this).val();
				var len = value.length;
				if(len >= 6 && len <= 20){
					$('.pw_safe').show();
					$('.pw_strength').find('span').removeClass('pw_strength_color');
					if(len >= 6)$('.strength_l').addClass('pw_strength_color');
					if(len >= 10 && ((/([a-zA-Z]+\d+|\d+[a-zA-Z]+)/.test(value)) || (/([^a-zA-Z0-9]+\d+|\d+[^a-zA-Z0-9]+)/.test(value)) || (/([a-zA-Z]+[^a-zA-Z0-9]+|[^a-zA-Z0-9]+[a-zA-Z]+)/.test(value))))$('.strength_m').addClass('pw_strength_color');
					if(len >= 15 && (/([a-zA-Z]+\d+[^a-zA-Z0-9]+|\d+[a-zA-Z]+[^a-zA-Z0-9]+|[^a-zA-Z0-9]+[a-zA-Z]+\d+|[^a-zA-Z0-9]+\d+[a-zA-Z]+|\d+[^a-zA-Z0-9]+[a-zA-Z]+|[a-zA-Z]+[^a-zA-Z0-9]+\d+)/.test(value)))$('.strength_h').addClass('pw_strength_color');
				}else{
					$('.pw_safe').hide();
				}
			});
			$('#submit_btn').click(function(){
				var new_password = $('#new_password').val();
				var confirm_password = $('#confirm_password').val();
				var len = new_password.length;
				if(len == 0){
					r_err('请输入密码');
				}else if(len < 6){
					r_err('新密码不能小于6位');
				}else if(len > 20){
					r_err('新密码不能大于20位');
				}else if(confirm_password == ''){
					r_err('请确认密码');
				}else if(confirm_password != new_password){
					r_err('两次密码输入不一致');
				}else{
					r_ok();
					$("#forget_password_form").submit();
				}
			});
		});
		</script>
		<?php }else{ ?>
		<div class="success">
			<span>恭喜你，密码修改成功！</span><br>	
			<a href="http://m.qinqin.net/tmpl/member/login.html" target="_parent">立即登录</a>亲亲网，去发现、收藏、分享你的喜爱的商品吧！
		</div>
		<?php } ?>
	</div>
</div>
<script>
function r_ok(){
	$('.errorMessage').text('').hide();
}
function r_err(msg){
	$('.errorMessage').text(msg).show();
}
</script>