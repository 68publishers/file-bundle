<?php

declare(strict_types=1);

namespace SixtyEightPublishers\FileBundle\Event;

use Symfony\Contracts\EventDispatcher\Event;

final class UploadCompletedEvent extends Event
{
	public const NAME = 'file_bundle.upload_completed';

	/** @var int  */
	private $filesCount;

	/**
	 * @param int $filesCount
	 */
	public function __construct(int $filesCount)
	{
		$this->filesCount = $filesCount;
	}

	/**
	 * @return int
	 */
	public function getFilesCount(): int
	{
		return $this->filesCount;
	}
}
