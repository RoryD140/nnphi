<?php

namespace Drupal\nnphi_training\Plugin\Block;

use Drupal\Core\Annotation\Translation;
use Drupal\Core\Block\Annotation\Block;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormBuilderInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Link;

/**
 * Class SearchBlock
 *
 * @Block(
 *   id = "nnphi_training_search_block",
 *   admin_label = @Translation("Training Search"),
 *   category = @Translation("Search")
 * )
 */
class SearchBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * @var \Drupal\Core\Form\FormBuilderInterface
   */
  protected $formBuilder;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, FormBuilderInterface $formBuilder) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->formBuilder = $formBuilder;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('form_builder')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $output = [];
    $output['search_form'] = $this->formBuilder->getForm('Drupal\nnphi_training\Form\SearchHeader');
    $output['search_link'] = Link::createFromRoute(t('Browse'), 'nnphi_training.instant_search', [], ['attributes' => ['class' => 'browse-link']])->toRenderable();
    return $output;
  }
}
