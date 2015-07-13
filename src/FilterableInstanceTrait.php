<?php

namespace Infiltrate;

trait FilterableInstanceTrait {

	public $_methodFilters = [];

	protected function _filter($method, $params, $callback, $filters = array()) {
		$method = Filters::target($method, $this);
		return Filters::run($method, $params, $callback, $filters);
	}

	public function applyFilter($method, $filter) {
		$method = Filters::target($method, $this);
		return Filters::apply($method, $filter);
	}

	public static function omitFilter($method, $filter) {
		$method = Filters::target($method, $this);
		return Filters::omit($method, $filter);
	}
}

?>