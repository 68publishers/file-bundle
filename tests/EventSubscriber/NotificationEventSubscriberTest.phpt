<?php

declare(strict_types=1);

namespace SixtyEightPublishers\FileBundle\Tests\EventSubscriber;

use Tester\Assert;
use ReflectionClass;
use ReflectionMethod;
use ReflectionProperty;
use Nette\Application\UI\Presenter;
use SixtyEightPublishers\FileBundle\EventSubscriber\NotificationEventSubscriber;

require __DIR__ . '/../bootstrap.php';

test('A warning is triggered instead of an exception if an AJAX presenter cannot redraw the messages', static function (): void {
	$subscriber = (new ReflectionClass(NotificationEventSubscriber::class))->newInstanceWithoutConstructor();
	(new ReflectionProperty($subscriber, 'presenter'))->setValue($subscriber, new class extends Presenter {
		public function isAjax(): bool
		{
			return TRUE;
		}
	});

	Assert::error(
		static fn () => (new ReflectionMethod($subscriber, 'redrawMessages'))->invoke($subscriber),
		E_USER_NOTICE,
		'~^Presenter .+ does\'t implement method ::redrawMessages\(\), notifications can\'t be redrawn\.$~'
	);
});
