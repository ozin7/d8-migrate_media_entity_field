<?php
/**
 * Created by PhpStorm.
 * User: ozin
 * Date: 5/30/17
 * Time: 9:34 PM
 */

namespace Drupal\media_migrate\Plugin\migrate\process;

use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\Row;

/**
 * Map CNRS to multi-valued field.
 *
 * @MigrateProcessPlugin(
 *   id = "empty_title"
 * )
 */
class EmptyTitle extends ProcessPluginBase {

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    foreach ($value as $title_value) {
      if (!empty($title_value)) {
        return $title_value;
      }
    }

    return 'Default Article Title';
  }
}