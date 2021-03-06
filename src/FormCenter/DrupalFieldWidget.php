<?php

namespace Drupal\form_builder\FormCenter;

use GO1\FormCenter\Field\FieldInterface;
use GO1\FormCenter\Field\FieldOptions;
use GO1\FormCenter\Field\FieldValueItem;
use GO1\FormCenter\Field\FieldValueItemInterface;
use GO1\FormCenter\Field\Widget\FieldWidgetBase;

class DrupalFieldWidget extends FieldWidgetBase {

  private $drupalFieldInfo;

  public function __construct(array $drupalFieldInfo) {
    $this->setName('drupal.abstract_widget');
    $this->setHumanName('[Drupal] Abstract widget');
    $this->drupalFieldInfo = $drupalFieldInfo;
  }

  /**
   * @param FieldInterface $field
   * @param FieldOptions $fieldOptions
   * @param FieldValueItemInterface[] $fieldValueItems
   * @return string
   */
  protected function renderFieldTypes(FieldInterface $field, FieldOptions $fieldOptions, array $fieldValueItems = []) {
    if (!empty($this->drupalFieldInfo['field'])) {
      return $this->renderDrupalField($field, $fieldOptions, $fieldValueItems);
    }
    return $this->renderDrupalProperty($field, $fieldOptions, $fieldValueItems);
  }

  /**
   * @param FieldInterface $field
   * @param FieldOptions $fieldOptions
   * @param FieldValueItemInterface[] $fieldValueItems
   * @return string
   */
  public function renderDrupalField(FieldInterface $field, FieldOptions $fieldOptions, array $fieldValueItems = []) {
    $eTName = $field->getEntityType()->getName();
    $dETName = $field->getEntityType()->getDrupalEntityTypeName();
    $dBundleName = $field->getEntityType()->getDrupalBundleName();
    $dFName = $field->getName();
    $dField = field_info_field($dFName);
    $dFieldInstance = field_info_instance($dETName, $dFName, $dBundleName);
    $dLang = 'und';
    $dItems = array_map(function(FieldValueItem $item) {
      return $item->toArray();
        }, $fieldValueItems);

    $dForm = ['#parents' => [$dFName, 'und']];
    $dFormState = [];

    $form[$dFName] = [
        '#type'       => 'container',
        '#parents'    => [$dFName, 'und'],
        '#weight'     => $fieldOptions->getWeight(),
        '#attributes' => [
            'class' => [
                'field-type-' . str_replace('_', '-', $dField['type']),
                'field-name-' . str_replace('_', '-', $dFName),
                'field-widget-' . str_replace('_', '-', $dFieldInstance['widget']['type'])
            ]
        ],
        $dLang        => [],
    ];

    $e = &$form[$dFName][$dLang];
    if ($dField['cardinality'] == -1) {
      $items_count = max([2, count($fieldValueItems) + 1]);
      $dFormState['field']['#parents'][$dFName]['und']['#fields'][$dFName]['und']['items_count'] = $items_count;
    }
    $e = field_multiple_value_form($dField, $dFieldInstance, $dLang, $dItems, $dForm, $dFormState);

    $form['#parents'] = [];
    $form_state = ['values' => [], 'complete form' => $form];

    if (!empty($e['add_more']['#ajax'])) {
      unset($e['add_more']['#ajax'], $e['add_more']['#submit']);
      $e['add_more']['#attributes']['class'][] = 'fob-field-more';
    }

    form_builder('form_builder_element', $form, $form_state);

    foreach (element_children($form[$dFName][$dLang]) as $delta) {
      $e = &$form[$dFName][$dLang][$delta];
      foreach (element_children($e) as $i) {
        if (empty($e[$i]['#type'])) {
          continue;
        }

        if (isset($e[$i]['#value'])) {
          $name = $eTName . '[' . $dFName . '][' . $dLang . '][' . $delta . '][' . $i . ']';
          $e[$i]['#name'] = $name;
        }

        $iis = element_children($e[$i]);
        foreach ($iis as $ii) {
          $name = $eTName . '[' . $dFName . '][' . $dLang . ']' . implode('', array_map(function($pa) {
                return '[' . $pa . ']';
                            }, $e[$i][$ii]['#parents']));
          $e[$i][$ii]['#name'] = $name;
        }
      }
    }

    return drupal_render($form);
  }

  /**
   * @param FieldInterface $field
   * @param FieldOptions $fieldOptions
   * @param FieldValueItemInterface[] $fieldValueItems
   */
  protected function renderDrupalProperty(FieldInterface $field, FieldOptions $fieldOptions, array $fieldValueItems = []) {
    $e = [
        '#type'          => 'textfield',
        '#parents'       => [$field->getEntityType()->getName(), $field->getName()],
        '#required'      => !empty($this->drupalFieldInfo['required']),
        '#title'         => $this->drupalFieldInfo['label'],
        '#default_value' => isset($fieldValueItems[0]) ? $fieldValueItems[0]['value'] : '',
    ];

    if (isset($this->drupalFieldInfo['type'])) {
      switch ($this->drupalFieldInfo['type']) {
        case 'boolean':
          $e['#type'] = 'checkbox';
          break;
        case 'text':
          $e['#type'] = 'textarea';
          $e['#rows'] = 3;
          break;
        case 'integer':
          $e['#type'] = 'textfield';
          break;
        case 'date':
          if ('date' === $this->drupalFieldInfo['type']) {
            if (isset($fieldValueItems[0]['value'])) {
              list($year, $month, $day) = explode('-', date('Y-m-d', $fieldValueItems[0]['value']));
              $fieldValueItems[0]['year'] = $year;
              $fieldValueItems[0]['month'] = $month;
              $fieldValueItems[0]['day'] = (int) $day;
              unset($fieldValueItems[0]['value']);
              $e['#default_value'] = $fieldValueItems[0]->toArray();
            }
          }
          $e['#type'] = $this->drupalFieldInfo['type'];
          break;
        default:
          $entityTypeName = $this->drupalFieldInfo['type'];
          $fieldName = $entityTypeName === 'user' ? 'name' : entity_get_info($entityTypeName)['entity keys']['label'];
          $e['#autocomplete_path'] = strtr('form/!type/!field/autocomplete', [
              '!type'  => $entityTypeName,
              '!field' => $fieldName,
          ]);
          break;
      }
    }

    if (isset($this->drupalFieldInfo['options list'])) {
      $e['#type'] = 'select';
      $e['#options'] = $this->drupalFieldInfo['options list']();
    }

    $form_state = ['values' => []];
    form_builder('form_builder_element', $e, $form_state);

    return drupal_render($e);
  }

}
