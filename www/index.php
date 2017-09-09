<?
use Symfony\Component\HttpFoundation\Request;
use Sylight\Application;

$dir_root = realpath(dirname(__FILE__)."/../");

// init composer autoloader
require "{$dir_root}/vendor/autoload.php";

// create http request from $GET, $POST, $COOKIES etc.
// create application and use its built-in kernel to turn the request into a response
$app = new Application($dir_root, is_file($dir_root."/config/dev") ? "dev" : "prod");
$app->handle(Request::createFromGlobals())->send();
