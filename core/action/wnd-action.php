<?php
namespace Wnd\Action;

/**
 *@since 2019.10.27
 *操作基类
 */
abstract class Wnd_Action {

	/**
	 *获取全局变量并选择model执行
	 */
	abstract public static function execute();
}
