<?php

namespace Drupal\media_migrate\Plugin\migrate\process;

use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\media_entity\MediaStorageInterface;
use Drupal\migrate\MigrateException;
use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\Row;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class MediaEntity.
 *
 * @MigrateProcessPlugin(
 *   id = "media_entity"
 * )
 */
class MediaEntity extends ProcessPluginBase implements ContainerFactoryPluginInterface {
  /**
   * The file system service.
   *
   * @var \Drupal\Core\File\FileSystemInterface
   */
  protected $fileSystem;

  /**
   * The media storage.
   *
   * @var \Drupal\media_entity\MediaStorageInterface
   */
  protected $mediaStorage;

  /**
   * MediaImage constructor.
   */
  public function __construct(array $configuration, $plugin_id, array $plugin_definition, FileSystemInterface $file_system, MediaStorageInterface $media_storage) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->fileSystem = $file_system;
    $this->mediaStorage = $media_storage;
  }

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    list($file_uri, $file_name) = $value;
    $bundle = NULL;
    if (empty($file_uri)) {
      return NULL;
    }
    // Get bundle.
    if (isset($this->configuration['bundle']) && $this->configuration['bundle']) {
      $bundle = $this->configuration['bundle'];
    }
    else {
      throw new MigrateException('Bundle is not set or empty.');
    }

    // Set destination directory.
    $destination = isset($this->configuration['destination']) ? $this->configuration['destination'] : 'public://migrate';
    // Get image source url & filename.
    $file = NULL;
    // Get file content by URL.
    $data = file_get_contents($file_uri);

    if (file_prepare_directory($destination, FILE_CREATE_DIRECTORY | FILE_MODIFY_PERMISSIONS) && $data) {
      $file = file_save_data($data, $destination . '/' . $this->fileSystem->basename($file_uri), FILE_EXISTS_REPLACE);
    }
    if ($file) {
      $fid = $file->id();
      // Save Media entity.
      if ($fid) {
        $media = $this->mediaStorage->create([
          'bundle' => $bundle,
          'uid' => 1,
          'langcode' => \Drupal::languageManager()
            ->getDefaultLanguage()
            ->getId(),
          'status' => 1,
          $destination_property => [
            'target_id' => $fid,
            'alt' => $file_name,
            'title' => $file_name,
          ],
        ]);
        $media->save();
        return $media->id();
      }
    }

    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    $entity_manager = $container->get('entity.manager');

    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('file_system'),
      $entity_manager->getStorage('media')
    );
  }

}
