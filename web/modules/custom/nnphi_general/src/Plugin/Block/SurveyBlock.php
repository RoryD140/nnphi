<?php

namespace Drupal\nnphi_general\Plugin\Block;

use Drupal\Core\Annotation\Translation;
use Drupal\Core\Block\Annotation\Block;
use Drupal\Core\Block\BlockBase;

/**
 * @Block(
 *   id = "qualtrics_survey",
 *   admin_label = @Translation("Qualtrics Survey"),
 *   category = @Translation("General")
 * )
 */
class SurveyBlock extends BlockBase {

  public function build() {
    $build['iframe'] = [
      '#type' => 'inline_template',
      '#template' => '<div class="embed-responsive"><iframe src="{{ url }}""></iframe></div> ',
      '#context' => [
        'url' => 'https://nnphi.az1.qualtrics.com/jfe/form/SV_4IA9bks2QpM5ua9',
      ],
    ];

    return $build;
  }

}
