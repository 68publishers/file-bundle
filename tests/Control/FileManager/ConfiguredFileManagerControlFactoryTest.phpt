<?php

declare(strict_types=1);

namespace SixtyEightPublishers\FileBundle\Tests\Control\FileManager;

use Tester\Assert;
use ReflectionMethod;
use SixtyEightPublishers\FileBundle\Tests\ContainerFactory;
use SixtyEightPublishers\FileBundle\Entity\Basic\File as BasicFile;
use SixtyEightPublishers\FileBundle\Control\FileManager\FileManagerControl;
use SixtyEightPublishers\FileBundle\Control\FileManager\ConfiguredFileManagerControlFactory;

require __DIR__ . '/../../bootstrap.php';

function resolveTemplate(FileManagerControl $control): string
{
	return (new ReflectionMethod($control, 'getTemplateFileResolver'))->invoke($control)->resolve();
}

test('Templates prefixed with `@` are relative to the directory with default templates', static function (): void {
	$factory = ContainerFactory::create([
		'entity' => BasicFile::class,
		'file_managers' => [
			'relative' => [
				'template' => '@fileManagerControl.latte',
			],
			'relative_escaped' => [ # nette/di 3.2+ escapes quoted strings in NEON files this way
				'template' => '@@fileManagerControl.latte',
			],
			'absolute' => [
				'template' => __DIR__ . '/../../../src/Control/FileManager/templates/fileManagerControl.latte',
			],
		],
	])->getByType(ConfiguredFileManagerControlFactory::class);

	$expected = realpath(__DIR__ . '/../../../src/Control/FileManager/templates/fileManagerControl.latte');

	Assert::same($expected, resolveTemplate($factory->create($factory->createArgs('relative'))));
	Assert::same($expected, resolveTemplate($factory->create($factory->createArgs('relative_escaped'))));
	Assert::same($expected, resolveTemplate($factory->create($factory->createArgs('absolute'))));
});
