<?
namespace Sylight;

use Symfony\Component\Templating\TemplateNameParserInterface;
use Symfony\Component\Templating\TemplateReferenceInterface;
use Symfony\Component\Templating\TemplateReference;

class TemplateNameParser implements TemplateNameParserInterface
{
  /**
   * {@inheritdoc}
   */
  public function parse($name)
  {
    if ($name instanceof TemplateReferenceInterface) {
      return $name;
    }
      
    /***start of the code I added ***/
    $formatedPath = str_replace(':', ':', $name, $count);


    if ($count >= 2) {
      $pathList = explode(':', $name);
      $formatedPath = '';


      for($i=0; $i < sizeOf($pathList); $i++) {
        if ($i==0) {
          $formatedPath = $pathList[$i].':';
        } else {
          if ($i !== sizeOf($pathList)-1) {
            $formatedPath .= $pathList[$i].'/';
          } else {
            $formatedPath .= $pathList[$i];
         }
       }
     }

   } else {
     $formatedPath = str_replace(':', '/', $formatedPath);
   }
   
    /***end of part of the code I added**/
    $engine = null;
    if (false !== $pos = strrpos($formatedPath, '.')) {
      $engine = substr($formatedPath, $pos + 1);
    }
    
    return new TemplateReference($formatedPath, $engine);
  }
}
