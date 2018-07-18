<?php

namespace Drupal\nnphi_micro_assessment\Form;

use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\node\NodeInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class MicroAssessmentForm extends FormBase {

  /**
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * @var \Drupal\Core\Database\Connection
   */
  protected $database;

  /**
   * @var \Drupal\Component\Datetime\TimeInterface
   */
  protected $time;

  public function __construct(EntityTypeManagerInterface $entityTypeManager, Connection $database,
                              TimeInterface $time) {
    $this->entityTypeManager = $entityTypeManager;
    $this->database = $database;
    $this->time = $time;
  }

  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('database'),
      $container->get('datetime.time')
    );
  }

  public function getFormId() {
    return 'micro_assessment_form';
  }

  public function buildForm(array $form, FormStateInterface $form_state, NodeInterface $node = NULL, $selector = '') {
    if (null === $node) {
      throw new \InvalidArgumentException('Missing node argument');
    }
    $options = [];

    $form['node'] = [
      '#type' => 'value',
      '#value' => $node,
    ];

    if (empty($selector)) {
      $selector = 'random-quiz-form';
    }

    $form['wrapper'] = [
      '#prefix' => "<div id='$selector' class='random-quiz-form'>",
      '#suffix' => '</div>',
    ];

    $selected_choice = $form_state->getValue('choice');
    if (!is_null($selected_choice)) {
      $form['wrapper']['message'] = $this->choiceMessage($selected_choice, $node);
    }
    else {
      $form['wrapper']['quiz'] = $this->entityTypeManager->getViewBuilder('node')->view($node, 'teaser');

      $answers = $node->get('field_ma_answers');
      foreach ($answers as $delta => $answer) {
        $options[$delta] = $answer->value;
      }

      $form['wrapper']['choice'] = [
        '#type' => 'radios',
        '#options' => $options,
        '#required' => TRUE,
      ];

      $form['wrapper']['submit'] = [
        '#type' => 'submit',
        '#value' => $this->t('Submit'),
        '#ajax' => [
          'wrapper' => $selector,
          'callback' => '::ajaxCallback',
          'disable-refocus' => TRUE,
          'url' => Url::fromRoute('nnphi_micro_assessment.random_quiz_result', ['node' => $node->id()]),
          'options' => ['query' => ['selector' => $selector]],
        ],
      ];
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $choice = $form_state->getValue('choice');
    // Check if the answer is correct.
    /** @var NodeInterface $node */
    $node = $form_state->getValue('node');
    $selected_option = $node->get('field_ma_answers')->get($choice);
    // Write the record.
    \Drupal::database()->insert('micro_assessment_response')
      ->fields([
        'nid' => $node->id(),
        'choice' => $selected_option->value,
        'uid' => $this->currentUser()->id(),
        'correct' => $this->choiceIsCorrect($choice, $node),
        'timestamp' => $this->time->getRequestTime(),
      ])
      ->execute();
    $form_state->setRebuild();
  }

  public function ajaxCallback($form, FormStateInterface $form_state) {
    return $form['wrapper'];
  }

  /**
   * Get the render element for the selected choice.
   *
   * @param $choice
   * @param \Drupal\node\NodeInterface $node
   * @return array
   * @throws \Drupal\Core\Entity\EntityMalformedException
   * @throws \Drupal\Core\TypedData\Exception\MissingDataException
   */
  protected function choiceMessage($choice, NodeInterface $node) {
    $message = '';
    // Get the content referenced by the micro assessment.
    /** @var NodeInterface $content */
    $content = $node->get('field_ma_selected_content')->get(0)->entity;
    $t_args = [
      '@url' => $content->toUrl()->toString(),
    ];
    if ($this->choiceIsCorrect($choice, $node)) {
      $correct_class= 'quiz-correct-answer';
      $message = t('Right answer! Learn more <a href="@url">here.</a>', $t_args);
    }
    else {
      $correct_class= 'quiz-incorrect-answer';
      $message = t("Sorry! That's not the right answer. Learn more <a href='@url'>here.</a>", $t_args);
    }
    return [
      '#type' => 'inline_template',
      '#template' => '<span class="result-message ' . $correct_class . '">' . $message . '</span>',
    ];
  }

  /**
   * Check if an answer to a quiz is correct.
   *
   * @param $choice
   * @param \Drupal\node\NodeInterface $node
   * @return bool
   */
  private function choiceIsCorrect($choice, NodeInterface $node) {
    $answer = $node->get('field_ma_answers')->get($choice);
    return $answer->correct;
  }

}
