<?php

namespace Drupal\nnphi_general\Plugin\Filter;

use Drupal\filter\Plugin\FilterBase;
use Drupal\filter\FilterProcessResult;

/**
    * @Filter(
 *   id = "filter_iframe",
 *   title = @Translation("Responsive iFrame filter"),
 *   description = @Translation("Wrap iFrames in responsive embed class"),
 *   type = Drupal\filter\Plugin\FilterInterface::TYPE_TRANSFORM_IRREVERSIBLE,
 * )
 */
class FilterIframe extends FilterBase {
  public function process($text, $langcode) {

    // Wrap iFrames in responsive embed divs
    $pattern = ['<iframe', '</iframe>'];
    $replacement = ['<div class="iframe-wrapper"><iframe', '</iframe></div>'];

    $filtered_text = str_replace($pattern, $replacement, $text);
    return new FilterProcessResult($filtered_text);
  }
}