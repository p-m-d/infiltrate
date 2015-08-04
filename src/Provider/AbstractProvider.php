<?php

namespace Infiltrate\Provider;

abstract class AbstractPovider {

	/**
	 * Config settings, set config defaults for you subclasses here
	 *
	 * @var array
	*/
	protected static $defaults = array();

	/**
	 * Cached storage of FilterObject filter methods
	*/
	protected static $filterMethods = array();

	/**
	 * Internal storage of binding settings.
	 *
	 * @var array
	*/
	protected static $settings = array();

	/**
	 * Apply the filter object to a class
	 *
	 * @param string $class class object is being applied to
	 * @param array $settings settings for the binding
	 * @return array binding settings for $class
	*/
	public static function apply($class, array $settings = array()) {
		static::settings($class, false);
		$settings += [
			'methods' => [],
			'apply' => true
		];
		if ($settings['apply']) {
			$settings+= static::$defaults;
			$settings = static::applyFilterMethods($class, $settings);
		}
		return static::settings($class, $settings);
	}


	/**
	 * Get or set settings for binding
	 *
	 * Note: to prpoerly handle instance based bindings this method could
	 * be overwritten to proxy the storage & access to a property of the
	 * class instance, i.e. by default we only handle binding settings
	 * to single instance of a bound object.
	 *
	 * @param string $class class object is being applied to
	 * @param array $settings settings for the binding
	 * @return array binding settings for $class
	 */
	protected static function settings($class, $settings = array()) {
		$self = get_called_class();
		if (is_object($class)) {
			$class = get_class($class);
		}
		if ($settings === false || !isset(static::$settings[$self][$class])) {
			static::$settings[$self][$class] = array();
		}
		if (!empty($settings)) {
			static::$settings[$self][$class] = $settings + static::$settings[$self][$class];
		}
		return static::$settings[$self][$class];
	}

	/**
	 * Apply filter object filter methods to class filters
	 *
	 * @param mixed $class class name or instance
	 * @param array $settings configuration for binding
	 * @return $settings with `method` key modified as required
	 */
	protected static function applyFilterMethods($class, $settings) {
		$self = get_called_class();
		$all = empty($settings['methods']);
		$filterMethods = static::filterMethods();
		foreach ($filterMethods as $filterMethod) {
			$pattern = '/(.*)(Before|After)Filter$/';
			if (preg_match($pattern, $filterMethod, $matches)) {
				$method = $matches[1];
				$apply = $matches[2];
				$applyMethod = "filter{$apply}Method";
				if ($all) {
					$settings['methods'][] = $method . '.' . lcfirst($apply);
				}
			} else {
				$applyMethod = 'filterMethod';
				$method = preg_replace('/(.*)Filter$/', '$1', $filterMethod);
				if ($all) {
					$settings['methods'][] = $method;
				}
			}
			$filter = $self::$applyMethod($method, $filterMethod);
			if (!$filter) {
				continue;
			}
			Filters::add($class, $method, $filter);
		}
		return $settings;
	}

	/**
	 * Get method names of a filter class that will be applied to a class
	 *
	 * @return array methods of class that match the standard filter pattern
	 */
	protected static function filterMethods() {
		$self = get_called_class();
		if (!isset(static::$filterMethods[$self])) {
			$methods = get_class_methods($self);
			$pattern = '/(.*)(?<!^apply)Filter$/';
			static::$filterMethods[$self] = preg_grep($pattern, $methods);
		}
		return static::$filterMethods[$self];
	}


	/**
	 * Create filter closure
	 *
	 * @param string $method class method being filtered
	 * @param string $filter filter method
	 * @return filter closure to apply to class
	 */
	abstract protected static function filterMethod($method, $filter);

	/**
	 * Create before filter closure
	 *
	 * @param string $method class method being filtered
	 * @param string $filter filter method
	 * @return filter closure to apply to class
	 */
	abstract protected static function filterBeforeMethod($method, $filter);

	/**
	 * Create after filter closure
	 *
	 * @param string $method class method being filtered
	 * @param string $filter filter method
	 * @return filter closure to apply to class
	 */
	abstract protected static function filterAfterMethod($method, $filter);

}