<?php
namespace Infiltrate;

class Filters {

	protected static $staticMethodFilters = [];

	public static function add($class, $method, $filter) {
		if (is_object($class)) {
			$class->addMethodFilter($method, $filter);
		} else {
			if (class_exists($class, false)) {
				$class::addStaticMethodFilter($method, $filter);
			} else {
				static::addStaticMethodFilter($class, $method, $filter);
			}
		}
	}

	public static function remove($class, $method, $filter) {
		if (is_object($class)) {
			$class->removeMethodFilter($method, $filter);
		} else {
			if (class_exists($class, false)) {
				$class::removeStaticMethodFilter($method, $filter);
			}
			static::removeStaticMethodFilter($class, $method, $filter);
		}
	}

	public static function filter($class, $method, $params, $callback, $filters = []) {
		$_filters = static::getStaticMethodFilters($class, $method);
		if (is_object($class)) {
			$_filters = array_merge($_filters, $class->getMethodFilters($method));
		} elseif (class_exists($class, false)) {
			$_filters = array_merge($_filters, $class::getStaticMethodFilters($method));
		}
		if (empty($_filters) && empty($filters)) {
			return $callback($class, $params, null);
		}
		$filters = array_merge($_filters, $filters, [$callback]);
		return static::begin($class, $params, compact('filters', 'class', 'method'));
	}

	public static function getStaticMethodFilters($class, $method) {
		if (is_object($class)) {
			$class = get_class($class);
		}
		if (!empty(static::$staticMethodFilters[$class][$method])) {
			return static::$staticMethodFilters[$class][$method];
		}
		return [];
	}

	public static function addStaticMethodFilter($class, $method, $filter) {
		if (is_object($class)) {
			$class = get_class($class);
		}
		static::$staticMethodFilters[$class][$method][] = $filter;
	}

	public static function removeStaticMethodFilter($class, $method, $filter) {
		if (is_object($class)) {
			$class = get_class($class);
		}
		if ($filter === true) {
			static::$staticMethodFilters[$class][$method] = [];
		} elseif (!empty(static::$staticMethodFilters[$class][$method])) {
			$index = array_search($filter, static::$staticMethodFilters[$class][$method]);
			if ($index) {
				unset(static::$staticMethodFilters[$class][$method][$index]);
			}
		}
	}

	protected static function begin($class, $params, array $options = []) {
		$defaults = ['class' => null, 'method' => null, 'filters' => []];
		$options += $defaults;
		$chain = new FilterCollection($options);
		$next = $chain->rewind();
		return call_user_func($next, $class, $params, $chain);
	}

	/**
	 * @deprecated
	 */
	public static function run($method, $params, $callback, $filters = []) {
		$message = '%s is deprecated, please use %::filter()';
		trigger_error(sprintf($message, __METHOD__, __CLASS__), E_USER_DEPRECATED);
		list($class, $method) = static::target($method);
		return static::filter($class, $method, $params, $callback, $filters);
	}

	/**
	 * @deprecated
	 */
	public static function apply($method, $filter) {
		$message = '%s is deprecated, please use %::add()';
		trigger_error(sprintf($message, __METHOD__, __CLASS__), E_USER_DEPRECATED);
		list($class, $method) = static::target($method);
		$this->add($class, $method, $filter);
	}

	/**
	 * @deprecated
	 */
	public static function omit($method, $filter) {
		$message = '%s is deprecated, please use %::remove()';
		trigger_error(sprintf($message, __METHOD__, __CLASS__), E_USER_DEPRECATED);
		list($class, $method) = static::target($method);
		$this->remove($class, $method, $filter);
	}
}

?>