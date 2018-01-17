<?php

namespace Drupal\nnphi_bookmark\Form;

use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\CloseDialogCommand;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\flag\FlaggingInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class BookmarkDeleteForm extends FormBase {

  /**
   * @var \Drupal\node\NodeStorageInterface
   */
  protected $nodeStorage;

  public function getFormId() {
    return 'bookmark_delete_form';
  }

  public function __construct(EntityTypeManagerInterface $entityTypeManager) {
    $this->nodeStorage = $entityTypeManager->getStorage('node');
  }

  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager')
    );
  }

  public function buildForm(array $form, FormStateInterface $form_state, FlaggingInterface $bookmark = NULL) {
    if ($bookmark === NULL) {
      throw new \InvalidArgumentException('Missing bookmark');
    }

    $form['bookmark'] = [
      '#type' => 'value',
      '#value' => $bookmark,
    ];

    /** @var \Drupal\node\NodeInterface $node */
    $nid = $bookmark->get('flagged_entity')->getString();
    $node = $this->nodeStorage->load($nid);

    $form['question'] = [
      '#type' => 'markup',
      '#markup' => $this->t('Are you sure you want to remove %label from your Bookmarked Trainings?',
        ['%label' => $node->label()]),
    ];

    $form['actions'] = [
      '#type' => 'actions',
    ];

    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Remove'),
    ];

    $form['actions']['cancel'] = [
      '#type' => 'button',
      '#value' => $this->t('Cancel'),
      '#ajax' => [
        'callback' => [$this, 'closeModal'],
      ],
    ];

    return $form;
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {
    /** @var FlaggingInterface $bookmark */
    $bookmark = $form_state->getValue('bookmark');
    try {
      $bookmark->delete();
      drupal_set_message($this->t('The bookmark has been deleted.'));
    }
    catch (\Exception $exception) {
      $this->logger('nnphi_bookmark')->error('Unable to delete flagging @fid. Error: %err',
        ['@fid' => $bookmark->id(), '%err' => $exception->getMessage()]);
    }
    $form_state->setRedirect('nnphi_bookmark.user_list', ['user' => $bookmark->getOwnerId()]);
  }

  public function closeModal(array &$form, FormStateInterface $form_state) {
    $response = new AjaxResponse();
    $response->addCommand(new CloseDialogCommand());
    return $response;
  }

}