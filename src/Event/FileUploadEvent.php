<?php

declare(strict_types=1);

namespace SixtyEightPublishers\FileBundle\Event;

use Nette\Http\FileUpload;
use Symfony\Contracts\EventDispatcher\Event;

final class FileUploadEvent extends Event
{
	public const NAME = 'file_bundle.file_upload';

	/** @var \Nette\Http\FileUpload  */
	private $fileUpload;

	/**
	 * @param \Nette\Http\FileUpload $fileUpload
	 */
	public function __construct(FileUpload $fileUpload)
	{
		$this->fileUpload = $fileUpload;
	}

	/**
	 * @return \Nette\Http\FileUpload
	 */
	public function getFileUpload(): FileUpload
	{
		return $this->fileUpload;
	}
}
