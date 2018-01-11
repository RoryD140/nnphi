<?php

namespace Drupal\nnphi_bookmark\Form;

use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\RendererInterface;
use Drupal\user\UserInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ManageBookmarks extends FormBase {

  /**
   * @var \Drupal\flag\Entity\Storage\FlaggingStorageInterface
   */
  protected $flaggingStorage;

  /**
   * @var \Drupal\node\NodeStorageInterface
   */
  protected $nodeStorage;

  /**
   * @var \Drupal\Core\Entity\EntityViewBuilderInterface
   */
  protected $nodeViewer;

  /**
   * @var \Drupal\Core\Render\RendererInterface
   */
  protected $renderer;

  /**
   * @var \Drupal\Core\Datetime\DateFormatterInterface
   */
  protected $dateFormatter;

  public function getFormId() {
    return 'nnphi_bookmark_manage_bookmarks';
  }

  public function __construct(EntityTypeManagerInterface $entityTypeManager, RendererInterface $renderer,
                              DateFormatterInterface $dateFormatter) {
    $this->flaggingStorage = $entityTypeManager->getStorage('flagging');
    $this->nodeStorage = $entityTypeManager->getStorage('node');
    $this->nodeViewer = $entityTypeManager->getViewBuilder('node');
    $this->renderer = $renderer;
    $this->dateFormatter = $dateFormatter;
  }

  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('renderer'),
      $container->get('date.formatter')
    );
  }

  public function buildForm(array $form, FormStateInterface $form_state, UserInterface $account = NULL) {
    $header = [
      'name' => $this->t('Name'),
      'created' => ['data-sort-default' => 1, 'data' => $this->t('Created')],
      'rating' => $this->t('Rating'),
      'options' => '',
    ];

    $fids = $this->flaggingStorage->getQuery()
      ->condition('flag_id', 'bookmark')
      ->condition('uid', $account->id())
      ->notExists('field_bookmark_folder')
      ->sort('created', 'DESC')
      ->execute();
    if (empty($fids)) {
      return FALSE;
    }

    $options = [];

    /** @var \Drupal\flag\FlaggingInterface $flag */
    foreach ($this->flaggingStorage->loadMultiple($fids) as $flag) {
      $nid = $flag->get('entity_id')->getString();
      /** @var \Drupal\node\NodeInterface $node */
      $node = $this->nodeStorage->load($nid);
      $rating = '';
      $raw_rating = 0;
      if ($node->hasField('field_training_overall_rating') && $node->get('field_training_overall_rating')->count()) {
        $field = $node->get('field_training_overall_rating');
        $raw_rating = $field->getString();
        $rating = $this->nodeViewer->viewField($field, 'mini');
        $rating = $this->renderer->render($rating);
      }
      $date = $flag->get('created')->getString();
      $options[$flag->id()] = [
        'name' => ['data-sort' => $node->label(), 'data' => $node->toLink($node->label())],
        'created' => ['data-sort' => $date, 'data' => $this->dateFormatter->format($date, 'custom', 'm/d/Y g:i A')],
        'rating' => ['data-sort' => $raw_rating, 'data' => $rating],
        'options' => '',
      ];
    }

    $form['bookmarks'] = [
      '#type' => 'tableselect',
      '#header' => $header,
      '#options' => $options,
      '#js_select' => FALSE,
      '#attributes' => [
        'class' => ['user-bookmarks-table', 'orphan-flags'],
      ],
    ];

    return $form;
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {
    // No submits.
  }

}