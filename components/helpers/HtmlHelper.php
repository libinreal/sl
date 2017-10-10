<?php
namespace app\components\helpers;

class HtmlHelper
{
	/**
	 * 输出导航
	 * @param  array $navArr 导航配置
	 * @return string 导航内容
	 */
	public static function renderResponsiveMenu( $navArr )
	{
		if( !isset($navArr['items']) )
			return '';
		$str = '<ul>';
		foreach($navArr['items'] as $_nk1=>$_nv1)
		{
			
			$str .= '<li><a href="' . $_nv1['url'] . '">' . $_nv1['label'];
			$str .= '</a>';

			$str .= self::renderResponsiveMenu($_nv1);
			$str .= '</li>';

		}
		$str .= '</ul>';

		return $str;
	}

	/**
	 * 输出面包屑
	 * @param  array $navArr 面包屑配置
	 * @param  array $navArr 面包屑ul容器class名
	 * @return string 面包屑内容
	 */
	public static function renderBreadcrumbs( $navArr, $className = '' )
	{
		if( !isset($navArr['items']) )
			return '';

		if(!empty($className))
		{
			$str = '<ul class="' . $className . '">';
		}
		else
		{
			$str = '<ul>';
		}

		foreach($navArr['items'] as $_nk1=>$_nv1)
		{
			if(!empty($_nv1['li_class']))
			{
				$str .= '<li class="' . $_nv1['li_class'] . '">';
			}
			else
			{
				$str .= '<li>';
			}			

			if(isset($_nv1['url']))
			{
				$str .= '<a href="' . $_nv1['url'] . '">';
			}

			$str .= $_nv1['label'];

			if(isset($_nv1['url']))
			{
				$str .= '</a>';
			}

			$str .= self::renderBreadcrumbs($_nv1);
			$str .= '</li>';

		}
		$str .= '</ul>';

		return $str;
	}
}