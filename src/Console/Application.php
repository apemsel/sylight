<?
namespace Sylight\Console;

use Symfony\Component\Console\Application as sfConsoleApplication;
use Symfony\Component\Finder\Finder;
use Sylight\Application as SylightApplication;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

class Application extends sfConsoleApplication
{
  public $app;
  
  public function __construct(string $rootDir, string $env = "prod")
  {
    $this->app = new SylightApplication($rootDir, $env);
    
    // Monolog logger
    $this->app["logger"] = function($c) {
      $log = new Logger('console');
      $log->pushHandler(new StreamHandler($c["root_dir"]."/log/console.log", $c["env"] == "prod" ? Logger::NOTICE : Logger::DEBUG));
      return $log;
    };
    
    return parent::__construct("sylight", "1.0");
  }
  
  public function loadCommands()
  {
    $this->loadCommandsFromDir("src/Framework/Console/Command");
  }
  
  public function loadCommandsFromDir($dir)
  {
    $finder = new Finder();
    $finder->files()->in($dir)->name("*.php");
    foreach ($finder as $file) {
      $contents = $file->getContents();
      $ns = (preg_match('/namespace\s+(\S+);\s+/', $contents, $matches) != false) ? $matches[1] : '';
      $class = (preg_match('/class\s+(\S+)\s+/', $contents, $matches) !== false) ? $matches[1] :'';
      $className = '\\'.$ns.'\\'.$class;
      
      if (!class_exists($className, true)) {
        $this->app["logger"]->notice("ConsoleApplication: Unable to load command $className");
        continue;
      }
      
      $cmd = new $className();
      
      if (method_exists($cmd, "setApp")) {
        $cmd->setApp($this->app);
      }
      
      $this->add($cmd);
    }
  }
}
