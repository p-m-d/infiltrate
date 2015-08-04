<?php
namespace Infiltrate\Provider;

/**
 * The `Observer` base class.
 */
abstract class Observer extends AbstractProvider {

	/**
	 * Create filter closure
	 *
	 * @param string $method class method being filtered
	 * @param string $filter filter method
	 * @return filter closure to apply to class
	 */
	protected static function filterMethod($method, $filter){
		return static::filterBeforeMethod($method, $filter);
	}

	/**
	 * Create before filter closure
	 *
	 * @param string $method class method being filtered
	 * @param string $filter filter method
	 * @return filter closure to apply to class
	 */
	protected static function filterBeforeMethod($method, $filter){
		$class = get_called_class();
		return function($self, $params, $chain) use ($method, $class, $filter) {
			$settings = $class::settings($self);
			$check = array($method .'.before', $method);
			$methods = $settings['methods'];
			if (in_array($check[0], $methods) || in_array($check[1], $methods)) {
				$class::$filter($self, $params, $settings);
			}
			return $chain->next($self, $params, $chain);
		};
	}

	/**
	 * Create after filter closure
	 *
	 * @param string $method class method being filtered
	 * @param string $filter filter method
	 * @return filter closure to apply to class
	 */
	protected static function filterAfterMethod($method, $filter){
		$class = get_called_class();
		return function($self, $params, $chain) use ($method, $class, $filter) {
			$settings = $class::settings($self);
			$params = $chain->next($self, $params, $chain);
			if (in_array($method .'.after', $settings['methods'])) {
				$class::$filter($self, $params, $settings);
			}
			return $params;
		};
	}
}