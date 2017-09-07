<?
use PHPUnit\Framework\TestCase;
use Sylight\EscapedString;

class EscapedStringTest extends TestCase
{
  public function testEscaped() {
    $es = new EscapedString("foo <bar> baz");
    $this->assertEquals("foo &lt;bar&gt; baz", (string) $es);
  }

  public function testRaw() {
    $es = new EscapedString("foo <bar> baz");
    $this->assertEquals("foo <bar> baz", $es->raw);
  }
  
  public function testToInteger() {
    $es = new EscapedString("1234");
    $this->assertEquals(1234, $es->toInteger());
    $this->assertInternalType("integer", $es->toInteger());

    $es = new EscapedString("1234 Foo");
    $this->assertEquals(1234, $es->toInteger());
    $this->assertInternalType("integer", $es->toInteger());
  }

  public function testToFloat() {
    $es = new EscapedString("1234.567");
    $this->assertEquals(1234.567, $es->toFloat());
    $this->assertInternalType("float", $es->toFloat());

    $es = new EscapedString("1234.567 Foo");
    $this->assertEquals(1234.567, $es->toFloat());
    $this->assertInternalType("float", $es->toFloat());
  }

}
