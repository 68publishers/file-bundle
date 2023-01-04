<?php

declare(strict_types=1);

namespace SixtyEightPublishers\FileBundle\DI;

use Nette\Schema\Expect;
use Nette\Schema\Schema;
use Nette\Schema\Helpers;
use Nette\DI\CompilerExtension;
use SixtyEightPublishers\FileBundle\Entity\FileInterface;
use SixtyEightPublishers\FileBundle\Storage\DataStorageFactory;
use SixtyEightPublishers\FileBundle\Entity\Basic\File as BasicFile;
use SixtyEightPublishers\FileBundle\Exception\InvalidStateException;
use SixtyEightPublishers\DoctrineBridge\Bridge\Nette\DI\TargetEntity;
use SixtyEightPublishers\FileBundle\Control\FileManager\Configuration;
use SixtyEightPublishers\FileBundle\Entity\SoftDeletableFileInterface;
use SixtyEightPublishers\DoctrineBridge\Bridge\Nette\DI\EntityMapping;
use SixtyEightPublishers\FileBundle\Storage\DataStorageFactoryInterface;
use SixtyEightPublishers\FileStorage\Bridge\Nette\DI\FileStorageExtension;
use SixtyEightPublishers\FileBundle\EntityFactory\DefaultFileEntityFactory;
use SixtyEightPublishers\NotificationBundle\DI\NotificationBundleExtension;
use SixtyEightPublishers\ImageStorage\Bridge\Nette\DI\ImageStorageExtension;
use SixtyEightPublishers\FileBundle\EntityFactory\FileEntityFactoryInterface;
use SixtyEightPublishers\FileBundle\Storage\Manipulator\ManipulatorInterface;
use SixtyEightPublishers\FileBundle\EventSubscriber\NotificationEventSubscriber;
use SixtyEightPublishers\FileBundle\ResourceMetadata\BaseResourceMetadataFactory;
use SixtyEightPublishers\FileBundle\ResourceValidator\ResourceValidatorInterface;
use SixtyEightPublishers\FileBundle\Entity\SoftDeletable\File as SoftDeletableFile;
use SixtyEightPublishers\FileBundle\EventSubscriber\DeleteFileSourceEventSubscriber;
use SixtyEightPublishers\FileBundle\Control\DropZone\DropZoneControlFactoryInterface;
use SixtyEightPublishers\FileBundle\ResourceMetadata\ResourceMetadataFactoryRegistry;
use SixtyEightPublishers\FileBundle\ResourceMetadata\ResourceMetadataFactoryInterface;
use SixtyEightPublishers\DoctrineBridge\Bridge\Nette\DI\TargetEntityProviderInterface;
use SixtyEightPublishers\DoctrineBridge\Bridge\Nette\DI\EntityMappingProviderInterface;
use SixtyEightPublishers\TranslationBridge\Bridge\Nette\DI\TranslationProviderInterface;
use SixtyEightPublishers\FileBundle\Control\FileManager\FileManagerControlFactoryInterface;
use SixtyEightPublishers\EventDispatcherExtra\Bridge\Nette\DI\EventDispatcherExtraExtension;
use SixtyEightPublishers\FileBundle\Control\FileManager\ConfiguredFileManagerControlFactory;
use SixtyEightPublishers\FileBundle\Bridge\ImageStorage\ResourceMetadata\ImageResourceMetadataFactory;

final class FileBundleExtension extends CompilerExtension implements EntityMappingProviderInterface, TargetEntityProviderInterface, TranslationProviderInterface
{
	public const TAG_RESOURCE_METADATA_FACTORY = '68publishers.file_bundle.resource_metadata_factory';

	/**
	 * {@inheritDoc}
	 */
	public function getConfigSchema(): Schema
	{
		return Expect::structure([
			'entity' => Expect::string()->assert(static function (string $className) {
				return is_subclass_of($className, FileInterface::class, TRUE);
			}),
			'file_managers' => Expect::arrayOf(Configuration::getSchema(TRUE)),
		]);
	}

	/**
	 * {@inheritdoc}
	 *
	 * @throws \SixtyEightPublishers\FileBundle\Exception\InvalidStateException
	 */
	public function loadConfiguration(): void
	{
		foreach ([FileStorageExtension::class, EventDispatcherExtraExtension::class] as $extensionName) {
			if (0 >= count($this->compiler->getExtensions($extensionName))) {
				throw new InvalidStateException(sprintf(
					'The extension %s can be used only with %s.',
					static::class,
					$extensionName
				));
			}
		}

		$builder = $this->getContainerBuilder();

		$builder->addDefinition($this->prefix('file_entity_factory'))
			->setType(FileEntityFactoryInterface::class)
			->setFactory(DefaultFileEntityFactory::class, [$this->config->entity]);

		$builder->addDefinition($this->prefix('data_storage_factory'))
			->setType(DataStorageFactoryInterface::class)
			->setFactory(DataStorageFactory::class);

		# Resource metadata factory
		$builder->addDefinition($this->prefix('resource_metadata_factory.registry'))
			->setType(ResourceMetadataFactoryInterface::class)
			->setFactory(ResourceMetadataFactoryRegistry::class);

		$builder->addDefinition($this->prefix('resource_metadata_factory.base'))
			->setAutowired(FALSE)
			->setType(ResourceMetadataFactoryInterface::class)
			->setFactory(BaseResourceMetadataFactory::class)
			->addTag(self::TAG_RESOURCE_METADATA_FACTORY);

		if (class_exists(ImageStorageExtension::class) && 0 < count($this->compiler->getExtensions(ImageStorageExtension::class))) {
			$builder->addDefinition($this->prefix('resource_metadata_factory.image'))
				->setAutowired(FALSE)
				->setType(ResourceMetadataFactoryInterface::class)
				->setFactory(ImageResourceMetadataFactory::class)
				->addTag(self::TAG_RESOURCE_METADATA_FACTORY);
		}

		# Event subscribers
		$builder->addDefinition($this->prefix('event_subscriber.delete_file_source'))
			->setType(DeleteFileSourceEventSubscriber::class);

		if (class_exists(NotificationBundleExtension::class) && 0 < count($this->compiler->getExtensions(NotificationBundleExtension::class))) {
			$builder->addDefinition($this->prefix('event_subscriber.notification'))
				->setType(NotificationEventSubscriber::class);
		}

		# Control factories
		$builder->addFactoryDefinition($this->prefix('control.dropzone_control_factory'))
			->setImplement(DropZoneControlFactoryInterface::class);

		$builder->addFactoryDefinition($this->prefix('control.file_manager_control_factory'))
			->setImplement(FileManagerControlFactoryInterface::class);

		$configurableFileManagerControlFactory = $builder->addDefinition($this->prefix('control.configurable_file_manager_control_factory'))
			->setType(ConfiguredFileManagerControlFactory::class);

		foreach ($this->config->file_managers as $name => $fileManagerConfig) {
			if (isset($fileManagerConfig['extends'])) {
				if (!isset($config->image_managers[$fileManagerConfig['extends']])) {
					throw new InvalidStateException(sprintf(
						'Configuration for file manager with name "%s" is missing.',
						$fileManagerConfig['extends']
					));
				}

				$fileManagerConfig = Helpers::merge($fileManagerConfig, $this->config->image_managers[$fileManagerConfig['extends']]);
			}

			unset($fileManagerConfig['extends']);

			foreach ($fileManagerConfig['manipulators'] as $i => $manipulator) {
				$fileManagerConfig['manipulators'][$i] = $builder->addDefinition($this->prefix('manipulator.' . $name . '.' . $i))
					->setAutowired(FALSE)
					->setType(ManipulatorInterface::class)
					->setFactory($manipulator);
			}

			foreach ($fileManagerConfig['resource_validators'] as $i => $validator) {
				$fileManagerConfig['resource_validators'][$i] = $builder->addDefinition($this->prefix('resource_validator.' . $name . '.' . $i))
					->setAutowired(FALSE)
					->setType(ResourceValidatorInterface::class)
					->setFactory($validator);
			}

			$configurableFileManagerControlFactory->addSetup('addConfiguration', [(string) $name, $fileManagerConfig]);
		}
	}

	/**
	 * {@inheritDoc}
	 */
	public function beforeCompile(): void
	{
		$builder = $this->getContainerBuilder();

		/** @var \Nette\DI\Definitions\ServiceDefinition $resourceMetadataFactoryRegistry */
		$resourceMetadataFactoryRegistry = $builder->getDefinition($this->prefix('resource_metadata_factory.registry'));

		$resourceMetadataFactoryRegistry->setArguments([
			array_map(static function (string $name) {
				return '@' . $name;
			}, array_keys($builder->findByTag(self::TAG_RESOURCE_METADATA_FACTORY))),
		]);
	}

	/**
	 * {@inheritdoc}
	 */
	public function getEntityMappings(): array
	{
		switch ($this->config->entity) {
			case BasicFile::class:
				return [
					new EntityMapping(EntityMapping::DRIVER_ANNOTATION, 'SixtyEightPublishers\FileBundle\Entity', __DIR__ . '/../Entity/Basic'),
				];
			case SoftDeletableFile::class:
				return [
					new EntityMapping(EntityMapping::DRIVER_ANNOTATION, 'SixtyEightPublishers\FileBundle\Entity', __DIR__ . '/../Entity/SoftDeletable'),
				];
		}

		return [];
	}

	/**
	 * {@inheritdoc}
	 */
	public function getTargetEntities(): array
	{
		$entityClassName = $this->config->entity;
		$targetEntities = [
			new TargetEntity(FileInterface::class, $this->config->entity),
		];

		if (is_subclass_of($entityClassName, SoftDeletableFileInterface::class, TRUE)) {
			$targetEntities[SoftDeletableFileInterface::class] = $entityClassName;
		}

		return $targetEntities;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getTranslationResources(): array
	{
		return [
			__DIR__ . '/../translations',
		];
	}
}
