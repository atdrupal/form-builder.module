<?php

namespace Drupal\form_builder\Controller;

use DatabaseTransaction;
use Drupal\form_builder\FormEntity;
use Drupal\form_builder\Helper\FormEntityFixer;
use Drupal\form_builder\Helper\FormEntityReversedFixer;
use EntityAPIController;
use GO1\FormCenter\Form\FormInterface;

class FormEntityController extends EntityAPIController {

  public function load($ids = [], $conditions = []) {
    $entities = parent::load($ids, $conditions);
    $entityFixer = new FormEntityFixer();
    foreach ($entities as &$form) {
      /* @var $form FormEntity */
      $entityFixer->fix($form);
    }
    return $entities;
  }

  /**
   * {@inheritdoc}
   */
  public function query($ids, $conditions, $revision_id = FALSE) {
    // I hate but I do not know better way to fix this:
    // At /admin/structure/fob-form/manage/%id, Drupal load entity before
    // executing hook_init, where composer_manager module registers composer's
    // autoloader.
    $fns = spl_autoload_functions();
    if (is_string($fns[0])) {
      composer_manager_init();
    }

    return parent::query($ids, $conditions, $revision_id);
  }

  /**
   * @param FormInterface $form
   * @param DatabaseTransaction $transaction
   */
  public function save($form, DatabaseTransaction $transaction = NULL) {
    (new FormEntityReversedFixer())->fix($form);
    return parent::save($form, $transaction);
  }

}
