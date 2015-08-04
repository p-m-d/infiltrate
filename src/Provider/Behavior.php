<?php
namespace Infiltrate\Provider;

/**
 * The `Behavior` base class.
 */
abstract class Behavior extends AbstractProvider {

	/**
	 * Create filter closure
	 *
	 * @param string $method class method being filtered
	 * @param string $filter filter method
	 * @return filter closure to apply to class
	 */
	protected static function filterMethod($method, $filter){
		$class = get_called_class();
		return function($self, $params, $chain) use ($method, $class, $filter) {
			$settings = $class::settings($self);
			if (!in_array($method, $settings['methods'])) {
				return $chain->next($self, $params, $chain);
			}
			return $class::$filter($self, $params, $chain, $settings);
		};
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
			if (in_array($method .'.before', $settings['methods'])) {
				$params = $class::$filter($self, $params, $settings);
			}
			return $chain->next($self, $params, $chain, $settings);
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
				$params = $class::$filter($self, $params, $settings);
			}
			return $params;
		};
	}
}
