<?php
namespace Wndt\Action;

use Exception;
use Wndt\Model\Wndt_Rating;
use Wnd\Action\Wnd_Action;

/**
 *点评
 *
 */
class Wndt_Insert_Rating extends Wnd_Action {

	public function execute(): array{
		// 获取数据
		$post_id = $this->data['_post_ID'] ?? 0;
		$rating  = $this->data['rating'] ?? '';
		$content = $this->data['content'] ?? '';

		try {
			$review = new Wndt_Rating;
			$review->set_post_id($post_id);
			$review->set_rating($rating);
			$review->set_content($content);
			$review->rating();
		} catch (Exception $e) {
			return ['status' => 0, 'msg' => $e->getMessage()];
		}

		return ['status' => 1, 'msg' => '添加成功'];
	}
}
