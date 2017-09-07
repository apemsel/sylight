<?
use PHPUnit\Framework\TestCase;
use Sylight\Configuration;

class ConfigurationTest extends TestCase
{
  public function testConstruct() {
    $c = new Configuration();
    $this->assertInstanceOf(Configuration::class, $c, "in memory");
  }
  
  public function testSetGet() {
    $c = new Configuration();
    $c->set("foo.bar.baz", 123);
    $this->assertEquals(123, $c->get("foo.bar.baz"));
  }
}
