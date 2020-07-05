<?php

namespace Wndt\Utility;

use WP_Error;

/**
 *@since 2020.04.13
 *主题配置
 */
class Wndt_Upgrader {

	// 主题文件目录名称
	protected $directory_name;

	// 更新包信息
	protected $upgrade_info = [
		'theme'       => '', //主题名称
		'new_version' => '', //版本号
		'url'         => '', //介绍页面
		'package'     => '', //下载地址
	];

	// 远程版本
	protected $remote_version;

	// 本地版本
	protected $local_version;

	/**
	 * Method called from the init hook to initiate the updater
	 */
	public function __construct() {
		$this->directory_name = basename(get_template_directory());
		$this->local_version  = (wp_get_theme($this->directory_name))->get('Version');

		add_filter('upgrader_source_selection', [$this, 'fix_directory_name'], 10, 4);
		add_filter('pre_set_site_transient_update_themes', [$this, 'check_for_update']);
	}

	/**
	 *检测更新信息
	 **/
	public function check_for_update($transient): object{
		$this->get_upgrade_info();

		// 版本检测
		if (!version_compare($this->remote_version, $this->local_version, '>')) {
			return $transient;
		}

		$transient->response[$this->directory_name] = $this->upgrade_info;
		return $transient;
	}

	/**
	 *获取更新包详细信息
	 */
	protected function get_upgrade_info() {
		$url      = 'https://api.github.com/repos/swling/wndt/releases';
		$response = wp_remote_get($url);
		if (is_wp_error($response)) {
			return $response;
		}

		$response = json_decode($response['body'], true);
		if (is_array($response)) {
			$response = current($response);
		}

		// 读取GitHub tag name
		$this->remote_version = $response['tag_name'];

		// 构造安装包信息
		$this->upgrade_info['url']         = $response['html_url'];
		$this->upgrade_info['package']     = $response['zipball_url'];
		$this->upgrade_info['theme']       = $this->directory_name;
		$this->upgrade_info['new_version'] = $this->remote_version;
	}

	/* -------------------------------------------------------------------
		 * Fix directory name when installing updates
		 * -------------------------------------------------------------------
	*/

	/**
	 * Rename the update directory to match the existing plugin/theme directory.
	 *
	 * When WordPress installs a plugin or theme update, it assumes that the ZIP file will contain
	 * exactly one directory, and that the directory name will be the same as the directory where
	 * the plugin or theme is currently installed.
	 *
	 * GitHub and other repositories provide ZIP downloads, but they often use directory names like
	 * "project-branch" or "project-tag-hash". We need to change the name to the actual plugin folder.
	 *
	 * This is a hook callback. Don't call it from a plugin.
	 *
	 * @access protected
	 *
	 * @param string $source The directory to copy to /wp-content/plugins or /wp-content/themes. Usually a subdirectory of $remoteSource.
	 * @param string $remoteSource WordPress has extracted the update to this directory.
	 * @param WP_Upgrader $upgrader
	 * @return string|WP_Error
	 */
	public function fix_directory_name($source, $remoteSource, $upgrader) {
		global $wp_filesystem;
		/** @var WP_Filesystem_Base $wp_filesystem */

		//Basic sanity checks.
		if (!isset($source, $remoteSource, $upgrader, $upgrader->skin, $wp_filesystem)) {
			return $source;
		}

		if (false === stristr(basename($source), $this->directory_name)) {
			return $source;
		}

		//Rename the source to match the existing directory.
		$correctedSource = trailingslashit($remoteSource) . $this->directory_name . '/';
		if ($source == $correctedSource) {
			return $source;
		}

		//The update archive should contain a single directory that contains the rest of plugin/theme files.
		//Otherwise, WordPress will try to copy the entire working directory ($source == $remoteSource).
		//We can't rename $remoteSource because that would break WordPress code that cleans up temporary files
		//after update.
		if (static::is_bad_directory_structure($remoteSource)) {
			return new WP_Error(
				'puc-incorrect-directory-structure',
				sprintf(
					'The directory structure of the update is incorrect. All files should be inside ' .
					'a directory named <span class="code">%s</span>, not at the root of the ZIP archive.',
					htmlentities($this->directory_name)
				)
			);
		}

		/** @var WP_Upgrader_Skin $upgrader ->skin */
		$upgrader->skin->feedback(sprintf(
			'Renaming %s to %s&#8230;',
			'<span class="code">' . basename($source) . '</span>',
			'<span class="code">' . $this->directory_name . '</span>'
		));

		if ($wp_filesystem->move($source, $correctedSource, true)) {
			$upgrader->skin->feedback('Directory successfully renamed.');
			return $correctedSource;
		} else {
			return new WP_Error(
				'puc-rename-failed',
				'Unable to rename the update to match the existing directory.'
			);
		}

		return $source;
	}

	/**
	 * Check for incorrect update directory structure. An update must contain a single directory,
	 * all other files should be inside that directory.
	 *
	 * @param string $remoteSource Directory path.
	 * @return bool
	 */
	protected static function is_bad_directory_structure($remoteSource): bool {
		global $wp_filesystem;
		/** @var WP_Filesystem_Base $wp_filesystem */

		$sourceFiles = $wp_filesystem->dirlist($remoteSource);
		if (is_array($sourceFiles)) {
			$sourceFiles   = array_keys($sourceFiles);
			$firstFilePath = trailingslashit($remoteSource) . $sourceFiles[0];
			return (count($sourceFiles) > 1) || (!$wp_filesystem->is_dir($firstFilePath));
		}

		//Assume it's fine.
		return false;
	}
}
