<?php

namespace Drupal\biodex_component\Plugin\Block;

use Drupal\block_content\Plugin\Block\BlockContentBlock as CoreBlockContentBlock;
use Drupal\Core\Form\FormStateInterface;

class BlockContentBlock extends CoreBlockContentBlock {
  public function blockForm($form, FormStateInterface $form_state) {
    $form = parent::blockForm($form, $form_state);
    $form['display_controls'] = [
      '#type' => 'select',
      '#options' => [
        'top' => $this->t('Controls on top'),
        'bottom' => $this->t('Controls on bottom'),
      ],
      '#default_value' => isset($this->configuration['display_controls']) ? $this->configuration['display_controls'] : NULL,
      '#title' => t('Controls display'),
      '#required' => TRUE,
    ];

    return $form;
  }

  public function blockSubmit($form, FormStateInterface $form_state) {
    $this->configuration['display_controls'] = $form_state->getValue('display_controls');
    parent::blockSubmit($form, $form_state);
  }
}
