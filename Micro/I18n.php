<?php

namespace Micro;

/**
 * Handles doing all the i18n and l10n stuff PHP should already do.
 * Basically, makes your site work with other languages.
 *
 * Much of this class comes from the groundbreaking work of Alix Axel
 * @see https://github.com/alixaxel/phunction
 */
//class Internationalization
class I18n
{
	/**
	 * Set the default locale for this request
	 *
	 * @param string $locale The locale desired
	 */
	public static function setLocale($locale)
	{
		// Match preferred language to those available, defaulting to generic English
		$locale = Locale::lookup(config()->languages, $locale, false, 'en');
		Locale::setDefault($locale);
		setlocale(LC_ALL, $locale . '.utf-8');
		//putenv("LC_ALL", $locale);
	}

	/**
	 * Format the given string using the current system locale.
	 * Basically, it's sprintf on i18n steroids.
	 *
	 * @see MessageFormatter
	 * @param string $string to parse
	 * @param array $params to insert
	 * @return string
	 */
	public static function format($string, array $params = NULL)
	{
		return msgfmt_format_message(setlocale(LC_ALL,0), $string, $params);
	}

	/**
	 * Format the given DateTime object (or string) for display in the current locale
	 *
	 * @param DateTime $date
	 * @param integer $datetype
	 * @param integer $typetype
	 * @param integer $timezone
	 * @return string
	 */
	public static function date($date, $datetype = \IntlDateFormatter::MEDIUM, $timetype = \IntlDateFormatter::SHORT, $timezone = NULL)
	{
		$dateFormatter = new \IntlDateFormatter(
			\Locale::getDefault(),
			$datetype,
			$timetype,
			$timezone ?: date_default_timezone_get()
		);

		if( ! $date instanceof \DateTime)
		{
			if(ctype_digit($date)) {
				$date = date("c", $date);
			}
			$date = new \DateTime($date);
		}

		return $dateFormatter->format($date->getTimestamp());
	}

	/**
	 * Convert an integer to a PHP timezone name
	 *
	 * @param integer $offset
	 * @param boolean $dst
	 * @return string
	 */
	public static function utc_offset_to_timezone($offset, $dst = false)
	{
		return timezone_name_from_abbr('', (int) $offset * 3600, $dst);
	}

	/**
	 * Normalize the given UTF-8 string
	 *
	 * @see http://stackoverflow.com/a/7934397/99923
	 * @param string $string to normalize
	 * @param int $form to normalize as
	 * @return string
	 */
	public static function normalize($string, $form = Normalizer::FORM_KD)
	{
		return normalizer_normalize($string, $form);
	}

	/**
	 * Convert a string to UTF-8, remove invalid bytes sequences, and control
	 * characters.
	 *
	 * @param string $string to convert
	 * @param string $control true to remove control characters
	 * @param string $encoding current encoding of string (default to UTF-8)
	 * @return string
	 */
	public static function filter($data, $control = true, $encoding = null)
	{
		if (is_array($data) === true)
		{
			$result = array();

			foreach ($data as $key => $value)
			{
				$result[self::filter($key, $control, $encoding)] = self::filter($value, $control, $encoding);
			}

			return $result;
		}

		else if (is_string($data) === true)
		{
			if (preg_match('~[^\x00-\x7F]~', $data) > 0)
			{
				if (function_exists('mb_detect_encoding') === true)
				{
					$encoding = mb_detect_encoding($data, 'auto');
				}

				$data = @iconv((empty($encoding) === true) ? 'UTF-8' : $encoding, 'UTF-8//IGNORE', $data);
			}

			// ~\R~u  ====  ~\r\n?~		???
			return ($control === true) ? preg_replace('~\p{C}+~u', '', $data) : preg_replace(array('~\r\n?~', '~[^\P{C}\t\n]+~u'), array("\n", ''), $data);
		}

		return $data;
	}

	/**
	 * Remove accents from characters
	 *
	 * @param string $string to remove accents from
	 * @return string
	 */
	public static function unaccent($string)
	{
		if (strpos($string = htmlentities($string, ENT_QUOTES, 'UTF-8'), '&') !== false)
		{
			$regex = '~&([a-z]{1,2})(?:acute|caron|cedil|circ|grave|lig|orn|ring|slash|tilde|uml);~i';
			$string = html_entity_decode(preg_replace($regex, '$1', $string), ENT_QUOTES, 'UTF-8');
		}

		return $string;
	}

	/**
	 * Convert a string to an ASCII/URL/file name safe slug
	 *
	 * @param string $string to convert
	 * @param string $slug character to separate words with
	 * @param string $extra characters to include
	 * @return string
	 */
	public static function slug($string, $slug = '-', $extra = null)
	{
		$string = self::unaccent(self::normalize($string));
		return strtolower(trim(preg_replace('~[^0-9a-z' . preg_quote($extra, '~') . ']+~i', $slug, $string), $slug));
	}

	/**
	 * Tests whether a string contains only 7bit ASCII characters.
	 *
	 * @param string $string to check
	 * @return bool
	 */
	public static function is_ascii($string)
	{
		return ! preg_match('/[^\x00-\x7F]/S', $string);
	}
}