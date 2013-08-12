<?php

namespace Micro;

/**
 * Dependency Injection + Service Locator
 */
class Instance
{
	public static $registry = array();
	public static $singletons = array();

	/**
	 * Register an object and its resolver.
	 *
	 * @param string $name
	 * @param Closure $resolver
	 * @return void
	 */
	public static function register($name, \Closure $resolver, $singleton = true)
	{
		static::$registry[$name] = array('resolver' => $resolver, 'singleton' => $singleton);
	}
	
	/**
	 * Determine if an object has been registered.
	 *
	 * @param string $name
	 * @return bool
	 */
	public static function registered($name)
	{
		return isset(static::$registry[$name]);
	}

	/**
	 * Resolve an object instance from the container.
	 *
	 * @param string $name
	 * @param array $parameters
	 * @return mixed
	 */
	public static function get($name, array $parameters = NULL)
	{
		if (array_key_exists($name, static::$singletons))
		{
			return static::$singletons[$name];
		}

		if ( ! static::registered($name))
		{
			throw new \Exception("Error resolving [$name]. No resolver has been registered.");
		}

		$object = call_user_func(static::$registry[$name]['resolver'], $parameters);

		// If the resolver is registering as a singleton resolver, we will cache
		// the instance of the object in the container so we can resolve it next
		// time without having to instantiate a new instance of the object.
		//
		// This allows the developer to reuse objects that do not need to be
		// instantiated each time they are needed, such as a SwiftMailer or
		// Twig object that can be shared.
		if (isset(static::$registry[$name]['singleton']))
		{
			return static::$singletons[$name] = $object;
		}

		return $object;
	}
}