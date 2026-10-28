<?php

declare(strict_types=1);

namespace SixtyEightPublishers\FileBundle\Tests\Control\DropZone;

use Tester\Assert;
use Nette\Http\Request;
use Nette\Http\Response;
use Nette\Http\UrlScript;
use Nette\Http\FileUpload;
use Symfony\Component\EventDispatcher\EventDispatcher;
use SixtyEightPublishers\FileBundle\Event\UploadErrorEvent;
use SixtyEightPublishers\FileBundle\Exception\UploadException;
use SixtyEightPublishers\FileBundle\Event\UploadCompletedEvent;
use SixtyEightPublishers\FileBundle\Control\DropZone\DropZoneControl;

require __DIR__ . '/../../bootstrap.php';

function createControl(EventDispatcher $dispatcher, array $files = []): DropZoneControl
{
	$control = new DropZoneControl(new Request(new UrlScript('http://localhost/'), [], $files), new Response());
	$control->setEventDispatcher($dispatcher);

	return $control;
}

test('Signal `completed` without the files count', static function (): void {
	$dispatcher = new EventDispatcher();
	$events = [];
	$dispatcher->addListener(UploadCompletedEvent::NAME, static function (UploadCompletedEvent $event) use (&$events): void {
		$events[] = $event;
	});

	createControl($dispatcher)->handleCompleted(NULL);
	createControl($dispatcher)->handleCompleted(2);

	Assert::count(2, $events);
	Assert::same(0, $events[0]->getFilesCount());
	Assert::same(2, $events[1]->getFilesCount());
});

test('Upload of a file with an unsupported type and extension', static function (): void {
	$file = tempnam(TEMP_PATH, 'upload');
	file_put_contents($file, "\0");

	$dispatcher = new EventDispatcher();
	$errors = [];
	$dispatcher->addListener(UploadErrorEvent::NAME, static function (UploadErrorEvent $event) use (&$errors): void {
		$errors[] = $event->getException();
	});

	$upload = new FileUpload(['name' => 'document.unknown', 'size' => 1, 'tmp_name' => $file, 'error' => UPLOAD_ERR_OK]);
	$control = createControl($dispatcher, ['file' => $upload]);
	$control->setAccepted(['image/png', '.pdf']);
	$control->handleUpload();

	Assert::count(1, $errors);
	Assert::type(UploadException::class, $errors[0]);
	Assert::same(UploadException::UNSUPPORTED_TYPE, $errors[0]->getCode());
});
