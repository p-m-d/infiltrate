<?php
namespace Infiltrate;

class Filters implements \ArrayAccess, \Iterator, \Countable {

	protected $filters = [];

	protected $valid = false;

	protected static $staticMethodFilters = [];

	public function __construct(array $options = []) {
		if (!empty($options['data'])) {
			$this->filters = $options['data'];
		}
	}

	public static function target($method, $_class = null) {
		if (is_array($method)) {
			list($class, $_method) = $method;
			$method = $_method;
		} else {
			if (strpos($method, '::')) {
				list($class, $method) = explode('::', $method);
			} else {
				$class = '0';
			}
		}
		if (isset($_class)) {
			$class = $_class;
		}
		return [$class, $method];
	}

	public static function apply($method, $filter) {
		list($class, $method) = static::target($method);
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

	public static function omit($method, $filter) {
		list($class, $method) = static::target($method);
		if (is_object($class)) {
			$class->removeMethodFilter($method, $filter);
		} else {
			if (class_exists($class, false)) {
				$class::removeStaticMethodFilter($method, $filter);
			}
			static::removeStaticMethodFilter($class, $method, $filter);
		}
	}

	public static function run($method, $params, $callback, $filters = []) {
		list($class, $method) = static::target($method);
		$_filters = [];
		if (is_object($class)) {
			$_filters = $class->getMethodFilters($method);
		} else {
			$_filters = static::getStaticMethodFilters($class, $method);
			if (class_exists($class, false)) {
				$_filters = array_merge($_filters, $class::getStaticMethodFilters($method));
			}
		}
		if (empty($_filters) && empty($filters)) {
			return $callback($class, $params, null);
		}
		$data = array_merge($_filters, $filters, [$callback]);
		return static::_run($class, $params, compact('data', 'class', 'method'));
	}

	protected static function getStaticMethodFilters($class, $method) {
		if (!empty(static::$staticMethodFilters[$class][$method])) {
			return static::$staticMethodFilters[$class][$method];
		}
		return [];
	}

	protected static function addStaticMethodFilter($class, $method, $filter) {
		static::$staticMethodFilters[$class][$method][] = $filter;
	}

	protected static function removeStaticMethodFilter($class, $method, $filter) {
		if ($filter === true) {
			static::$staticMethodFilters[$class][$method] = [];
		} elseif (!empty(static::$staticMethodFilters[$class][$method])) {
			$index = array_search($filter, static::$staticMethodFilters[$class][$method]);
			if ($index) {
				unset(static::$staticMethodFilters[$class][$method][$index]);
			}
		}
	}

	protected static function _run($class, $params, array $options = []) {
		$defaults = ['class' => null, 'method' => null, 'data' => []];
		$options += $defaults;
		$chain = new Filters($options);
		$next = $chain->rewind();
		return call_user_func($next, $class, $params, $chain);
	}

	public function offsetExists($offset) {
		return isset($this->filters[$offset]);
	}

	public function offsetGet($offset) {
		return $this->filters[$offset];
	}

	public function offsetSet($offset, $value) {
		if (is_null($offset)) {
			return $this->filters[] = $value;
		}
		return $this->filters[$offset] = $value;
	}

	public function offsetUnset($offset) {
		unset($this->filters[$offset]);
		prev($this->filters);
	}

	public function rewind() {
		$this->valid = !(reset($this->filters) === false && key($this->filters) === null);
		return current($this->filters);
	}

	public function end() {
		$this->valid = !(end($this->filters) === false && key($this->filters) === null);
		return current($this->filters);
	}

	public function valid() {
		return $this->valid;
	}

	public function current() {
		return current($this->filters);
	}

	public function key() {
		return key($this->filters);
	}

	public function prev() {
		if (!prev($this->filters)) {
			end($this->filters);
		}
		return current($this->filters);
	}

	public function next($self = null, $params = null, $chain = null) {
		$this->valid = !(next($this->filters) === false && key($this->filters) === null);
		$next = current($this->filters);
		if (empty($self) || empty($chain)) {
			return $next;
		}
		return call_user_func($next, $self, $params, $chain);
	}

	public function append($value) {
		is_object($value) ? $this->filters[] =& $value : $this->filters[] = $value;
	}

	public function count() {
		$count = iterator_count($this);
		$this->rewind();
		return $count;
	}

	public function keys() {
		return array_keys($this->filters);
	}
}

?>