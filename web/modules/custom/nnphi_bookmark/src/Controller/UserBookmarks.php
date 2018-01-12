<?php

namespace Drupal\nnphi_bookmark\Controller;

use Drupal\Core\Ajax\AjaxResponse;
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
    $flagging->delete();
    drupal_set_message($this->t('The bookmark has been deleted.'));
    if ($request->isXmlHttpRequest()) {
      $response = new AjaxResponse();
      $response->addCommand(new RefreshCommand());
      return $response;
    }
    $url = Url::fromRoute('nnphi_bookmark.user_list', ['user' => $flagging->getOwnerId()]);
    return new RedirectResponse($url->toString());
  }
}