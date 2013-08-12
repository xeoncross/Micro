<?php

namespace Micro;

/**
 * Ciphers algorithms for encription, hashing, and base conversion
 */
class Cipher
{
	/**
	 * Encrypt a string
	 *
	 * @param string $string to encrypt
	 * @param string $key a cryptographically random string
	 * @param int $algo the encryption algorithm
	 * @param int $mode the block cipher mode
	 * @return string
	 */
	public static function encrypt($string, $key, $algo = MCRYPT_RIJNDAEL_256, $mode = MCRYPT_MODE_CBC)
	{
		$iv = mcrypt_create_iv(mcrypt_get_iv_size($algo, $mode), MCRYPT_DEV_URANDOM);
		return base64_encode(mcrypt_encrypt($algo, $key, $string, $mode, $iv) . $iv);
	}

	/**
	 * Decrypt an encrypted string
	 *
	 * @param string $string to encrypt
	 * @param string $key a cryptographically random string
	 * @param int $algo the encryption algorithm
	 * @param int $mode the block cipher mode
	 * @return string
	 */
	public static function decrypt($string, $key, $algo = MCRYPT_RIJNDAEL_256, $mode = MCRYPT_MODE_CBC)
	{
		$string = base64_decode($string);
		$size = mcrypt_get_iv_size($algo, $mode);
		$iv = substr($string, -$size);

		if(strlen($iv) === $size) {
			return rtrim(mcrypt_decrypt($algo, $key, substr($string, 0, -$size), $mode, $iv), "\x0");
		}
	}

	/**
	 * Hash a string using blowfish with a default of 12 iterations. To verify a hash,
	 * pass the hash plus the string back to this function as the second parameter.
	 *
	 * @param string $string to hash
	 * @param string|null $salt previous hash of string
	 * @return string
	 */
	public static function hash($string, $salt = NULL, $iterations = '12')
	{
		$hash = crypt($string, $salt ?: "$2a\$$iterations$" . md5(mcrypt_create_iv(22, MCRYPT_DEV_URANDOM)));
		if (strlen($hash) == 60) return $hash;
	}

	/**
	 * Convert a higher-base ID key back to a base-10 integer
	 *
	 * @param string $key
	 * @return integer
	 */
	public static function keyToID($key)
	{
		return function_exists('gmp_init') ? gmp_strval(gmp_init($key, 62), 10) : base_convert($key, 32, 10);
	}

	/**
	 * Convert a base-10 integer to a higher-base ID key
	 *
	 * @param integer $id
	 * @return string
	 */
	public static function IDToKey($id)
	{
		return function_exists('gmp_init') ? gmp_strval(gmp_init($id, 10), 62) : base_convert($key, 10, 32);
	}

	/**
	 * Encode a string so it is safe to pass through the URL
	 *
	 * @param string $string to encode
	 * @return string
	 */
	public static function base64_url_encode($string = NULL)
	{
		return strtr(base64_encode($string), '+/=', '-_~');
	}

	/**
	 * Decode a string passed through the URL
	 *
	 * @param string $string to decode
	 * @return string
	 */
	public static function base64_url_decode($string = NULL)
	{
		return base64_decode(strtr($string, '-_~', '+/='));
	}

	/**
	 * Generate a SHA1 hash in base 64 (28 characters) that is URL safe
	 *
	 * @param string $string to encode
	 * @return string
	 */
	public static function base64_sha1($string = NULL)
	{
		return static::base64_url_encode(base64_encode(pack('H*', sha1($string))));
	}
}