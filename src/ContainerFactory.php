<?
namespace Sylight;

use Pimple\Container;

use Symfony\Component\Config\FileLocator;

use Symfony\Component\EventDispatcher\EventDispatcher;

use Symfony\Component\Form\Forms;
use Symfony\Component\Form\Extension\HttpFoundation\HttpFoundationExtension;
use Symfony\Component\Form\FormRenderer;
use Symfony\Component\Form\Extension\Templating\TemplatingRendererEngine;
use Symfony\Component\Form\Extension\Validator\ValidatorExtension;

use Symfony\Component\HttpFoundation\Session\Session;

use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Loader\YamlFileLoader;
use Symfony\Component\Routing\Router;

use Symfony\Component\Templating\PhpEngine;
use Symfony\Component\Templating\Loader\FilesystemLoader;

use Symfony\Component\Translation\Translator;
use Symfony\Component\Translation\Loader\YamlFileLoader as TranslationYamlLoader;
use Symfony\Component\Translation\MessageSelector;

use Symfony\Component\Validator\Validation;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Sylight\Logger\ArrayHandler;

/**
 * ContainerFactory
 *
 * Build the applications service container
 * @author Adrian Pemsel <adrian@koony.de>
 */
class ContainerFactory
{
  /**
   * Buld an applications service container
   * @param  Application $app     Application
   * @param  string      $rootDir Root dir of the application
   * @param  string      $env     Environment the application runs in
   * @return Pimple\Container     Container
   */
  public static function create(Application $app, string $rootDir, string $env) : Container
  {
    $container = new Container();
    
    // Set service arguments
    $container["env"] = $env;
    $container["root_dir"] = $rootDir;
    $container["config_dir"] = "$rootDir/config";
    $container["data_dir"] = "$rootDir/data";
    $container["cache_dir"] = $env == "prod" ? "$rootDir/cache/routing" : null;
    
    // Define services
    
    // Configuration
    $container["config"] = function($c) {
      return new Configuration($c["config_dir"]."/app.yml", $c["config_dir"]."/app.local.yml");
    };
    
    // Symfony RequestContext
    $container["requestContext"] = function($c) {
      return new RequestContext('', 'GET', 'koony.de');
    };
    
    // Symfony Session
    $container["session"] = function($c) {
      $session = new Session();
      $session->start();
      
      return $session;
    };
    
    // Symfony Router
    $container["router"] = function($c) {
      $loader = new YamlFileLoader(new FileLocator([$c["config_dir"]]));

      return new Router($loader, "routes.yml", [
        "cache_dir" => $c["cache_dir"],
        "debug" => $c["env"] == "dev"
      ], $c["requestContext"]);
    };
    
    // Symfony validation
    $container["validation"] = function($c) {
      $validator = Validation::createValidatorBuilder()
        ->setTranslator($c["translation"])
        ->setTranslationDomain(null)
        ->getValidator();
      return $validator;
    };
    
    // Symfony translation
    $container["translation"] = function($c) {
      $translator = new Translator('de_DE', new MessageSelector());
      $translator->addLoader('yaml', new TranslationYamlLoader());
      $translator->addResource('yaml', $c['config_dir'].'/translation.de.yml', 'de_DE', null);
      
      return $translator;
    };
    
    // Symfony form
    $container["formFactory"] = function($c) {
      return Forms::createFormFactoryBuilder()
        ->addExtension(new HttpFoundationExtension())
        ->addExtension(new ValidatorExtension($c["validation"]))
        ->getFormFactory();
    };
    
    // SlotsHelper
    $container["helper.slots"] = function($c) {
      return new Helper\SlotsHelper();
    };
    
    // HtmlHelper
    $container["helper.html"] = function($c) {
      return new Helper\HtmlHelper();
    };
    
    // FormHelper
    $container["helper.form"] = function($c) {
      $tre = new TemplatingRendererEngine($c["templating"], [
        $c["root_dir"]."/src/Frontend/View/Form"
      ]);
      
      return new Helper\FormHelper(new FormRenderer($tre));
    };
    
    // Translator Helper
    $container["helper.translator"] = function($c) {
      return new Helper\TranslatorHelper($c["translation"]);
    };
    
    // Event Dispatcher
    $container["event"] = function($c) {
      return new EventDispatcher();
    };
        
    // Templating Engine
    $container["templating"] = function($c) {
      return new PhpEngine(new TemplateNameParser(), new FilesystemLoader([$c["module_dir"].'/View/%name%.php', $c["root_dir"]."/src/Frontend/View/%name%.php", "%name%.php"]));
    };
    
    // Application User
    $container["user"] = function($c) use($app) {
      return new User($app);
    };
    
    // Monolog logger
    $container["logger"] = function($c) {
      $log = new Logger('app');
      $log->pushHandler($c["logger.array"], Logger::INFO);
      $log->pushHandler(new StreamHandler($c["root_dir"]."/log/application.log", $c["env"] == "prod" ? Logger::NOTICE : Logger::DEBUG));
      return $log;
    };
    
    // Logging for the dev console
    $container["logger.array"] = function($c) {
      return new ArrayHandler(Logger::NOTICE);
    };
    
    return $container;
  }
}
