<?php

namespace Drupal\nnphi_bookmark\Form;

use Drupal\Component\Serialization\Json;
use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;
use Drupal\nnphi_bookmark\BookmarkFolderService;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Form controller for Bookmark folder edit forms.
 *
 * @ingroup nnphi_bookmark
 */
class BookmarkFolderForm extends ContentEntityForm {

  protected $folderService;

  public function __construct(\Drupal\Core\Entity\EntityManagerInterface $entity_manager, \Drupal\Core\Entity\EntityTypeBundleInfoInterface $entity_type_bundle_info = NULL, \Drupal\Component\Datetime\TimeInterface $time = NULL,
                              BookmarkFolderService $folderService) {
    parent::__construct($entity_manager, $entity_type_bundle_info, $time);
    $this->folderService = $folderService;
  }

  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity.manager'),
      $container->get('entity_type.bundle.info'),
      $container->get('datetime.time'),
      $container->get('nnphi_bookmark.folder')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    /* @var $entity \Drupal\nnphi_bookmark\Entity\BookmarkFolder */
    $form = parent::buildForm($form, $form_state);

    $entity = $this->entity;

    $form['uid']['#access'] = $this->currentUser()->hasPermission('administer bookmark folder entities');

    $form['default_bookmarks'] = [
      '#type' => 'hidden',
      '#default_value' => '',
      '#attributes' => [
        'class' => 'default-bookmarks',
      ],
    ];

    $form['default_entity'] = [
      '#type' => 'value',
      '#value' => $form_state->getStorage()['defaultEntity'],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    /** @var \Drupal\nnphi_bookmark\Entity\BookmarkFolderInterface $entity */
    $entity = &$this->entity;

    $status = parent::save($form, $form_state);

    switch ($status) {
      case SAVED_NEW:
        drupal_set_message($this->t('Created the %label folder.', [
          '%label' => $entity->label(),
        ]));
        break;

      default:
        drupal_set_message($this->t('Saved the %label folder.', [
          '%label' => $entity->label(),
        ]));
    }
    $form_state->setRedirect('nnphi_bookmark.user_list', ['user' => $entity->getOwnerId()]);

    $default_entity = $form_state->getValue('default_entity');
    if ($default_entity) {
      /** @var $default_entity \Drupal\Core\Entity\ContentEntityInterface */
      $entity_type = $default_entity->getEntityTypeId();
      if ($entity_type === 'flagging') {
        $this->folderService->addBookmarkToFolders($default_entity, [$entity->id()]);
      }
    }

    if ($default_bookmarks = $form_state->getValue('default_bookmarks')) {
      $flagging_storage = $this->entityTypeManager->getStorage('flagging');
      $default_bookmarks = Json::decode($default_bookmarks);
      $flaggings = $flagging_storage->loadMultiple($default_bookmarks);
      if ($flaggings) {
        /** @var \Drupal\flag\FlaggingInterface $flagging */
        foreach ($flaggings as $flagging) {
          if ($flagging->getOwnerId() == $this->currentUser()->id() || $this->currentUser()->hasPermission('administer users')) {
            $this->folderService->addBookmarkToFolders($flagging, [$entity->id()]);
          }
        }
      }
    }
  }

}
