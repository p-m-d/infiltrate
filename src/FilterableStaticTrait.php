<?php

namespace Infiltrate;

trait FilterableStaticTrait {

	protected static function _filter($method, $params, $callback, $filters = array()) {
		$method = Filters::target($method, get_called_class());
		return Filters::run($method, $params, $callback, $filters);
	}

	public static function applyFilter($method, $filter) {
		$method = Filters::target($method, get_called_class());
		return Filters::apply($method, $filter);
	}

	public static function omitFilter($method, $filter) {
		$method = Filters::target($method, get_called_class());
		return Filters::omit($method, $filter);
	}
}

?>