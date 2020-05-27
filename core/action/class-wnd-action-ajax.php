<?php
namespace Wnd\Action;

/**
 *@since 2019.10.02
 *Ajax操作基类
 */
abstract class Wnd_Action_Ajax {

	/**
	 *获取全局变量并选择model执行
	 */
	abstract public static function execute(): array;
}
