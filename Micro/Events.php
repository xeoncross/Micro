<?php
namespace Micro;

/**
 * Simple event base class for objects
 */
abstract class Events
{
	protected $listeners = array();

	public function on($event, \Closure $listener)
	{
		$this->listeners[$event][] = $listener;
	}
	
	public function once($event, \Closure $listener)
	{
		$onceListener = function () use (&$onceListener, $event, $listener)
		{
			$this->off($event, $onceListener);
			call_user_func_array($listener, func_get_args());
		};

		$this->on($event, $onceListener);
	}

	public function off($event, \Closure $listener)
	{
		if (isset($this->listeners[$event]))
		{
			if (false !== $index = array_search($listener, $this->listeners[$event], true))
			{
				unset($this->listeners[$event][$index]);
			}
		}
	}

	public function removeAllListeners($event = null)
	{
		if ($event !== null) {
			unset($this->listeners[$event]);
		} else {
			$this->listeners = array();
		}
	}

	public function listeners($event)
	{
		return isset($this->listeners[$event]) ? $this->listeners[$event] : array();
	}

	public function emit($event, $parameters = NULL)
	{
		$parameters = func_get_args();
		array_shift($parameters);

		foreach ($this->listeners($event) as $listener)
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