<?php

namespace Drupal\nnphi_training\EventSubscriber;

use Drupal\Core\Config\ConfigEvents;
use Drupal\Core\Config\ConfigImporterEvent;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ConfigImportSubscriber implements EventSubscriberInterface {

  /**
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  public function __construct(EntityTypeManagerInterface $entityTypeManager) {
    $this->entityTypeManager = $entityTypeManager;
  }

  /**
   * Callback for config being imported.
   *
   * @param \Drupal\Core\Config\ConfigImporterEvent $event
   */
  public function onRespond(ConfigImporterEvent $event) {
    $changes = $event->getChangelist('update');
    // If the search index settings are updated, mark content for reindexing.
    if (in_array('search_api.index.training', $changes)) {
      try {
        $index = $this->entityTypeManager->getStorage('search_api_index')
          ->load('training')->reindex();
      }
      catch (\Exception $exception) {}
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events[ConfigEvents::IMPORT] = ['onRespond'];
    return $events;
  }

}
