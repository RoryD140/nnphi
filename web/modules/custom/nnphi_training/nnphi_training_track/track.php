<?php

/**
 * @file
 * Handles counts of node views via AJAX with minimal bootstrap.
 */

use Drupal\Core\DrupalKernel;
use Symfony\Component\HttpFoundation\Request;

chdir('../../../..');

$autoloader = require_once 'autoload.php';

$kernel = DrupalKernel::createFromRequest(Request::createFromGlobals(), $autoloader, 'prod');
$kernel->boot();
$container = $kernel->getContainer();

$nid = filter_input(INPUT_POST, 'nid', FILTER_VALIDATE_INT);
$uid = filter_input(INPUT_POST, 'uid', FILTER_VALIDATE_INT);
if ($nid && $uid) {
  $container->get('request_stack')->push(Request::createFromGlobals());
  $container->get('nnphi_training_track.view_tracker')->recordView($nid, $uid);
}
