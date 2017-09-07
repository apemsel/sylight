<?
namespace Sylight\Helper;

class HtmlHelper extends \Symfony\Component\Templating\Helper\Helper
{
  public function getName()
  {
    return "HtmlHelper";
  }
  
  public function attributes($attributes, $prefix = "")
  {
    return implode(" ", array_map(function($k, $v) use($prefix) {
      return $v === false ? "" : $prefix.$k."=\"".$v."\"";
    }, array_keys($attributes), $attributes));
  }
}
