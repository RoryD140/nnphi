<?php

namespace Drupal\nnphi_bookmark\Controller;

use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\OpenModalDialogCommand;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Url;
use Drupal\flag\FlaggingInterface;
use Drupal\nnphi_bookmark\Ajax\RefreshCommand;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

class UserBookmarks extends ControllerBase {

  /**
   * Flag delete route callback.
   *
   * @param \Drupal\flag\FlaggingInterface $flagging
   */
  public function delete(Request $request, FlaggingInterface $flagging) {
    $form = $this->formBuilder()->getForm(\Drupal\nnphi_bookmark\Form\BookmarkDeleteForm::class, $flagging);
    if ($request->isXmlHttpRequest()) {
      $response = new AjaxResponse();
      $response->addCommand(new OpenModalDialogCommand($this->t('Remove this training?'), $form, ['width' => '60%']));
      return $response;
    }
    return $form;
  }
}