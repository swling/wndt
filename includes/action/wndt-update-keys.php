<?php

namespace Wndt\Action;

use Exception;
use Wnd\Action\Wnd_Action;

/**
 *点评
 *
 */
class Wndt_Update_Keys extends Wnd_Action {

	protected function execute(): array {
		// 获取数据
		$post_id = $this->data['post_id'] ?? 0;
		$keys  = $this->data['keys'] ?? '';

		// 使用换行符切分文本为数组
		$keys_array = explode("\n", $keys);
		$keys_array = array_map('trim', $keys_array);
		$aciton  = update_post_meta($post_id, 'secret_keys', $keys_array);
		if ($aciton) {
			return ['status' => 1, 'msg' => 'keys 更新成功'];
		} else {
			return ['status' => 0, 'msg' => 'keys 更新失败'];
		}
	}

	protected function check() {
		$post_id = $this->data['post_id'] ?? 0;
		if (!current_user_can('edit_post', $post_id)) {
			throw new Exception(__('权限错误', 'wnd'));
		}
	}
}
