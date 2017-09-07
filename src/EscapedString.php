<?
namespace Sylight;

class EscapedString
{
  public $raw;
  protected $escaped;
  
  public function __construct(string $s)
  {
    $this->raw = $s;
  }
  
  /* Convenience methods for type conversion */
  
  public function __toString()
  {
    if (NULL === $this->escaped) {
      $this->escaped = htmlspecialchars($this->raw, ENT_HTML5|ENT_SUBSTITUTE|ENT_COMPAT, "utf-8", true);
    }
    
    return $this->escaped;
  }
  
  public function toString()
  {
    return $this->__toString();
  }
  
  public function toInteger()
  {
    return (int) $this->raw;
  }
  
  public function toInt()
  {
    return $this->toInteger();
  }
  
  public function toFloat()
  {
    return (float) $this->raw;
  }
}
