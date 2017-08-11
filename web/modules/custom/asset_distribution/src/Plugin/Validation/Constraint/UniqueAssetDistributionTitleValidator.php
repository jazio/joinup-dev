<?php

namespace Drupal\asset_distribution\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Validates that a distribution's title is unique within the release.
 *
 * This is the validator for the UniqueAssetReleaseTitleConstraint.
 *
 * @see \Drupal\asset_distribution\Plugin\Validation\Constraint\UniqueAssetReleaseTitleConstraint
 */
class UniqueAssetDistributionTitleValidator extends ConstraintValidator {

  /**
   * {@inheritdoc}
   */
  public function validate($items, Constraint $constraint) {
    if (!$item = $items->first()) {
      return;
    }

    /** @var \Drupal\rdf_entity\RdfInterface $entity */
    $entity = $items->getEntity();
    // Distinguish between solutions and releases.
    $bundle = empty($entity->get('og_audience')->first()->getValue()['target_id']) ? t('release') : $entity->get('og_audience')->first()->entity->bundle();
    if (!asset_distribution_title_is_unique($entity)) {
      $this->context->addViolation($constraint->message, [
        '%title' => $entity->label(),
        '%bundle' => $bundle,
      ]);
    }
  }

}
