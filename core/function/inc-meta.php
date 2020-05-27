<?php

//############################################################################ 用户字段增量函数
function wnd_inc_user_meta($user_id, $meta_key, $val = 1, $min_zero = false) {
	if (!is_numeric($val)) {
		return;
	}

	$old_user_meta = get_user_meta($user_id, $meta_key, 1);
	$old_user_meta = $old_user_meta ? $old_user_meta : 0;
	$new_user_meta = $old_user_meta + $val;

	// 不为负数
	if ($min_zero and $new_user_meta < 0) {
		$new_user_meta = 0;
	}

	return update_user_meta($user_id, $meta_key, $new_user_meta);
}

//############################################################################ 用户数组字段增量函数
function wnd_inc_wnd_user_meta($user_id, $meta_key, $val = 1, $min_zero = false) {
	if (!is_numeric($val)) {
		return;
	}

	$old_user_meta = wnd_get_user_meta($user_id, $meta_key);
	$old_user_meta = $old_user_meta ? $old_user_meta : 0;
	$new_user_meta = $old_user_meta + $val;

	// 不为负数
	if ($min_zero and $new_user_meta < 0) {
		$new_user_meta = 0;
	}

	return wnd_update_user_meta($user_id, $meta_key, $new_user_meta);
}

//############################################################################ 文章字段增量函数
function wnd_inc_post_meta($post_id, $meta_key, $val = 1, $min_zero = false) {
	if (!is_numeric($val)) {
		return;
	}

	$old_post_meta = get_post_meta($post_id, $meta_key, 1);
	$old_post_meta = $old_post_meta ? $old_post_meta : 0;
	$new_post_meta = $old_post_meta + $val;

	// 不为负数
	if ($min_zero and $new_post_meta < 0) {
		$new_post_meta = 0;
	}

	return update_post_meta($post_id, $meta_key, $new_post_meta);
}

//############################################################################ 文章数组字段增量函数
function wnd_inc_wnd_post_meta($post_id, $meta_key, $val = 1, $min_zero = false) {
	if (!is_numeric($val)) {
		return;
	}

	$old_post_meta = wnd_get_post_meta($post_id, $meta_key);
	$old_post_meta = $old_post_meta ? $old_post_meta : 0;
	$new_post_meta = $old_post_meta + $val;

	// 不为负数
	if ($min_zero and $new_post_meta < 0) {
		$new_post_meta = 0;
	}

	return wnd_update_post_meta($post_id, $meta_key, $new_post_meta);
}

//############################################################################ 更新用户字段数组元素，若数组不存在，则插入更新数组元素
function wnd_update_user_meta($user_id, $array_key, $array_value) {
	// 空值，删除
	if (empty($array_value) and !is_numeric($array_value)) {
		wnd_delete_user_meta($user_id, $array_key);
		return true;
	}

	$update_array = [$array_key => $array_value];
	$old_array    = get_user_meta($user_id, 'wnd_meta', 1);
	$old_array    = $old_array ? $old_array : [];
	if (!is_array($old_array)) {
		return false;
	}

	$new_array = array_merge($old_array, $update_array);
	return update_user_meta($user_id, 'wnd_meta', $new_array);
}

function wnd_update_user_meta_array($user_id, $update_array) {
	if (!is_array($update_array)) {
		return false;
	}

	$old_array = get_user_meta($user_id, 'wnd_meta', 1);
	$old_array = $old_array ? $old_array : [];
	if (!is_array($old_array)) {
		return false;
	}

	$new_array = array_merge($old_array, $update_array);
	// 删除空值
	foreach ($new_array as $array_key => $array_value) {
		if (empty($array_value) and !is_numeric($array_value)) {
			unset($new_array[$array_key]);
		}
	}
	unset($array_value, $array_key);

	return update_user_meta($user_id, 'wnd_meta', $new_array);
}

// 获取user meta数组中的元素值
function wnd_get_user_meta($user_id, $array_key) {
	$array = get_user_meta($user_id, 'wnd_meta', 1);
	if (!is_array($array) or !array_key_exists($array_key, $array)) {
		return false;
	}

	$value = $array[$array_key];
	return $value;
}

// 删除用户字段数组元素
function wnd_delete_user_meta($user_id, $array_key) {
	$array = get_user_meta($user_id, 'wnd_meta', 1);
	if (!$array) {
		return false;
	}

	if (is_array($array) and array_key_exists($array_key, $array)) {
		unset($array[$array_key]);
		return update_user_meta($user_id, 'wnd_meta', $array);
	}
}

//############################################################################ 更新文章字段数组元素，若数组不存在，则插入更新数组元素
function wnd_update_post_meta($post_id, $array_key, $array_value) {
	// 空值，删除
	if (empty($array_value) and !is_numeric($array_value)) {
		wnd_delete_post_meta($post_id, $array_key);
		return true;
	}

	$update_array = [$array_key => $array_value];
	$old_array    = get_post_meta($post_id, 'wnd_meta', 1);
	$old_array    = $old_array ? $old_array : [];
	if (!is_array($old_array)) {
		return false;
	}

	$new_array = array_merge($old_array, $update_array);
	return update_post_meta($post_id, 'wnd_meta', $new_array);
}

function wnd_update_post_meta_array($post_id, $update_array) {
	if (!is_array($update_array)) {
		return false;
	}

	$old_array = get_post_meta($post_id, 'wnd_meta', 1);
	$old_array = $old_array ? $old_array : [];
	if (!is_array($old_array)) {
		return;
	}

	$new_array = array_merge($old_array, $update_array);
	// 删除空值
	foreach ($new_array as $array_key => $array_value) {
		if (empty($array_value) and !is_numeric($array_value)) {
			unset($new_array[$array_key]);
		}
	}
	unset($array_value, $array_key);

	return update_post_meta($post_id, 'wnd_meta', $new_array);
}

// 获取post meta数组中的元素值
function wnd_get_post_meta($post_id, $array_key) {
	$array = get_post_meta($post_id, 'wnd_meta', 1);
	if (!is_array($array) or !array_key_exists($array_key, $array)) {
		return false;
	}

	$value = $array[$array_key];
	return $value;
}

// 删除文章字段数组元素
function wnd_delete_post_meta($post_id, $array_key) {
	$array = get_post_meta($post_id, 'wnd_meta', 1);
	if (!$array) {
		return false;
	}

	if (is_array($array) and array_key_exists($array_key, $array)) {
		unset($array[$array_key]);
		return update_post_meta($post_id, 'wnd_meta', $array);
	}
}

//############################################################################ 更新options数组元素，若数组不存在，则插入更新数组元素
function wnd_update_option($option_name, $array_key, $array_value) {
	// 空值，删除
	if (empty($array_value) and !is_numeric($array_value)) {
		wnd_delete_option($option_name, $array_key);
		return true;
	}

	$update_array = [$array_key => $array_value];
	$old_array    = get_option($option_name);
	$old_array    = $old_array ? $old_array : [];
	if (!is_array($old_array)) {
		return false;
	}
	$new_array = array_merge($old_array, $update_array);

	return update_option($option_name, $new_array);
}

function wnd_update_option_array($option_name, $update_array) {
	if (!is_array($update_array)) {
		return false;
	}

	$old_array = get_option($option_name);
	$old_array = $old_array ? $old_array : [];
	if (!is_array($old_array)) {
		return false;
	}

	$new_array = array_merge($old_array, $update_array);
	// 删除空值
	foreach ($new_array as $array_key => $array_value) {
		if (empty($array_value) and !is_numeric($array_value)) {
			unset($new_array[$array_key]);
		}
	}
	unset($array_value, $array_key);

	return update_option($option_name, $new_array);

}

// 获取options数组中的元素值
function wnd_get_option($option_name, $array_key) {
	$array = get_option($option_name);
	if (!is_array($array) or !array_key_exists($array_key, $array)) {
		return false;
	}

	$value = $array[$array_key];
	return $value;
}

// 删除options数组元素
function wnd_delete_option($option_name, $array_key) {
	$array = get_option($option_name);
	if (!$array) {
		return false;
	}

	if (is_array($array) and array_key_exists($array_key, $array)) {
		unset($array[$array_key]);
		return update_option($option_name, $array);
	}
}
