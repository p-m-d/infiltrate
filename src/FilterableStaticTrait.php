<?php

namespace Infiltrate;

trait FilterableStaticTrait {

	protected static $staticMethodFilters = [];

	public static function getStaticMethodFilters($method) {
		if (!empty(static::$staticMethodFilters[$method])) {
			return static::$staticMethodFilters[$method];
		}
		return [];
	}

	public static function addStaticMethodFilter($method, $filter) {
		static::$staticMethodFilters[$method][] = $filter;
	}

	public static function removeStaticMethodFilter($method, $filter) {
		if ($filter === true) {
			static::$staticMethodFilters[$method] = [];
		} elseif (!empty(static::$staticMethodFilters[$method])) {
			$index = array_search($filter, static::$staticMethodFilters[$method]);
			if ($index) {
				unset(static::$staticMethodFilters[$method][$index]);
			}
		}
		Filters::removeStaticMethodFilter(get_called_class(), $method, $filter);
	}

	protected static function filterStaticMethod($method, $params, $callback, $filters = array()) {
		return Filters::filter(get_called_class(), $method, $params, $callback, $filters);
	}

	/**
	 * @deprecated
	 */
	protected static function _filter($method, $params, $callback, $filters = array()) {
		$message = '%s is deprecated, please use %::filterStaticMethod()';
		trigger_error(sprintf($message, __METHOD__, __CLASS__), E_USER_DEPRECATED);
		return static::filterStaticMethod($method, $params, $callback, $filters);
	}

	/**
	 * @deprecated
	 */
	public static function applyFilter($method, $filter) {
		$message = '%s is deprecated, please use %::addStaticMethodFilter()';
		trigger_error(sprintf($message, __METHOD__, __CLASS__), E_USER_DEPRECATED);
		list(,$method) = Filters::target($method);
		static::addStaticMethodFilter($method, $filter);
	}

	/**
	 * @deprecated
	 */
	public static function omitFilter($method, $filter) {
		$message = '%s is deprecated, please use %::removeStaticMethodFilter()';
		trigger_error(sprintf($message, __METHOD__, __CLASS__), E_USER_DEPRECATED);
		list(,$method) = Filters::target($method);
		static::removeStaticMethodFilter($method);
	}
}

?>