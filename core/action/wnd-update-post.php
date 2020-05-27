<?php
namespace Wnd\Action;

use Wnd\Action\Wnd_Insert_Post;

class Wnd_Update_Post extends Wnd_Action_Ajax {

	/**
	 *
	 *@since 初始化
	 *@param 	array 	$_POST 		表单数据
	 *@param 	int 	$post_id 	文章id
	 *@return 	array
	 *更新文章
	 */
	public static function execute($post_id = 0): array{
		// 获取被编辑Post ID
		$_POST['_post_ID'] = $post_id ?: $_POST['_post_ID'];
		if ($_POST['_post_ID']) {
			return ['status' => 0, 'msg' => __('ID无效', 'wnd')];
		}

		return Wnd_Insert_Post::execute();
	}
}
