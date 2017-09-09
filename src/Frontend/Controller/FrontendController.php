<?
namespace Sylight\Frontend\Controller;

use Sylight\Controller;
use Symfony\Component\HttpFoundation\Request;

class FrontendController extends Controller
{
  public function indexAction(Request $request)
  {
    return $this->renderText("Hello World");
  }
}
