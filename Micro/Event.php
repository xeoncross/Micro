<?php

namespace Micro;

/**
 * Singleton global event system for systems that need site-wide observers
 * for 3rd party code.making it easier to tie into existing functionality.
 */
class Event
{
	protected static $listeners = array();

	public static function on($event, \Closure $listener)
	{
		static::$listeners[$event][] = $listener;
	}
	
	public static function once($event, \Closure $listener)
	{
		$onceListener = function () use (&$onceListener, $event, $listener)
		{
			static::off($event, $onceListener);
			call_user_func_array($listener, func_get_args());
		};

		static::on($event, $onceListener);
	}

	public static function off($event, \Closure $listener)
	{
		if (isset(static::$listeners[$event]))
		{
			if (false !== $index = array_search($listener, static::$listeners[$event], true))
			{
				unset(static::$listeners[$event][$index]);
			}
		}
	}

	public static function removeAllListeners($event = null)
	{
		if ($event !== null) {
			unset(static::$listeners[$event]);
		} else {
			static::$listeners = array();
		}
	}

	public static function listeners($event)
	{
		return isset(static::$listeners[$event]) ? static::$listeners[$event] : array();
	}

	public static function emit($event, $parameters = NULL)
	{
		$parameters = func_get_args();
		array_shift($parameters);

		foreach (self::listeners($event) as $listener)
		{
			$result = call_user_func_array($listener, $parameters);
				
			if($result !== NULL)
			{
				$parameters = $result;
			}
		}

		return $parameters;
	}
}