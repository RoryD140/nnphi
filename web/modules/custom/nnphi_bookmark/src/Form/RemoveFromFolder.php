<?php

namespace Drupal\nnphi_bookmark\Form;

use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\CloseDialogCommand;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\flag\FlaggingInterface;
use Drupal\nnphi_bookmark\BookmarkFolderService;
use Drupal\nnphi_bookmark\Entity\BookmarkFolderInterface;
use Drupal\node\NodeStorageInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class RemoveFromFolder extends FormBase {

  /**
   * @var \Drupal\node\NodeStorageInterface
   */
  private $nodeStorage;

  /**
   * @var \Drupal\nnphi_bookmark\BookmarkFolderService
   */
  private $folderService;

  public function __construct(NodeStorageInterface $nodeStorage, BookmarkFolderService $bookmarkFolderService) {
    $this->nodeStorage = $nodeStorage;
    $this->folderService = $bookmarkFolderService;
  }

  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager')->getStorage('node'),
      $container->get('nnphi_bookmark.folder')
    );
  }

  public function getFormId() {
    return 'bookmark_folder_remove_from_folder';
  }

  public function buildForm(array $form, FormStateInterface $form_state,
                            FlaggingInterface $flagging = NULL, BookmarkFolderInterface $bookmarkFolder = NULL) {
    $form['bookmark'] = [
      '#type' => 'value',
      '#value' => $flagging,
    ];

    $form['folder'] = [
      '#value' => $bookmarkFolder,
      '#type' => 'value',
    ];

    $node = $this->nodeStorage->load($flagging->entity_id->value);

    $form['description'] = [
      '#markup' => $this->t('Are you sure you want to remove "@node" from folder "@folder?"', ['@node' => $node->label(), '@folder' => $bookmarkFolder->label()]),
    ];

    $form['actions'] = [
      '#type' => 'actions',
    ];

    $form['actions']['cancel'] = [
      '#type' => 'button',
      '#value' => $this->t('Cancel'),
      '#ajax' => [
        'callback' => [$this, 'closeModal'],
      ],
    ];

    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Remove from Folder'),
    ];

    return $form;
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {
    /** @var BookmarkFolderInterface $folder */
    $folder = $form_state->getValue('folder');
    /** @var FlaggingInterface $flagging */
    $flagging = $form_state->getValue('bookmark');
    $form_state->setRedirect('entity.bookmark_folder.canonical', ['user' => $folder->getOwnerId(), 'bookmark_folder' => $folder->id()]);
    return $this->folderService->removeBookmarkFromFolder($flagging, $folder);
  }

  /**
   * Cancel button submit callback.
   */
  public function cancelFormSubmit(array &$form, FormStateInterface $form_state) {
    $form_state->setRedirect('entity.bookmark_folder.canonical', ['bookmark_folder' => $form_state->getValue('folder')->id()]);
  }

  /**
   * Cancel button AJAX callback.
   */
  public function closeModal() {
    $response = new AjaxResponse();
    $response->addCommand(new CloseDialogCommand());
    return $response;
  }

}
