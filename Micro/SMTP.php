<?php

namespace Micro;

/**
 * SMTP client acting as a Mail Transfer Agent (MTA)
 */
class SMTP
{
	/**
	 * Hashcash is a computationally expensive operation for the sender, while being 
	 * easily verified by the receiver. It proves this email was worth working for 
	 * and isn't spam.
	 *
	 * @param string $email
	 * @return string
	 */
	public static function hashcash($email)
	{
		$count = 0;
		$hashcash = sprintf('1:20:%u:%s::%u', date('ymd'), $email, mt_rand());
		while (strncmp('00000', sha1($hashcash . $count), 5) !== 0) ++$count;
		return $hashcash . $count;
	}

	/**
	 * Compose a Content-Type string for the email DATA body
	 *
	 * @param string $body
	 * @param string $boundry
	 * @param string $type
	 * @return string
	 */
	public static function body($string, $boundary, $type='text/html')
	{
		return "--$boundary\r\n"
			. "Content-Type: $type; charset=utf-8\r\n"
			. "Content-Disposition: inline\r\n"
			. "Content-Transfer-Encoding: base64\r\n\r\n"
			. chunk_split(base64_encode($string));
	}

	/**
	 * Compose a valid SMTP DATA string from email message parts
	 *
	 * @param string $to
	 * @param string $subject
	 * @param string $html
	 * @param string $text
	 * @return string
	 */
	public static function message($to, $subject, $html, $text = NULL)
	{
		$boundary = uniqid();

		return 'Subject: =?UTF-8?B?' . base64_encode($subject) . "?=\r\n"
			. "To: $to\r\n"
			//. "Date: " . date('r') . "\r\n"
			. "MIME-Version: 1.0\r\n"
			. 'X-Hashcash: ' . self::hashcash($to) . "\r\n"
			. "Content-Type: multipart/alternative; boundary=\"$boundary\"\r\n"
			. "\r\n"
			. self::body($html, $boundary)
			. self::body($text ?: strip_tags($html), $boundary, 'text/plain')
			. "--$boundary--\r\n"
			. ".";
	}

	/**
	 * Mail an SMTP message to the recipient
	 *
	 * @param string $to
	 * @param string $from
	 * @param string $message
	 * @param string $user
	 * @param string $pass
	 * @param string $host
	 * @param string $port
	 * @return boolean
	 */
	public static function mail($to, $from, $message)
	{
		list(, $host) = explode('@', $to, 2);
		
		// MX records for email servers are optional :)
		if(getmxrr($host, $mx))
		{
			$host = current($mx);
		}

		if ($h = fsockopen($host, 25, $errno, $errstr))
		{
			$data = array(
				0,
				"EHLO $host",
				"MAIL FROM: <$from>",
				"RCPT TO: <$to>",
				'DATA',
				$message,
				'QUIT'
			);

			foreach($data as $c)
			{
				$c && fwrite($h, "$c\r\n");
				while(is_resource($h) && substr(fgets($h, 256), 3, 1) != ' '){}
			}

			return is_resource($h) && fclose($h);
		}
		else
		{
			return $errstr;
		}
	}
}