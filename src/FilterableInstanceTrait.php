<?php

namespace Infiltrate;

trait FilterableInstanceTrait {

	protected $methodFilters = [];

	public function getMethodFilters($method) {
		if (!empty($this->methodFilters[$method])) {
			return $this->methodFilters[$method];
		}
		return [];
	}

	public function addMethodFilter($method, $filter) {
		$this->methodFilters[$method][] = $filter;
	}

	public function removeMethodFilter($method, $filter) {
		if ($filter === true) {
			$this->methodFilters[$method] = [];
		} elseif (!empty($this->methodFilters[$method])) {
			$index = array_search($filter, $this->methodFilters[$method]);
			if ($index) {
				unset($this->methodFilters[$method][$index]);
			}
		}
	}

	protected function filterMethod($method, $params, $callback, $filters = array()) {
		return Filters::filter($this, $method, $params, $callback, $filters);
	}

	/**
	 * @deprecated
	 */
	protected function _filter($method, $params, $callback, $filters = array()) {
		$message = '%s is deprecated, please use %::filterMethod()';
		trigger_error(sprintf($message, __METHOD__, __CLASS__), E_USER_DEPRECATED);
		return $this->filterMethod($method, $params, $callback, $filters);
	}

	/**
	 * @deprecated
	 */
	public function applyFilter($method, $filter) {
		$message = '%s is deprecated, please use %::addMethodFilter()';
		trigger_error(sprintf($message, __METHOD__, __CLASS__), E_USER_DEPRECATED);
		list(,$method) = Filters::target($method);
		return $this->addMethodFilter($method, $filter);
	}

	/**
	 * @deprecated
	 */
	public static function omitFilter($method, $filter) {
		$message = '%s is deprecated, please use %::removeMethodFilter()';
		trigger_error(sprintf($message, __METHOD__, __CLASS__), E_USER_DEPRECATED);
		list(,$method) = Filters::target($method);
		return $this->removeMethodFilter($method, $filter);
	}
}

?>