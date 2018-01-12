<?php

namespace Drupal\nnphi_bookmark\Ajax;

use Drupal\Core\Ajax\CommandInterface;

class RefreshCommand implements CommandInterface {

  public function render() {
    return [
      'command' => 'refreshPage',
    ];
  }

}
