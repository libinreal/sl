<?php
namespace app\components\helpers;

class DateHelper
{
	/**
	 * 分割日期范围字符串为数组
	 * @param  [type] $date_ranges [description]
	 * @return array  [起始日期,结束日期]
	 */
	public static function makeBetweenValue( $date_ranges )
	{
		if(empty( $date_ranges )){
			return [null, null];
		}

		if( substr_count($date_ranges, ',') === 1 )
		{
			$range = explode(',', $date_ranges);
		}
		else if( substr_count($date_ranges, '-') === 1 )
		{
			$range = explode('-', $date_ranges);
		}

		return [ trim( $range[0] ), trim( $range[1] ) ];
	}
}