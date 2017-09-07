<?php

namespace Sylight\Logger;

use Monolog\Logger;
use Monolog\Handler\AbstractProcessingHandler;

/**
 * Store log in PHP array
 */
class ArrayHandler extends AbstractProcessingHandler
{
  protected $log;

  public function __construct($level = Logger::DEBUG, $bubble = true)
  {
      parent::__construct($level, $bubble);
  }

  /**
   * {@inheritdoc}
   */
  public function close()
  {
      return $this->log;
  }

  /**
   * {@inheritdoc}
   */
  protected function write(array $record)
  {
      $this->log[] = $record;
  }
  
  public function get()
  {
    return $this->log;
  }
  
  public function getHtml()
  {
    if (!count($this->log)) return "Everything is fine";
    $html = "";
    foreach($this->log as $record) {
      $html .= '<div class="'.strtolower($record["level_name"]).'">'.$record["formatted"].'</div>';
    }
    
    return $html;
  }
}
