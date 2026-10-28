<?php

declare(strict_types=1);

namespace SixtyEightPublishers\FileBundle\Tests\Control;

use Latte\Engine;
use Tester\Assert;
use Latte\Essential\TranslatorExtension;
use Nette\Bridges\ApplicationLatte\UIExtension;

require __DIR__ . '/../bootstrap.php';

test('Default templates can be compiled', static function (): void {
	$latte = new Engine();
	$latte->addExtension(new UIExtension(NULL));
	$latte->addExtension(new TranslatorExtension(NULL));
	$latte->addFunction('x_descriptor', static fn () => NULL); # registered by 68publishers/image-storage

	foreach (['DropZone/templates/dropZoneControl.latte', 'FileManager/templates/fileManagerControl.latte'] as $template) {
		Assert::type('string', $latte->compile(__DIR__ . '/../../src/Control/' . $template));
	}
});
