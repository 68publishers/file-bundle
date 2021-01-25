<?php

declare(strict_types=1);

namespace SixtyEightPublishers\FileBundle\Event;

use Symfony\Contracts\EventDispatcher\Event;
use SixtyEightPublishers\FileBundle\Entity\FileInterface;

final class FileUploadedEvent extends Event
{
	public const NAME = 'file_bundle.file_uploaded';

	/** @var \SixtyEightPublishers\FileBundle\Entity\FileInterface  */
	private $file;

	/**
	 * @param \SixtyEightPublishers\FileBundle\Entity\FileInterface $file
	 */
	public function __construct(FileInterface $file)
	{
		$this->file = $file;
	}

	/**
	 * @return \SixtyEightPublishers\FileBundle\Entity\FileInterface
	 */
	public function getFile(): FileInterface
	{
		return $this->file;
	}
}
