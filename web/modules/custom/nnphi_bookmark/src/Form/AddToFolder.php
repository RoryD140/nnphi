<?php

namespace Drupal\nnphi_bookmark\Form;

use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\CloseModalDialogCommand;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\flag\FlaggingInterface;
use Drupal\nnphi_bookmark\Ajax\RefreshCommand;
use Drupal\nnphi_bookmark\BookmarkFolderService;
use Symfony\Component\DependencyInjection\ContainerInterface;

class AddToFolder extends FormBase {

  /**
   * @var \Drupal\nnphi_bookmark\BookmarkFolderService
   */
  protected $folderService;

  public function __construct(BookmarkFolderService $folderService) {
    $this->folderService = $folderService;
  }

  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('nnphi_bookmark.folder')
    );
  }

  public function getFormId() {
    return 'nnphi_bookmark_add_to_folder';
  }

  public function buildForm(array $form, FormStateInterface $form_state, FlaggingInterface $flagging = NULL) {
    if (empty($flagging)) {
      throw new \InvalidArgumentException('Missing bookmark flagging');
    }

    $form['bookmark'] = [
      '#type' => 'value',
      '#value' => $flagging,
    ];

    $header = [
      'name' => ['data' => $this->t('Name')],
    ];
    /** @var \Drupal\nnphi_bookmark\Entity\BookmarkFolderInterface[] $folders */
    $folders = $this->folderService->getFoldersForUser($this->currentUser());
    // If the bookmark already belongs to folders, remove those folders from the list.
    if (!$flagging->get(BookmarkFolderService::FLAGGING_FOLDER_FIELD)->isEmpty()) {
      $vals = array_column($flagging->get(BookmarkFolderService::FLAGGING_FOLDER_FIELD)->getValue(), 'target_id');
      $vals = array_combine($vals, $vals);
      $folders = array_diff_key($folders, $vals);
    }
    $rows = [];
    foreach ($folders as $folder) {
      $rows[$folder->id()] = [
        'name' => ['data' => $folder->label()],
      ];
    }

    $form['folders'] = [
      '#type' => 'tableselect',
      '#options' => $rows,
      '#header' => $header,
      '#empty' => $this->t('You have not created any folders.'),
      '#after_build' => [[$this, 'foldersAfterBuild']],
      '#attributes' => [
        'class' => ['user-bookmarks-table'],
      ],
    ];

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Add to Selected'),
      '#ajax' => [
        'callback' => [\self::class, 'ajaxSubmit'],
      ],
    ];

    $form['#attached']['library'][] = 'nnphi_bookmark/manage_bookmarks';

    return $form;
  }

  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);
    $folders = $form_state->getValue('folders');
    if (empty($folders)) {
      $form_state->setError($form['folders'], $this->t('Please select a folder.'));
    }
  }

  public static function foldersPreRender($element) {
    $element['#header'][0]['data-sort-method'] = 'none';
    return $element;
  }

  public function foldersAfterBuild($element) {
    $element['#pre_render'][] = [self::class, 'foldersPreRender'];
    return $element;
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {
    $folders = array_filter($form_state->getValue('folders'));
    $bookmark = $form_state->getValue('bookmark');
    try {
      $this->folderService->addBookmarkToFolders($bookmark, $folders);
      drupal_set_message($this->t('Your bookmarks have been updated.'));
    }
    catch (\Exception $exception) {
      watchdog_exception('nnphi_bookmark', $exception);
      $this->logger('nnphi_bookmark')->error('Error adding bookmark @bid to folders @fids: @err',
        ['@bid' => $bookmark->id(), '@fids' => implode(',', $folders), '@err' => $exception->getMessage()]);
      drupal_set_message($this->t('An error occurred. Please try again later.'), 'error');
    }
  }

  /**
   * Submit button ajax callback.
   */
  public function ajaxSubmit(array $form, FormStateInterface $form_state) {
    $response = new AjaxResponse();
    $response->addCommand(new RefreshCommand());
    return $response;
  }

}