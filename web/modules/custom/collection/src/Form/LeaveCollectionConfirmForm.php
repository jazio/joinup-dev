<?php

/**
 * @file
 * Contains \Drupal\collection\Form\LeaveCollectionConfirmForm.
 */

namespace Drupal\collection\Form;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\EventSubscriber\MainContentViewSubscriber;
use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\collection\CollectionInterface;
use Drupal\og\Og;
use Drupal\user\Entity\User;

/**
 * Confirmation form for users that want to revoke their collection membership.
 *
 * @package Drupal\collection\Form
 */
class LeaveCollectionConfirmForm extends ConfirmFormBase {

  /**
   * The collection that is about to be abandoned by the user.
   *
   * @var \Drupal\collection\CollectionInterface
   */
  protected $collection;

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'leave_collection_confirm_form';
  }

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    return $this->t('Leave collection');
  }

  /**
   * {@inheritdoc}
   */
  public function getDescription() {
    return $this->t('Are you sure you want to leave the %collection collection?', [
      '%collection' => $this->collection->getName(),
    ]);
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelUrl() {
    return Url::fromRoute('entity.collection.canonical', [
      'collection' => $this->collection->id(),
    ]);
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, CollectionInterface $collection = NULL) {
    // Store the collection on the object so it can be reused.
    $this->collection = $collection;

    $form = parent::buildForm($form, $form_state);

    // Hide the Cancel link when the form is displayed in a modal. The close
    // button should be used instead.
    $form['actions']['cancel']['#access'] = !$this->isModal();

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);

    // Only authenticated users can leave a collection.
    /** @var \Drupal\user\UserInterface $user */
    $user = User::load($this->currentUser()->id());
    if ($user->isAnonymous()) {
      $form_state->setErrorByName('user', $this->t('<a href=":login">Log in</a> or <a href=":register">register</a> to change your group membership.', [
        ':login' => Url::fromRoute('user.login'),
        ':register' => Url::fromRoute('user.register'),
      ]));
    }

    // Check if the user is a member of the collection.
    if (!Og::isMember($this->collection, $user)) {
      $form_state->setErrorByName('collection', $this->t('You are not a member of this collection. You cannot leave it.'));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $user = User::load($this->currentUser()->id());
    $membership_ids = \Drupal::entityQuery('og_membership')
      ->condition('member_entity_id', $user->id())
      ->condition('member_entity_type', 'user')
      ->condition('group_entity_id', $this->collection->id())
      ->condition('group_entity_type', 'collection')
      ->execute();
    $memberships = Og::membershipStorage()->loadMultiple($membership_ids);
    Og::membershipStorage()->delete($memberships);

    drupal_set_message($this->t('You are no longer a member of %collection.', [
      '%collection' => $this->collection->getName(),
    ]));

    $form_state->setRedirectUrl($this->getCancelUrl());
  }

  /**
   * Access check for the LeaveCollectionConfirmForm.
   *
   * @param \Drupal\collection\CollectionInterface $collection
   *   The collection that is on the verge of losing a member.
   *
   * @return \Drupal\Core\Access\AccessResult
   *   The access result object.
   */
  public static function access(CollectionInterface $collection) {
    /** @var \Drupal\Core\Session\AccountProxyInterface $account_proxy */
    $account_proxy = \Drupal::service('current_user');

    // Deny access if the user is not logged in.
    if ($account_proxy->isAnonymous()) {
      return AccessResult::forbidden();
    }

    // Only allow access if the current user is a member of the collection.
    $user = User::load($account_proxy->id());
    return AccessResult::allowedIf(Og::isMember($collection, $user));
  }

  /**
   * Returns whether the form is displayed in a modal.
   *
   * @return bool
   *   TRUE if the form is displayed in a modal.
   *
   * @todo Remove when issue #2661046 is in.
   *
   * @see https://www.drupal.org/node/2661046
   */
  protected function isModal() {
    return $this->getRequest()->query->get(MainContentViewSubscriber::WRAPPER_FORMAT) === 'drupal_modal';
  }

}
