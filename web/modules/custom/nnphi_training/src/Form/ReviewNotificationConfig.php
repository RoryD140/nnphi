<?php

namespace Drupal\nnphi_training\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\State\StateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ReviewNotificationConfig extends FormBase {

  const STATE_KEY = 'training_review_notification_email';

  /**
   * @var \Drupal\Core\State\StateInterface
   */
  protected $state;

  /**
   * ReviewNotificationConfig constructor.
   *
   * @param \Drupal\Core\State\StateInterface $state
   */
  public function __construct(StateInterface $state) {
    $this->state = $state;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('state')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'training_notification_config';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['email'] = [
      '#type' => 'email',
      '#title' => $this->t('Email address'),
      '#description' => $this->t('The email address to send review moderation notifications.'),
      '#default_value' => $this->state->get(self::STATE_KEY),
      '#required' => TRUE,
    ];

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Submit'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->state->set(self::STATE_KEY, $form_state->getValue('email'));
    drupal_set_message($this->t('The configuration has been saved.'));
  }

}
