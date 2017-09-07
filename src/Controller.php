<?
namespace Sylight;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

use Symfony\Component\Templating\PhpEngine;
use Symfony\Component\Templating\TemplateNameParser;
use Symfony\Component\Templating\Loader\FilesystemLoader;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormBuilder;

/**
 * Base class for all controllers
 * @author Adrian Pemsel <adrian@koony.de>
 */
class Controller
{
  protected $app;
  protected $request;
  
  public function __construct(Application $app, Request $request)
  {
    $this->app = $app;
    $this->request = $request;
  }
  
  /**
   * Return Application
   */
  public function getApp() : Application
  {
    return $this->app;
  }
  
  /**
   * Render given view with parameters and return Response
   */
  public function render(string $view, array $params = [], array $options = []) : Response
  {
    $templating = $this->app["templating"];
    
    if (isset($options["helpers"]))
    {
      $templating->setHelpers($options["helpers"]);
    }
    
    $templating->set($this->app["helper.slots"]);
    $templating->set($this->app["helper.form"]);
    $templating->set($this->app["helper.translator"]);
    
    $templating->addGlobal("app", $this->app);
    
    // Autoescaping
    $escaper = self::getEscaperFunction();
    $params = array_map($escaper, $params);
    
    $content = $templating->render($view, $params);
    
    return Response::create($content);
  }
  
  /**
   * Render text with optional HTTP status and headers
   */
  public function renderText(string $text, int $status = 200, array $headers = []) : Response
  {
    return Response::create($text, $status, $headers);
  }

  /**
   * Render JSON with optional HTTP status and headers
   */
  public function renderJson($data, int $status = 200, array $headers = []) : JsonResponse
  {
    return JsonResponse::create($data, $status, $headers);
  }
  
  /**
   * Render binary file with optional HTTP status and headers
   */
  public function renderBinaryFile($file, int $status = 200, array $headers = [], bool $public = true, string $contentDisposition = null, bool $autoEtag = false, bool $autoLastModified = true) : JsonResponse
  {
    return BinaryFileResponse::create($file, $status, $headers, $public, $contentDisposition, $autoEtag, $autoLastModified);
  }
  
  
  /**
   * Redirect to another URL
   */
  public function redirect(string $url, int $status = 302, array $headers = []) : RedirectResponse
  {
    return new RedirectResponse($url, $status, $headers);
  }
  
  /**
   * Internal forward to another controller
   */
  public function forward($controller, array $path = [], array $query = []) : Response
  {
    $path['_controller'] = $controller;
    $subRequest = $this->request->duplicate($query, null, $path);
    
    return $this->app->handle($subRequest, true);
  }
  
  /**
   * Internal forward to error controller
   */
  public function error(int $status = 500, ?string $message = null) : Response
  {
    $params = [
      "http_status" => $status,
    ];
    
    if ($message) {
      $params["exception"] = new \Exception($message);
    }
    
    return $this->forward("Frontend:Error:HttpError", $params);
  }

  /**
   * Quick way to create a Symfony Form
   * @param  string|bool  $type     type
   * @param  array        $defaults default values
   * @param  string|bool  $name     name
   * @return Symfony\Component\Form\Form
   */
  public function createForm($type = false, $defaults = [], $name = false) : Form
  {
    return $this->createFormBuilder($type, $defaults, $name)->getForm();
  }

  /**
   * Quick way to create a Symfony FormBuilder
   * @param  string|bool  $type     type
   * @param  array        $defaults default values
   * @param  string|bool  $name     name
   * @return Symfony\Component\Form\FormBuilder
   */
  public function createFormBuilder($type = false, $defaults = [], $name = false) : FormBuilder
  {
    $type = $type ? $type : 'Symfony\\Component\\Form\\Extension\\Core\\Type\\FormType';
    if (false === $name) {
      return $this->app["formFactory"]->createBuilder($type, $defaults);
    } else {
      return $this->app["formFactory"]->createNamedBuilder($name, $type, $defaults);
    }
  }
  
  /**
   * Add a message to the user shown on next opportunity
   * @param string $type    type, one of "notice", "warning", ...
   * @param string $message message
   */
  public function addFlash(string $type, string $message)
  {
    $this->app["session"]->getFlashBag()->add($type, $message);
  }
  
  /**
   * Generates a URL from the given parameters
   *
   * @param string $route         The name of the route
   * @param mixed  $parameters    An array of parameters
   * @param int    $referenceType The type of reference (one of the constants in UrlGeneratorInterface)
   *
   * @return string The generated URL
   */
  protected function generateUrl(string $route, array $parameters = [], int $referenceType = UrlGeneratorInterface::ABSOLUTE_PATH) : string
  {
    return $this->app["router"]->generate($route, $parameters, $referenceType);
  }
  
  public static function getEscaperFunction() : \Closure
  {
    $escaper = function($value) use (&$escaper) {
      if (is_string($value)) {
        return new EscapedString($value);
      } elseif (is_array($value)) {
        return new EscapedArray($value, array_map($escaper, $value));
      } else {
        return $value;
      }
    };
    
    return $escaper;
  }
}
