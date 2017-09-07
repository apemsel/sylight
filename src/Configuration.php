<?
namespace Sylight;

use Symfony\Component\Yaml\Parser;

class Configuration implements \ArrayAccess
{
  protected $config;
  
  public function __construct($file = false, $localFile = false)
  {
    if ($file) {
      $yaml = new Parser();
      $this->config = $yaml->parse(file_get_contents($file));
    }
    
    if ($this->config === NULL) {
      $this->config = [];
    }
    
    if ($localFile and is_file($localFile)) {
      $this->config = array_replace_recursive($this->config, $yaml->parse(file_get_contents($localFile)));
    }
  }

  /**
   * Get a configuration value
   *
   * @param String $path Config path in dot notation
   * @param Mixed $default Default value if path is not set
   */
  public function get(string $path, $default = null)
  {
    return self::dot($this->config, $path, $default);
  }

  /**
   * Check if configuration value is set (also null)
   *
   * @param $path Config path in dot notation
   */
  public function has(string $path)
  {
    return self::dot($this->config, $path) !== NULL;
  }
  
  /**
   * Set configuration value
   *
   * @param $path Config path in dot notation
   * @param $value Value
   */
  public function set(string $path, $value)
  {
    $pathElements = explode(".", $path);
    if (!isset($this->config[$pathElements[0]]))
    {
      $this->config[$pathElements[0]] = [];
    }
    $branch = &$this->config[$pathElements[0]];
    for($i=1; $i<count($pathElements); $i++)
    {
      if (!isset($branch[$pathElements[$i]]) or !is_array($branch[$pathElements[$i]]))
      {
        $branch[$pathElements[$i]] = [];
      }
      $branch = &$branch[$pathElements[$i]];
    }
    $branch = $value;
  }

  /**
   * Find value in config array with dot notation
   *
   * @param Array $arr Config array
   * @param String $path Path in dot notation
   * @param Mixed $default Default value for not set values
   */
  protected static function dot(array &$arr, string $path, $default = null)
  {
    $pathElements = explode('.', $path);
    $path =& $arr;

    foreach ($pathElements as $e) {
      if (!isset($path[$e])) {
        return $default;
      }

      $path = &$path[$e];
    }
    
    return $path;
  }
  
  // ArrayAccess
  
  public function offsetSet($offset, $value) {
    $this->set($offset, $value);
  }

  public function offsetExists($offset) {
    $this->has($offset);
  }

  public function offsetUnset($offset) {
    $this->set($offset, NULL);
  }

  public function offsetGet($offset) {
    return $this->get($offset);
  }
}
