<?php
namespace Bybzmt\Blog\Common\Helper;

use Bybzmt\Blog\Common\Helper;

/**
 * 文件存储服务
 */
class FileStorge extends Helper
{
	/**
	 * 得到文件管理服务
	 * @param string 文件管理服务器名
	 * @return bybzmt\HttpStorage\SimpleHttpStorage
	 */
	static public function getFileManager($name='default')
	{
		if (!isset(self::$_fileManagers[$name])) {
			$config = self::$di->get("config")['fileManager'][$name];

			$storage = new \bybzmt\HttpStorage\SimpleHttpStorage(
				$config['host'], $config['port'], $config['timeout']
			);

			self::$_fileManagers[$name] = $storage;
		}

		return self::$_fileManagers[$name];
	}

	/**
	 * 得到图片链接处理
	 * @param string 服务器名
	 * @return bybzmt\phpim
	 */
	static public function getImageUrl($path, $op, $width=0, $height=0, $format="", $anchor="")
	{
		$key = self::getConfig('imagefilter.signatureKey');
		\bybzmt\imagefilter::$signatureKey = $key;

		$tmp = new \bybzmt\imagefilter();
		return $tmp->build_url($path, $op, $width, $height, $format, $anchor);
	}

	/**
	 * 图片链接解码
	 */
	static public function ImageUrlDecode($url)
	{
		$path = parse_url($url, PHP_URL_PATH);
		if (!$path) {
			return false;
		}

		$base_url = self::getConfig("domain.image");
		$base_url = parse_url($base_url, PHP_URL_PATH);
		if ($base_url && strlen($base_url) > 1 && strpos($path, $base_url) === 0) {
			$path = substr($path, strlen($base_url));
		}

		$image_url = new \bybzmt\imagefilter();
		$path = $image_url->decode($path);
		if (!$path) {
			false;
		}

		return $path['path'];
	}

}
