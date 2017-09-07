<?
namespace Sylight\Test;

use PHPUnit\Framework\TestCase;
use Sylight\Application;

use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBag;
use Symfony\Component\HttpFoundation\Session\Attribute\AttributeBag;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;

class FrameworkTestCase extends TestCase
{
  protected $app;
  
  public function setUp() {
    $dir_root = realpath(dirname(__FILE__)."/../../");
    $this->app = new Application($dir_root, "dev");
    
    $storage = new MockArraySessionStorage();
    $session = new Session($storage, new AttributeBag(), new FlashBag());
    $this->app["session"] = $session;
  }
}
