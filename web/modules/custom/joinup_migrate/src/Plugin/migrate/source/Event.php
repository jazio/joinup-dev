<?php

namespace Drupal\joinup_migrate\Plugin\migrate\source;

use Drupal\joinup_migrate\HtmlUtility;
use Drupal\migrate\Row;

/**
 * Migrates events.
 *
 * @MigrateSource(
 *   id = "event"
 * )
 */
class Event extends NodeBase {

  use KeywordsTrait;

  /**
   * {@inheritdoc}
   */
  public function fields() {
    return [
      'start_date' => $this->t('Start date'),
      'end_date' => $this->t('End date'),
      'location' => $this->t('Location'),
      'organisation' => $this->t('Organisation'),
      'website' => $this->t('Website'),
      'mail' => $this->t('Contact mail'),
      'agenda' => $this->t('Agenda'),
    ] + parent::fields();
  }

  /**
   * {@inheritdoc}
   */
  public function query() {
    return $this->select('d8_event', 'n')->fields('n', [
      'start_date',
      'end_date',
      'city',
      'venue',
      'address',
      'coord',
      'organisation',
      'website',
      'mail',
      'agenda',
    ]);
  }

  /**
   * {@inheritdoc}
   */
  public function prepareRow(Row $row) {
    $nid = $row->getSourceProperty('nid');
    $vid = $row->getSourceProperty('vid');

    // Build a geolocable location.
    $location = [];
    foreach (['city', 'venue', 'address', 'coord'] as $property) {
      $html = $row->getSourceProperty($property);
      if ($html = trim($html)) {
        $plain = trim(HtmlUtility::htmlToPlainText($html));
        // Clear inner empty lines.
        $plain = implode("\n", array_filter(array_map('trim', explode("\n", $plain))));
        if ($plain) {
          // Special treatment for 'coord' due to its inconsistent value. We
          // store it as @lat,long format make easily to be parsed later.
          if ($property === 'coord') {
            $plain = static::normaliseCoordinates($plain);
          }
          $location[] = $plain;
        }
      }
    }
    if ($location) {
      $row->setSourceProperty('location', implode("\n", $location));
    }

    // Keywords.
    $this->setKeywords($row, 'keywords', $nid, $vid);

    return parent::prepareRow($row);
  }

  /**
   * Tries to normalise the coordinates.
   *
   * @param string $plain
   *   Plain text that may contain coordinates.
   *
   * @return string
   *   The coordinates as '@lat,long' or the initial value as fallback.
   */
  public static function normaliseCoordinates($plain) {
    if (preg_match('|N?(\-?\d+(\.\d+)),\s*E?(\-?\d+(\.\d+))|', $plain, $found)) {
      return '@' . $found[1] . ',' . $found[3];
    }
    // Fallback to the input value.
    return $plain;
  }

}