<?php

declare(strict_types=1);

namespace SixtyEightPublishers\FileBundle\Control\FileManager;

use Nette\Utils\Strings;
use SixtyEightPublishers\FileBundle\Storage\DataStorageInterface;
use SixtyEightPublishers\FileBundle\Event\DropZoneControlSetupEvent;
use SixtyEightPublishers\FileBundle\Exception\InvalidArgumentException;
use SixtyEightPublishers\FileBundle\Storage\DataStorageFactoryInterface;

final class ConfiguredFileManagerControlFactory
{
	/** @var \SixtyEightPublishers\FileBundle\Control\FileManager\FileManagerControlFactoryInterface  */
	private $fileManagerControlFactory;

	/** @var \SixtyEightPublishers\FileBundle\Storage\DataStorageFactoryInterface  */
	private $dataStorageFactory;

	/** @var array[]  */
	private $configurations = [];

	/**
	 * @param \SixtyEightPublishers\FileBundle\Control\FileManager\FileManagerControlFactoryInterface $fileManagerControlFactory
	 * @param \SixtyEightPublishers\FileBundle\Storage\DataStorageFactoryInterface                    $dataStorageFactory
	 */
	public function __construct(FileManagerControlFactoryInterface $fileManagerControlFactory, DataStorageFactoryInterface $dataStorageFactory)
	{
		$this->fileManagerControlFactory = $fileManagerControlFactory;
		$this->dataStorageFactory = $dataStorageFactory;
	}

	/**
	 * @param string $name
	 * @param array  $configuration
	 *
	 * @return void
	 */
	public function addConfiguration(string $name, array $configuration): void
	{
		$this->configurations[$name] = $configuration;
	}

	/**
	 * @param \SixtyEightPublishers\FileBundle\Control\FileManager\ConfiguredFileManagerArgs $args
	 *
	 * @return \SixtyEightPublishers\FileBundle\Control\FileManager\FileManagerControl
	 */
	public function create(ConfiguredFileManagerArgs $args): FileManagerControl
	{
		$config = $this->getConfig($args->getName());
		$control = $this->fileManagerControlFactory->create($this->createDataStorage($args));

		foreach ($config->get('actions') as $action) {
			$control->addAction($action);
		}

		if (NULL !== $config->get('template')) {
			if (Strings::startsWith($config->get('template'), '@')) {
				$control->setRelativeFile($config->get('template'));
			} else {
				$control->setFile($config->get('template'));
			}
		}

		if (NULL !== $config->get('max_allowed_files')) {
			$control->setMaxAllowedFiles($config->get('max_allowed_files'));
		}

		$control->setDeleteExistingFileIfMaxAllowedReached(
			(bool) $config->get('max_allowed_files_reached.delete'),
			$config->get('max_allowed_files_reached.direction')
		);

		if (NULL !== $config->get('max_file_size')) {
			$control->setMaxFileSize($config->get('max_file_size'));
		}

		foreach ($config->get('resource_validators') as $resourceValidator) {
			$control->addResourceValidator($resourceValidator);
		}

		$dispatcher = $control->getEventDispatcher();

		$dispatcher->addListener(DropZoneControlSetupEvent::NAME, function (DropZoneControlSetupEvent $event) use ($config) {
			$control = $event->getDropZoneControl();

			if (NULL !== $config->get('dropzone.id')) {
				$control->setDropZoneId($config->get('dropzone.id'));
			}

			if (NULL !== $config->get('dropzone.template')) {
				if (Strings::startsWith($config->get('dropzone.template'), '@')) {
					$control->setRelativeFile($config->get('dropzone.template'));
				} else {
					$control->setFile($config->get('dropzone.template'));
				}
			}

			foreach ($config->get('dropzone.content_html') as $contentHtml) {
				$control->addContentHtml($contentHtml);
			}

			foreach ($config->get('dropzone.settings') as $key => $value) {
				$control->addSetting((string) $key, $value);
			}

			foreach ($config->get('dropzone.extensions') as $key => $value) {
				if (!is_array($value)) {
					$key = $value;
					$value = [];
				}

				$control->addExtension((string) $key, $value);
			}
		});

		return $control;
	}

	/**
	 * @param string $name
	 * @param mixed  ...$dataStorageArgs
	 *
	 * @return \SixtyEightPublishers\FileBundle\Control\FileManager\ConfiguredFileManagerArgs
	 */
	public function createArgs(string $name, ...$dataStorageArgs): ConfiguredFileManagerArgs
	{
		return new ConfiguredFileManagerArgs($name, ...$dataStorageArgs);
	}

	/**
	 * @param \SixtyEightPublishers\FileBundle\Control\FileManager\ConfiguredFileManagerArgs $args
	 *
	 * @return \SixtyEightPublishers\FileBundle\Storage\DataStorageInterface
	 */
	public function createDataStorage(ConfiguredFileManagerArgs $args): DataStorageInterface
	{
		$config = $this->getConfig($args->getName());
		$dataStorage = $this->dataStorageFactory->create($config->get('storage.class_name'), ...array_merge($config->get('storage.arguments'), $args->getDataStorageArgs()));

		foreach ($config->get('manipulators') as $manipulator) {
			$dataStorage->addManipulator($manipulator);
		}

		foreach (array_merge($config->get('storage.options'), $args->getOptions()) as $key => $options) {
			$dataStorage->getOptions()->set($key, $options);
		}

		return $dataStorage;
	}

	/**
	 * @param string $name
	 *
	 * @return \SixtyEightPublishers\FileBundle\Control\FileManager\Configuration
	 * @throws \SixtyEightPublishers\FileBundle\Exception\InvalidArgumentException
	 */
	private function getConfig(string $name): Configuration
	{
		if (!array_key_exists($name, $this->configurations)) {
			throw new InvalidArgumentException(sprintf(
				'Missing definition for FileManagerControl with name %s.',
				$name
			));
		}

		return new Configuration($this->configurations[$name]);
	}
}
