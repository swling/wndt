<?php
// Exit if accessed directly
if (!defined('ABSPATH')) {
	exit;
}

/**
 *@since 2019.01.16 转移option选项
 *author：swling
 *email：tangfou@gmail.com
 */
if (!get_option('wndt')) {
	update_option('wndt', get_option('wndt_options'), 'no');
} else {
	delete_option('wndt_options');
}

/**
 *@since 2019.09.18 注册后台设置菜单
 */
function wndt_register_options_page()
{
	add_theme_page('主题配置', '主题配置', 'edit_theme_options', 'wndt', 'wndt_options');
}
add_action('admin_menu', 'wndt_register_options_page');

/**
 *@since 2019.09.18
 *设置表单
 */
function wndt_options()
{
	if ($_POST and current_user_can('administrator')) {
		check_admin_referer('wndt_update');

		// 按前缀筛选数组,过滤掉非指定数据
		foreach ($_POST as $key => $value) {
			if (false === strpos($key, 'wndt_')) {
				unset($_POST[$key]);
			}
		}
		unset($key, $value);

		// 更新设置
		update_option('wndt', array_merge(get_option('wndt', []), $_POST));
		echo '<div class="updated"><p>更新成功！</p></div>';
	}

	/**
	 *@since 2019.09.18
	 *获取参数
	 **/
	$wndt_logo = wndt_get_config('logo');
?>
	<div class="wrap">
		<h1>主题配置</h1>
		<form method="post" action="">
			<table class="form-table">
				<!--刷新设置-->
				<tr>
					<th>
						<h2>基础配置</h2>
					</th>
				</tr>

				<tr>
					<td>站点LOGO</td>
					<td>
						<input type="text" name="wndt_logo" min="1" value="<?php echo esc_html(stripslashes($wndt_logo)); ?>" class="large-text" />
					</td>
				</tr>

				<tr>
					<td>产品相册图片：</td>
					<td>
						<input type="number" name="wndt_gallery_picture_limit" min="1" value="<?php echo wndt_get_config('gallery_picture_limit'); ?>" required="required" /> *最多发布数量
					</td>
				</tr>

				<tr>
					<th>
						<h2>第三方登录</h2>
					</th>
				</tr>
				<tr>
					<td valign="top">QQ登录APP ID</td>
					<td>
						<input type="text" name="wndt_qq_appid" value="<?php echo wndt_get_config('qq_appid'); ?>" class="regular-text">
					</td>
				</tr>
				<tr>
					<td valign="top">QQ登录APP KEY</td>
					<td>
						<input type="text" name="wndt_qq_appkey" value="<?php echo wndt_get_config('qq_appkey'); ?>" class="regular-text">
					</td>
				</tr>
				<tr>
					<td>社交登录回调地址</td>
					<td>
						<input type="text" name="wndt_social_redirect_url" min="1" value="<?php echo wndt_get_config('social_redirect_url'); ?>" class="large-text" />
						<p><i>*不要忘了http:// 或 https://</i></p>
					</td>
				</tr>

				<tr>
					<th>
						<h2>其他信息</h2>
					</th>
				</tr>
				<tr>
					<td valign="top">ICP备案号</td>
					<td>
						<input type="text" name="wndt_icp" value="<?php echo wndt_get_config('icp'); ?>" class="regular-text">
					</td>
				</tr>

				<tr>
					<td valign="top">公安备案号</td>
					<td>
						<input type="text" name="wndt_wangan" value="<?php echo wndt_get_config('wangan'); ?>" class="regular-text">
					</td>
				</tr>

				<tr>
					<td valign="top">流量统计</td>
					<td>
						<textarea class="code" name="wndt_statistical_code" cols="40" rows="8" placeholder="流量统计代码"><?php echo esc_html(stripslashes(wndt_get_config('statistical_code'))); ?></textarea>
					</td>
				</tr>
			</table>
			<?php wp_nonce_field('wndt_update'); ?>
			<input type="submit" value="保存设置" class="button-primary" />
		</form>
	</div>

<?php }
