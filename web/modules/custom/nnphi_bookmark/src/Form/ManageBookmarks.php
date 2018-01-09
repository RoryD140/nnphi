<?php

namespace Drupal\nnphi_bookmark\Form;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
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

  public function getFormId() {
    return 'nnphi_bookmark_manage_bookmarks';
  }

  public function __construct(EntityTypeManagerInterface $entityTypeManager) {
    $this->flaggingStorage = $entityTypeManager->getStorage('flagging');
    $this->nodeStorage = $entityTypeManager->getStorage('node');
    $this->nodeViewer = $entityTypeManager->getViewBuilder('node');
  }

  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager')
    );
  }

  public function buildForm(array $form, FormStateInterface $form_state, UserInterface $account = NULL) {
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
      if ($node->hasField('field_training_overall_rating') && $node->get('field_training_overall_rating')->count()) {
        $field = $node->get('field_training_overall_rating');
        $rating = $this->nodeViewer->viewField($field, 'full');
      }
      $options[$flag->id()] = [
        'name' => $node->toLink($node->label()),
        'date' => $flag->get('created')->getString(),
        'rating' => $rating,
        'options' => '',
      ];
    }

    $header = [
      'name' => $this->t('Name'),
      'date' => $this->t('Date Added'),
      'rating' => $this->t('Rating'),
      'options' => '',
    ];

    $form['bookmarks'] = [
      '#type' => 'tableselect',
      '#header' => $header,
      '#options' => $options,
    ];

    return $form;
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {
    // No submits.
  }

}