<?php

declare(strict_types=1);

namespace SixtyEightPublishers\FileBundle\Tests\DI;

use Tester\Assert;
use Nette\DI\Container;
use ReflectionProperty;
use SixtyEightPublishers\FileBundle\Action\DeleteAction;
use SixtyEightPublishers\FileBundle\Entity\FileInterface;
use SixtyEightPublishers\FileBundle\Action\DownloadAction;
use SixtyEightPublishers\FileBundle\DI\FileBundleExtension;
use SixtyEightPublishers\FileBundle\Tests\ContainerFactory;
use SixtyEightPublishers\FileBundle\Entity\Basic\File as BasicFile;
use SixtyEightPublishers\FileBundle\Exception\InvalidStateException;
use SixtyEightPublishers\DoctrineBridge\Bridge\Nette\DI\TargetEntity;
use SixtyEightPublishers\FileBundle\Entity\SoftDeletableFileInterface;
use SixtyEightPublishers\FileBundle\Entity\SoftDeletable\File as SoftDeletableFile;
use SixtyEightPublishers\FileBundle\Control\FileManager\ConfiguredFileManagerControlFactory;

require __DIR__ . '/../bootstrap.php';

function getConfigurations(Container $container): array
{
	$factory = $container->getByType(ConfiguredFileManagerControlFactory::class);

	return (new ReflectionProperty($factory, 'configurations'))->getValue($factory);
}

test('File managers without `extends`', static function (): void {
	$configurations = getConfigurations(ContainerFactory::create([
		'entity' => BasicFile::class,
		'file_managers' => [
			'a' => [
				'max_allowed_files' => 3,
				'actions' => [DownloadAction::class],
			],
		],
	]));

	Assert::same(['a'], array_keys($configurations));
	Assert::same(3, $configurations['a']['max_allowed_files']);
	Assert::count(1, $configurations['a']['actions']);
});

test('A file manager extends options of another file manager', static function (): void {
	$configurations = getConfigurations(ContainerFactory::create([
		'entity' => BasicFile::class,
		'file_managers' => [
			'a' => [
				'template' => '@fileManagerControl.latte',
				'max_allowed_files' => 3,
				'max_file_size' => 100,
				'actions' => [DownloadAction::class],
			],
			'b' => [
				'extends' => 'a',
				'max_file_size' => 1024,
				'actions' => [DeleteAction::class],
			],
		],
	]));

	$b = $configurations['b'];

	Assert::false(array_key_exists('extends', $b));
	Assert::same('@fileManagerControl.latte', $b['template']);
	Assert::same(3, $b['max_allowed_files']);
	Assert::same(1024, $b['max_file_size']);
	Assert::count(2, $b['actions']); # lists are merged
	Assert::same(100, $configurations['a']['max_file_size']);
	Assert::count(1, $configurations['a']['actions']);
});

test('An exception is thrown if the extended file manager is missing', static function (): void {
	Assert::exception(
		static fn () => ContainerFactory::create([
			'entity' => BasicFile::class,
			'file_managers' => [
				'c' => [
					'extends' => 'missing',
				],
			],
		]),
		InvalidStateException::class,
		'Configuration for file manager with name "missing" is missing.'
	);
});

test('Target entities of the basic entity', static function (): void {
	$extension = new FileBundleExtension();
	$extension->setConfig((object) ['entity' => BasicFile::class, 'file_managers' => []]);

	Assert::equal([
		new TargetEntity(FileInterface::class, BasicFile::class),
	], $extension->getTargetEntities());
});

test('Target entities of the soft-deletable entity', static function (): void {
	$extension = new FileBundleExtension();
	$extension->setConfig((object) ['entity' => SoftDeletableFile::class, 'file_managers' => []]);

	Assert::equal([
		new TargetEntity(FileInterface::class, SoftDeletableFile::class),
		new TargetEntity(SoftDeletableFileInterface::class, SoftDeletableFile::class),
	], $extension->getTargetEntities());
});
