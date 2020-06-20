<?php

namespace Drupal\bootstrap_layout_builder;

use Drupal\Core\Config\Entity\ConfigEntityListBuilder;
use Drupal\Core\Entity\EntityInterface;

/**
 * Defines a class to build bootstrap_layout_builder layout entities.
 *
 * @see \Drupal\bootstrap_layout_builder\Entity\LayoutListBuilder
 */
class LayoutListBuilder extends ConfigEntityListBuilder {

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['label'] = $this->t('Label');
    $header['id'] = $this->t('Machine name');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function getDefaultOperations(EntityInterface $entity) {
    $operations = parent::getDefaultOperations($entity);

    $operations['list'] = [
      'title' => $this->t('List options'),
      'url' => $entity->toUrl('options-form'),
    ];
    return $operations;
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    $row['label'] = $entity->label();
    $row['id'] = $entity->id();
    return $row + parent::buildRow($entity);
  }

}
