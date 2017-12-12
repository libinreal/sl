<?php
namespace app\components\helpers;

class ConfigHelper
{
	/**
	 * 分析路径下的文本文件，不限文件后缀，内容格式为：[]内对应key，下面可放多行value e.g.
	 * [key]
	 * value1
	 * value2
	 * value3
	 * [key2]
	 * [key3]
	 * …
	 *
	 * @param  string $path 文件路径
	 * @return array  $config_arr 二维数组, [ 'key' => ['value1', 'value2', 'value3'…]]
	 */
	public static function parseIniToLine($p)
	{
		$ret = [];

		$f = fopen($p, 'r');
		$k = '';
		while(feof($f)===false)
		{
			$readline = fgets($f);
			if(preg_match_all('/\[(\w+)\]/', $readline, $m))
			{

				if(isset($m[1]) && isset($m[1][0]))
				{
					$k = $m[1][0];
					$ret[$k] = [];	
				}
				
			}
			else
			{
				$ret[$k][] = $readline;
			}
		}
		fclose($f);

		return $ret;
	}
}