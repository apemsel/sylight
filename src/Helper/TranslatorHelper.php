<?
/*
 * This file is based on the original templating translator helper from the
 * symfony framework bundle.
 */

namespace Sylight\Helper;

use Symfony\Component\Templating\Helper\Helper;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * TranslatorHelper.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class TranslatorHelper extends Helper
{
  protected $translator;
  /**
   * Constructor.
   *
   * @param TranslatorInterface $translator A TranslatorInterface instance
   */
  public function __construct(TranslatorInterface $translator)
  {
    $this->translator = $translator;
  }

  /**
   * @see TranslatorInterface::trans()
   */
  public function trans($id, array $parameters = array(), $domain = 'messages', $locale = null)
  {
    return $this->translator->trans($id, $parameters, $domain, $locale);
  }

  /**
   * @see TranslatorInterface::transChoice()
   */
  public function transChoice($id, $number, array $parameters = array(), $domain = 'messages', $locale = null)
  {
    return $this->translator->transChoice($id, $number, $parameters, $domain, $locale);
  }

  /**
   * {@inheritdoc}
   */
  public function getName()
  {
    return 'translator';
  }
}
