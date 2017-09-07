<?
namespace Sylight;

class EscapedArray implements \ArrayAccess, \Iterator
{
  public $raw;
  protected $escaped;
  
  public function __construct(array $raw, array $escaped)
  {
    $this->raw = $raw;
    $this->escaped = $escaped;
  }

  // ArrayAccess
  
  public function offsetSet($offset, $value) {
    if (is_null($offset)) {
      $this->raw[] = $value;
    } else {
      $this->raw[$offset] = $value;
    }
  }

  public function offsetExists($offset) {
    return isset($this->raw[$offset]);
  }

  public function offsetUnset($offset) {
    unset($this->raw[$offset]);
  }

  public function offsetGet($offset) {
    return isset($this->raw[$offset]) ? $this->raw[$offset] : null;
  }
  
  // Iterator
  public function current() {
    return current($this->raw);
  }
  
  public function key() {
    return key($this->raw);
  }
  
  public function next() {
    return next($this->raw);
  }
  
  public function rewind() {
    return reset($this->raw);
  }
  
  public function valid() {
    return key($this->raw) !== null;
  }
}
