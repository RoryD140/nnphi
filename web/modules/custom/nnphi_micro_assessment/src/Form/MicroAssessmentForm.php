<?php

namespace Drupal\nnphi_micro_assessment\Form;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\node\NodeInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class MicroAssessmentForm extends FormBase {

  public function __construct(EntityTypeManagerInterface $entityTypeManager) {
    $this->entityTypeManager = $entityTypeManager;
  }

  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager')
    );
  }

  public function getFormId() {
    return 'micro_assessment_form';
  }

  public function buildForm(array $form, FormStateInterface $form_state, NodeInterface $node = NULL) {
    if (null === $node) {
      throw new \InvalidArgumentException('Missing node argument');
    }
    $options = [];

    $form['node'] = [
      '#type' => 'value',
      '#value' => $node,
    ];

    $answers = $node->get('field_ma_answers');
    foreach ($answers as $delta => $answer) {
      $options[$delta] = $answer->value;
    }

    $form['choice'] = [
      '#type' => 'radios',
      '#options' => $options,
      '#required' => TRUE,
    ];

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Submit'),
    ];

    return $form;
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {
    /** @var NodeInterface $node */
    $node = $form_state->getValue('node');
    $form_state->setRedirect('entity.node.canonical', ['node' => $node->id()],
      ['query' => ['choice' => $form_state->getValue('choice')]]);
  }

}
