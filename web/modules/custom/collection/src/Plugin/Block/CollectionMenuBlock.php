<?php

namespace Drupal\collection\Plugin\Block;

use Drupal\Core\Url;
use Drupal\og_menu\OgMenuInstanceInterface;
use Drupal\og_menu\Plugin\Block\OgMenuBlock;

/**
 * Provides the block that displays the menu containing collection pages.
 *
 * @Block(
 *   id = "collection_menu_block",
 *   admin_label = @Translation("Collection menu"),
 *   category = @Translation("Collection"),
 *   deriver = "Drupal\og_menu\Plugin\Derivative\OgMenuBlock",
 *   context = {
 *     "og" = @ContextDefinition("entity:rdf_entity:collection", label = @Translation("Collection"))
 *   }
 * )
 */
class CollectionMenuBlock extends OgMenuBlock {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $menu_name = $this->getMenuName();
    $parameters = $this->menuTree->getCurrentRouteMenuTreeParameters($menu_name);

    // Adjust the menu tree parameters based on the block's configuration.
    $level = $this->configuration['level'];
    $depth = $this->configuration['depth'];
    $parameters->setMinDepth($level);
    // When the depth is configured to zero, there is no depth limit. When depth
    // is non-zero, it indicates the number of levels that must be displayed.
    // Hence this is a relative depth that we must convert to an actual
    // (absolute) depth, that may never exceed the maximum depth.
    if ($depth > 0) {
      $parameters->setMaxDepth(min($level + $depth - 1, $this->menuTree->maxDepth()));
    }

    $tree = $this->menuTree->load($menu_name, $parameters);
    $manipulators = array(
      array('callable' => 'menu.default_tree_manipulators:checkAccess'),
      array('callable' => 'menu.default_tree_manipulators:generateIndexAndSort'),
    );
    $tree = $this->menuTree->transform($tree, $manipulators);
    $build = $this->menuTree->build($tree);

    // The create custom page route will serve as test to see if the user has
    // enough privileges to configure the menu.
    $create_url = Url::fromRoute('custom_page.collection_custom_page.add', [
      'rdf_entity' => $this->getContext('og')->getContextData()->getValue()->id(),
    ]);

    // When the tree is empty, no pages have been added yet to it. Show an help
    // text to point the user to take some action.
    if (empty($tree)) {
      $build['create']['info'] = [
        '#type' => 'html_tag',
        '#tag' => 'p',
        '#value' => $this->t("There are no pages yet. Why don't you start by creating an <em>About</em> page?"),
        '#access' => $create_url->access(),
      ];
      $build['create']['link'] = [
        '#type' => 'link',
        '#title' => $this->t('Add a new page'),
        '#url' => $create_url,
        '#access' => $create_url->access(),
      ];
    }
    elseif (empty($build['#items'])) {
      // If there are entries in the tree but none of those is in the build
      // array, it means that all the available pages have been disabled inside
      // the menu configuration.
      $build['disabled'] = [
        '#type' => 'html_tag',
        '#tag' => 'p',
        '#value' => $this->t('All the pages have been disabled for this collection. You can edit the menu configuration or add a new page.'),
        '#access' => $create_url->access(),
      ];
    }

    $menu_instance = $this->getOgMenuInstance();
    if ($menu_instance instanceof OgMenuInstanceInterface) {
      // Show the "Edit menu" link only when at least one element is available.
      if ($tree) {
        $build['#contextual_links']['ogmenu'] = [
          'route_parameters' => [
            'ogmenu_instance' => $menu_instance->id(),
          ],
        ];
      }
      $build['#contextual_links']['collection_menu_block'] = [
        'route_parameters' => [
          'rdf_entity' => $this->getContext('og')->getContextData()->getValue()->id(),
        ],
      ];
    }

    // Improve the template suggestion.
    if (!empty($build['#items']) && $menu_instance) {
      $menu_name = $menu_instance->getType();
      $build['#theme'] = 'menu__og__' . strtr($menu_name, '-', '_');
    }
    return $build;
  }

}
