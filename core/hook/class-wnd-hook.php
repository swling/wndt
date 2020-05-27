<?php
namespace Wnd\Hook;

use Wnd\Hook\Wnd_Add_Action;
use Wnd\Hook\Wnd_Add_Action_WP;
use Wnd\Hook\Wnd_Add_Filter;
use Wnd\Hook\Wnd_Add_Filter_WP;
use Wnd\Utility\Wnd_Singleton_Trait;

/**
 *Wnd Default Hook
 */
class Wnd_Hook {

	use Wnd_Singleton_Trait;

	private function __construct() {
		// Wnd Action Hook
		Wnd_Add_Action::instance();

		// WP Action Hook
		Wnd_Add_Action_WP::instance();

		// Wnd Action Hook
		Wnd_Add_Filter::instance();

		// WP Action Hook
		Wnd_Add_Filter_WP::instance();
	}
}
