<?php
namespace Wndt\Model;

use Exception;

/**
 *@since 2019.09.25
 *文章发布管理权限：Post permission control
 */
class Wndt_PPC_Post extends Wndt_PPC {

	/**
	 *检测文章发布权限
	 **/
	public function check_insert() {
		if (!wnd_is_manager()) {
			throw new Exception('仅管理员可发布文章');
		}
	}
}
