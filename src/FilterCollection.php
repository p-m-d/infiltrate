<?php
namespace Infiltrate;

class FilterCollection implements \ArrayAccess, \Iterator, \Countable {

	protected $filters = [];

	protected $valid = false;

	public function __construct(array $options = []) {
		if (!empty($options['filters'])) {
			$this->filters = $options['filters'];
		}
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