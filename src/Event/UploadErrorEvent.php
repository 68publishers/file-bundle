<?php

declare(strict_types=1);

namespace SixtyEightPublishers\FileBundle\Event;

use Symfony\Contracts\EventDispatcher\Event;
use SixtyEightPublishers\FileBundle\Exception\ExceptionInterface;

final class UploadErrorEvent extends Event
{
	public const NAME = 'file_bundle.upload_error';

	/** @var \SixtyEightPublishers\FileBundle\Exception\ExceptionInterface  */
	private $exception;

	/**
	 * @param \SixtyEightPublishers\FileBundle\Exception\ExceptionInterface $exception
	 */
	public function __construct(ExceptionInterface $exception)
	{
		$this->exception = $exception;
	}

	/**
	 * @return \SixtyEightPublishers\FileBundle\Exception\ExceptionInterface
	 */
	public function getException(): ExceptionInterface
	{
		return $this->exception;
	}
}
