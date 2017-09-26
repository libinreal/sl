<?php
namespace app\components\helpers;

class HtmlHelper
{
	/**
	 * 输出导航
	 * @param  array $navArr 导航配置
	 * @return string 导航内容
	 */
	public static function renderNav1( $navArr )
	{
		if( !isset($navArr['items']) )
			return '';
		$str = '<ul>';
		foreach($navArr['items'] as $_nk1=>$_nv1)
		{
			
			$str .= '<li><a href="' . $_nv1['url'] . '">' . $_nv1['label'];
			$str .= '</a>';

			$str .= self::renderNav1($_nv1);
			$str .= '</li>';

		}
		$str .= '</ul>';

		return $str;
	}
}