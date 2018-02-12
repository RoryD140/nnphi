<?php

namespace Drupal\nnphi_bookmark\Form;

use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\OpenModalDialogCommand;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormBuilderInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Link;
use Drupal\Core\Render\Element;
use Drupal\nnphi_bookmark\Ajax\RefreshCommand;
use Drupal\nnphi_bookmark\BookmarkFolderService;
use Drupal\user\UserInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Url;
use Drupal\nnphi_bookmark\Entity\BookmarkFolderInterface;
use Drupal\Component\Serialization\Json;

class ManageBookmarkFolders extends FormBase {

  /**
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * @var \Drupal\nnphi_bookmark\BookmarkFolderService
   */
  protected $folderService;

  /**
   * @var \Drupal\Core\Entity\EntityStorageInterface;
   */
  private $folderStorage;

  public function getFormId() {
    return 'manage_bookmark_folders';
  }

  public function __construct(EntityTypeManagerInterface $entityTypeManager, BookmarkFolderService $folderService) {
    $this->entityTypeManager = $entityTypeManager;
    $this->folderService = $folderService;
  }

  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('nnphi_bookmark.folder')
    );
  }

  public function buildForm(array $form, FormStateInterface $form_state, UserInterface $user = NULL) {
    if (!$user) {
      throw new \InvalidArgumentException('Missing account');
    }

    $form['#prefix'] = '<div id="manage-folders-form">';
    $form['#suffix'] = '</div>';

    $header = [
      'name' => ['data-sort-default' => 1, 'data' => $this->t('Name'), 'class' => ['sort-column', 'folder-name-cell']],
      'opts' => ['data' => '', 'data-sort-method' => 'none', 'class' => 'folder-options-cell'],
    ];
    $rows = [];
    $fids = $this->entityTypeManager->getStorage('bookmark_folder')->getQuery()
      ->condition('uid', $user->id())
      ->execute();
    if (empty($fids)) {
      return $form;
    }
    /** @var \Drupal\nnphi_bookmark\Entity\BookmarkFolderInterface[] $folders */
    $folders = $this->folderStorage()->loadMultiple($fids);
    foreach ($folders as $folder) {
      $rows[$folder->id()] = [
        'name' => [
          'data-sort' => $folder->label(),
          'data' => $folder->toLink($folder->label()),
          'class' => 'folder-title'
        ],
        'opts' => ['data' => $this->getFolderOptions($folder)],
      ];
    }

    if (empty($form_state->getValue('folders'))) {

      $form['folders'] = [
        '#type' => 'tableselect',
        '#header' => $header,
        '#options' => $rows,
        '#attributes' => [
          'class' => [
            'user-bookmarks-table',
            'user-bookmarks-folders-table',
            // Bootstrap table classes.
            'table',
            'table-responsive',
            'table-hover'
          ],
        ],
        '#after_build' => [[$this, 'foldersAfterBuild']],
        '#js_select' => FALSE,
      ];

      $form['submit'] = [
        '#type' => 'submit',
        '#value' => $this->t('Combine Selected Folders'),
        '#attributes' => [
          'class' => ['visually-hidden'],
        ],
        '#ajax' => [
          'callback' => [$this, 'ajaxSubmit'],
          'wrapper' => 'manage-folders-form',
          'method' => 'replace',
        ],
      ];
    }
    else {
      $selected = $form_state->getValue('folders');
      /** @var BookmarkFolderInterface[] $folders */
      $folders = $this->folderStorage()->loadMultiple($selected);
      $folder_opts = [];
      foreach ($folders as $folder) {
        $folder_opts[$folder->id()] = $folder->label();
      }

      $form['folders'] = [
        '#type' => 'value',
        '#value' => $selected,
      ];

      $form['destination'] = [
        '#description' => $this->t('Select the folder to move all bookmarks to.'),
        '#type' => 'radios',
        '#options' => $folder_opts,
        '#required' => TRUE,
        '#title' => $this->t('Destination'),
      ];

      $form['combine'] = [
        '#type' => 'submit',
        '#value' => $this->t('Combine'),
        '#ajax' => [
          'callback' => [$this, 'refreshSubmit'],
          'wrapper' => 'manage-folders-form',
          'method' => 'replace',
        ],
        '#submit' => [[$this, 'combineSubmit']],
      ];
    }

    return $form;
  }

  /**
   * Form submit function.
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $form_state->setRebuild();
  }

  /**
   * @param $form
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *
   */
  public function ajaxSubmit(&$form, FormStateInterface $form_state) {
    return $form;
  }

  public function combineSubmit(array &$form, FormStateInterface $form_state) {
    $destination = $this->folderStorage()->load($form_state->getValue('destination'));
    $sources = $this->folderStorage()->loadMultiple($form_state->getValue('folders'));
    try {
      $this->folderService->combineFolders($destination, $sources);
      drupal_set_message($this->t('The selected folders have been combined into the "@dest" folder.', ['@dest' => $destination->label()]));
    }
    catch (\Exception $exception) {
      $this->logger('nnphi_bookmark')->error('Unable to combine folders <pre>@fids</pre>. Error: @err',
        ['@fids' => print_r($form_state->getValue('folders')), '@err' => $exception->getMessage()]);
      drupal_set_message($this->t('An error occurred. Please try again later.'), 'error');
    }
  }

  public function refreshSubmit(array &$form, FormStateInterface $form_state) {
    $response = new AjaxResponse();
    $response->addCommand(new RefreshCommand());
    return $response;
  }

  public static function foldersPreRender($element) {
    $element['#header'][0]['data-sort-method'] = 'none';
    foreach (Element::children($element) as $fid) {
      $element[$fid]['#attributes']['v-model.lazy'] = 'checkedFolders';
    }
    return $element;
  }

  public function foldersAfterBuild($element) {
    $element['#pre_render'][] = [self::class, 'foldersPreRender'];
    foreach (Element::children($element) as $fid) {
      $element[$fid]['#attributes']['v-model.lazy'] = 'checkedFolders';
    }
    return $element;
  }

  protected function getFolderOptions(BookmarkFolderInterface $folder) {
    $links['options_toggle'] = [
      '#type' => 'button',
      '#value' => '...',
      '#url' => '/',
      '#attributes' => [
        'class' => ['dropdown','dropdown-toggle'],
        'id' => 'dropdownMenuButton',
        'data-toggle' => 'dropdown',
        'aria-haspopup' => 'true',
        'aria-expanded' => 'false',
        'role' => 'button'
      ],
      '#prefix' => '<div class="dropdown">'
    ];

    $links['open'] = [
      '#prefix' => '<div class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenuButton">',
      '#title' => $this->t('Open'),
      '#type' => 'link',
      '#url' => $folder->toUrl(),
      '#attributes' => ['class' => ['dropdown-item']]
    ];

    $links['edit'] = [
      '#title' => $this->t('Edit'),
      '#type' => 'link',
      '#url' => Url::fromRoute('entity.bookmark_folder.edit_form',
        ['user' => $folder->getOwnerId(), 'bookmark_folder' => $folder->id()],
        ['attributes' => ['class' => ['use-ajax', 'dropdown-item'], 'data-dialog-type' => 'modal', 'data-dialog-options' => Json::encode(['width' => '75%'])]]
      ),
    ];

    $links['delete'] = [
      '#title' => $this->t('Delete'),
      '#type' => 'link',
      '#url' => Url::fromRoute('entity.bookmark_folder.delete_form',
        ['user' => $folder->getOwnerId(), 'bookmark_folder' => $folder->id()],
        ['attributes' => ['class' => ['use-ajax', 'dropdown-item'], 'data-dialog-type' => 'modal', 'data-dialog-options' => Json::encode(['width' => '75%'])]])
    ];

    $links['suffix'] = [
      '#type' => 'markup',
      '#markup' => '</div></div>'
    ];

    return $links;
  }

  /**
   * @return \Drupal\Core\Entity\EntityStorageInterface
   */
  private function folderStorage() {
    if ($this->folderStorage === NULL) {
      $this->folderStorage = $this->entityTypeManager->getStorage('bookmark_folder');
    }
    return $this->folderStorage;
  }

}