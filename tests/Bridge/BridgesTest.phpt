<?php

declare(strict_types=1);

namespace SixtyEightPublishers\FileBundle\Tests\Bridge;

use Tester\Assert;
use Nette\Bootstrap\Configurator;
use Nette\Localization\Translator;
use Nette\DI\Definitions\Statement;
use Nette\Caching\Storages\MemoryStorage;
use League\Flysystem\Local\LocalFilesystemAdapter;
use Symfony\Component\EventDispatcher\EventDispatcher;
use SixtyEightPublishers\FileBundle\DI\FileBundleExtension;
use SixtyEightPublishers\FileBundle\Entity\Basic\File as BasicFile;
use SixtyEightPublishers\DoctrinePersistence\TransactionFactoryInterface;
use SixtyEightPublishers\FileStorage\Bridge\Nette\DI\FileStorageExtension;
use SixtyEightPublishers\NotificationBundle\DI\NotificationBundleExtension;
use SixtyEightPublishers\ImageStorage\Bridge\Nette\DI\ImageStorageExtension;
use SixtyEightPublishers\FileBundle\EventSubscriber\NotificationEventSubscriber;
use SixtyEightPublishers\EventDispatcherExtra\Bridge\Nette\DI\EventDispatcherExtraExtension;
use SixtyEightPublishers\FileBundle\Bridge\ImageStorage\ResourceMetadata\ImageResourceMetadataFactory;

require __DIR__ . '/../bootstrap.php';

test('Every class of the image-storage bridge loads', static function (): void {
	# an incompatible signature against the interfaces of 68publishers/image-storage is a fatal error when the class is loaded
	$dir = (string) realpath(__DIR__ . '/../../src/Bridge/ImageStorage');
	$files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($dir, \FilesystemIterator::SKIP_DOTS));
	$count = 0;

	foreach ($files as $file) {
		$class = 'SixtyEightPublishers\\FileBundle\\Bridge\\ImageStorage\\' . str_replace(['/', '.php'], ['\\', ''], substr($file->getPathname(), strlen($dir) + 1));

		Assert::true(class_exists($class) || interface_exists($class) || trait_exists($class), $class);
		$count++;
	}

	Assert::true(class_exists(NotificationEventSubscriber::class));
	Assert::true(0 < $count);
});

test('The bridges are registered with image-storage and notification-bundle', static function (): void {
	$configurator = new Configurator();
	$configurator->setTempDirectory(TEMP_PATH . '/bridges');
	$configurator->setDebugMode(FALSE);
	$configurator->addConfig([
		'extensions' => [
			'file_storage' => FileStorageExtension::class,
			'image_storage' => ImageStorageExtension::class,
			'notification' => NotificationBundleExtension::class,
			'event_dispatcher' => EventDispatcherExtraExtension::class,
			'file_bundle' => FileBundleExtension::class,
		],
		'application' => [
			'scanDirs' => FALSE,
			'scanComposer' => FALSE,
		],
		'file_storage' => [
			'storages' => [
				'default' => [
					'filesystem' => [
						'adapter' => new Statement(LocalFilesystemAdapter::class, [TEMP_PATH . '/storage']),
					],
				],
				'image' => [
					'config' => [
						'signature_key' => 'test',
					],
					'filesystem' => [
						'adapter' => new Statement(LocalFilesystemAdapter::class, [TEMP_PATH . '/images']),
					],
				],
			],
		],
		'image_storage' => [
			'storages' => [
				'image' => [
					'source_filesystem' => [
						'adapter' => new Statement(LocalFilesystemAdapter::class, [TEMP_PATH . '/source']),
					],
				],
			],
		],
		'notification' => [
			'storage' => new Statement(MemoryStorage::class),
		],
		'file_bundle' => [
			'entity' => BasicFile::class,
		],
		'services' => [
			EventDispatcher::class,
			'translator' => [
				'type' => Translator::class,
				'imported' => TRUE,
			],
			'transaction_factory' => [
				'type' => TransactionFactoryInterface::class,
				'imported' => TRUE,
			],
		],
	]);

	$container = $configurator->createContainer();

	Assert::type(ImageResourceMetadataFactory::class, $container->getService('file_bundle.resource_metadata_factory.image'));
	Assert::true($container->hasService('file_bundle.event_subscriber.notification'));
	Assert::same(NotificationEventSubscriber::class, $container->getServiceType('file_bundle.event_subscriber.notification'));
});
