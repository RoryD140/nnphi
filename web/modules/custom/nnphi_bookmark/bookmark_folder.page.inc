<?php

/**
 * @file
 * Contains bookmark_folder.page.inc.
 *
 * Page callback for Bookmark folder entities.
 */

use Drupal\Core\Render\Element;

/**
 * Prepares variables for Bookmark folder templates.
 *
 * Default template: bookmark_folder.html.twig.
 *
 * @param array $variables
 *   An associative array containing:
 *   - elements: An associative array containing the user information and any
 *   - attributes: HTML attributes for the containing element.
 */
function template_preprocess_bookmark_folder(array &$variables) {
  // Fetch BookmarkFolder Entity Object.
  $bookmark_folder = $variables['elements']['#bookmark_folder'];

  // Helpful $content variable for templates.
  foreach (Element::children($variables['elements']) as $key) {
    $variables['content'][$key] = $variables['elements'][$key];
  }
}
