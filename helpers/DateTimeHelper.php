<?php

namespace helpers;

use DateTime;
use Exception;

class DateTimeHelper
{
	/**
	 * @param string $string
	 * @param string $format
	 * @return string
	 * @throws Exception
	 */
	public static function convertFormatDateTime(string $string, string $format): string
	{
		$date = new DateTime($string);
		return $date->format($format);
	}
}