<?
namespace Sylight\Helper;


class SlotsHelper extends \Symfony\Component\Templating\Helper\SlotsHelper
{
  protected $appendSlots = [];
  /**
   * Append a given slot.
   *
   * This method starts an output buffer that will be
   * closed when the stop() method is called.
   *
   * @param string $name The slot name
   *
   * @throws \InvalidArgumentException if a slot with the same name is already started
   */
  public function append($name)
  {
    if (in_array($name, $this->appendSlots)) {
      throw new \InvalidArgumentException(sprintf('A slot named "%s" is already appended.', $name));
    }

    $this->appendSlots[] = $name;

    ob_start();
    ob_implicit_flush(0);
  }
  
  /**
   * End appending a slot
   */
  public function end()
  {
    if (!$this->appendSlots) {
      throw new \LogicException('No slot appended.');
    }

    $name = array_pop($this->appendSlots);
    $this->slots[$name] = $this->slots[$name] ?? "";
    $this->slots[$name] .= ob_get_clean();
  }
}
