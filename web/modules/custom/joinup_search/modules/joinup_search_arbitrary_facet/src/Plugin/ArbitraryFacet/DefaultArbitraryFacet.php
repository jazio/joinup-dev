<?php

namespace Drupal\joinup_search_arbitrary_facet\Plugin\ArbitraryFacet;

use Drupal\joinup_search_arbitrary_facet\Plugin\ArbitraryFacetBase;

/**
 * Provides supports for facets generated by arbitrary queries.
 *
 * @ArbitraryFacet(
 *   id = "default",
 *   label = @Translation("Default arbitrary query"),
 * )
 */
class DefaultArbitraryFacet extends ArbitraryFacetBase {

  /**
   * {@inheritdoc}
   */
  public function getFacetDefinition() {
    $current_user = \Drupal::currentUser();
    return [
      'mine' => [
        'field_name' => 'entity_author',
        'field_condition' => $current_user->id(),
        'label' => $this->t("My content"),
      ],
      'featured' => [
        'field_name' => 'site_featured',
        'field_condition' => 'true',
        'label' => $this->t("Featured content"),
      ],
      'collections' => [
        'field_name' => 'entity_bundle',
        'field_condition' => 'collection',
        'label' => $this->t("Collections"),
      ],
    ];
  }

}