<?
namespace Sylight;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;

use Symfony\Component\Routing\Exception\ResourceNotFoundException;

/**
 * A lightweight application including an HTTP kernel, based on Symfony components.
 * @author Adrian Pemsel <adrian@koony.de>
 */
class Application implements \ArrayAccess
{
  protected $container;
  
  public function __construct(string $rootDir, string $env = "prod")
  {
    // build service container
    $this->container = ContainerFactory::create($this, $rootDir, $env);
  }
  
  /**
   * Turn a request into a response
   *
   * Maintain request context, find matching route, call controller and return response
   */
  public function handle(Request $request, $isSubRequest = false) : Response
  {
    // Force de_DE locale for now
    $request->setLocale("de_DE");
    
    // prepare request context and router
    $requestContext = $this->container["requestContext"];
    $requestContext->fromRequest($request);
    $router = $this->container["router"];
    $router->setContext($requestContext);
    
    if ($isSubRequest)
    {
      $routeParams["_controller"] = $request->attributes->get("_controller");
    } else {
      if ($this->container["env"] == "dev" and $request->query->has("debug")) {
        $this->container["session"]->set("debug", $request->query->get("debug", false));
      }
      
      $request->setSession($this->container["session"]);
      
      // match given url to a route
      $pathInfo = $request->getPathInfo();
      try {
        $routeParams = $router->match(strlen($pathInfo) > 2 ? rtrim($pathInfo, '/') : $pathInfo);
      }
      catch (ResourceNotFoundException $e) {
        $request->attributes->set('exception', $e);
        $request->attributes->set('http_status', 404);
        $routeParams['_controller'] = "Frontend:Error:HttpError";
      }
    }
    $request->attributes->add($routeParams);
    
    if (isset($routeParams["_permission"])) {
      if (!$this->container["user"]->may($routeParams["_permission"])) {
        // User does not have the permission required in routes.yml _permission, so redirect to login
        // either the default login route or one given in routes.yml _login
        $loginRouteName = $routeParams["_login"] ?? "login";
        $loginUrl = $this->container["router"]->generate($loginRouteName, [
          "continue" => $pathInfo
        ]);
        
        return new RedirectResponse($loginUrl, 302, []);
      }
    }
    
    $response = $this->call($request, $routeParams['_controller']);
      
    $response->prepare($request);
    
    return $response;
  }
  
  /**
   * Call controller to handle given request
   */
  public function call(Request $request, string $identifier) : Response
  {
    [$moduleName, $controllerName, $actionName] = explode(':', $identifier);
    if (!$controllerName and $moduleName) {
      $controllerName = $moduleName;
    }
    $controllerClass = "\\Sylight\\{$moduleName}\\Controller\\{$controllerName}Controller";
    
    $this->container["module_dir"] = $this->container["root_dir"]."/src/$moduleName";
    $controller = new $controllerClass($this, $request);

    // call action
    if (!is_callable([$controller, "{$actionName}Action"])) {
      throw new \Exception("Controller $controllerClass has no method {$actionName}Action.");
    }
    
    try {
      $response = call_user_func_array([$controller, "{$actionName}Action"], [$request]);
    }
    catch (\Exception $e) {
      // Do not create an endless loop if the error controller throws
      if ($request->attributes->has('exception')) {
        die("Secondary exception when trying to call ErrorController: ".$e->getMessage(). "First: ".$request->attributes->get('exception')->getMessage());
      }
      
      $request->attributes->set('exception', $e);
      $request->attributes->set('http_status', 500);
      $response = $this->call($request, "Frontend:Error:HttpError");
    }
    
    if (!($response instanceof Response)) {
      $request->attributes->set('exception', new \Exception("{$controllerName}Controller->{$actionName}Action has not created a response"));
      $request->attributes->set('http_status', 404);
      $response = $this->call($request, "Frontend:Error:HttpError");
    }
    
    return $response;
  }
  
  /**
   * Return service container
   */
  public function getContainer() : Container
  {
    return $this->container;
  }
    
  // ArrayAccess
  
  public function offsetSet($offset, $value) {
    if (is_null($offset)) {
        $this->container[] = $value;
    } else {
        $this->container[$offset] = $value;
    }
  }

  public function offsetExists($offset) {
    return $this->container->has($offset);
  }

  public function offsetUnset($offset) {
    throw new Exception("Application: Attempt to unset service");
  }

  public function offsetGet($offset) {
    return $this->container[$offset];
  }
}
