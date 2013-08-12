<?php
namespace Micro;

class DOM
{
	public $protocals = array(
		'http', 'https', 'mailto', 'ftp'
	);

	public $whitelist = array(
		'#text' => array(),
		'html' => array(),
		'body' => array(),

		'h3' => array(), 'h4' => array(), 'h5' => array(), 'h6' => array(),
		
		'blockquote' => array(), 'q' => array(),
		'pre' => array('class'), 'code' => array('class'), 'span' => array('class'), // Code
		'ul' => array(), 'ol' => array(), 'li' => array(),
		
		'a' => array('href', 'title'),
		'p' => array(),
		'b' => array(),
		'em' => array(),
		'i' => array(), 'u' => array(), 'strike' => array(),
		'img' => array('src', 'alt', 'title'),
		'sup' => array(), 'sub' => array(),

		'table' => array(),
		'thead' => array(),
		'tbody' => array(),
		'tfoot' => array(),
		'tr' => array(),
		'th' => array(),
		'td' => array(),

		'div' => array(),
		'br' => array(),

		//'script', //'#cdata-section',
		//'#comment', // HTML comment
		//'textarea', 'input', 'button', 'fieldset', 'form',
		//'xss','attack','name','plaintext','desc','meta','browser','label','xml',
		//'import','table','link','embed','object','style','frameset','base','bgsound',
		//'description', 'channel', 'rss', 'item', 'title', 'author', 'content', 'content:encoded', 'cdataelement'
	);

	public function __construct(array $whitelist = NULL, array $protocals = NULL)
	{
		if($whitelist) {
			$this->whitelist = $whitelist;
		}

		if($protocals) {
			$this->protocals = $protocals;
		}
	}

	public function convert_smart_quotes($string)
	{
		$quotes = array(
			"\xC2\xAB"	 => '"', // « (U+00AB) in UTF-8
			"\xC2\xBB"	 => '"', // » (U+00BB) in UTF-8
			"\xE2\x80\x98" => "'", // ‘ (U+2018) in UTF-8
			"\xE2\x80\x99" => "'", // ’ (U+2019) in UTF-8
			"\xE2\x80\x9A" => "'", // ‚ (U+201A) in UTF-8
			"\xE2\x80\x9B" => "'", // ‛ (U+201B) in UTF-8
			"\xE2\x80\x9C" => '"', // “ (U+201C) in UTF-8
			"\xE2\x80\x9D" => '"', // ” (U+201D) in UTF-8
			"\xE2\x80\x9E" => '"', // „ (U+201E) in UTF-8
			"\xE2\x80\x9F" => '"', // ‟ (U+201F) in UTF-8
			"\xE2\x80\xB9" => "'", // ‹ (U+2039) in UTF-8
			"\xE2\x80\xBA" => "'", // › (U+203A) in UTF-8
		);

		$string = strtr($string, $quotes);

		// Also replace common HTML entities
		$replace = array(
			'&#8216;' => "'",
			'&#8217;' => "'",
			'&#8220;' => '"',
			'&#8221;' => '"',
			'&#8211;' => '-',
			'&#8212;' => '-',
			'&#8230;' => '...',
			'&#8216;' => "'",
			'&#8217;' => "'",
			'&#8220;' => '"',
			'&#8221;' => '"',
			'&#8211;' => '-',
			'&#8212;' => '-',
			'&#8230;' => '...',
		);

		return str_replace(array_keys($replace), $replace, $string);
	}

	public function purify($html)
	{
		libxml_use_internal_errors(true) AND libxml_clear_errors();

		if (is_object($html))
		{
			if ( ! in_array($html->nodeName,  array_keys($this->whitelist)))
			{
				return $html->parentNode->removeChild($html);
			}
			else
			{
				//print $html->nodeName . "\n";
				//print_r($this->whitelist);
			}

			if ($html->hasChildNodes() === true)
			{
				// Purify/Delete child elements in reverse order so we don't messup DOM tree
				foreach (range($html->childNodes->length - 1, 0) as $i)
				{
					$this->purify($html->childNodes->item($i));
				}
			}

			if ($html->hasAttributes() === true)
			{
				//foreach ($html->attributes as $attr) $html->removeAttributeNode($attr);
				foreach (range($html->attributes->length - 1, 0) as $i)
				{
					$attribute = $html->attributes->item($i);

					preg_match('~^([^/:]*):~', $attribute->value, $match);

					if( ! in_array($attribute->name, $this->whitelist[$html->nodeName])
						OR empty($match[1]) OR ! in_array(strtolower(trim($match[1])), $this->protocals))
					{
						$html->removeAttributeNode($attribute);
					}
				}
			}

			return;
		}

		if(empty($html)) {
			return;
		}
		
		$dom = new \DomDocument();

		if($dom->loadHTML($html))
		{
			/*
			// Allow tags to be given without the "tag => array()" syntax
			foreach ($this->whitelist as $tag => $attributes)
			{
				if (is_int($tag))
				{
					unset($this->whitelist[$tag]);
					$this->whitelist[$attributes] = array();
				}
			}
			*/

			$this->purify($dom->documentElement);

			return preg_replace('~<(?:!DOCTYPE|/?(?:html|body))[^>]*>\s*~i', '', $dom->saveHTML());
		}
	}
}