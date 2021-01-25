<?php

declare(strict_types=1);

namespace SixtyEightPublishers\FileBundle\FileStorageResolver;

use Nette\Http\FileUpload;
use SixtyEightPublishers\FileStorage\FileStorageInterface;

interface FileStorageResolverInterface
{
	/**
	 * @param \Nette\Http\FileUpload $fileUpload
	 *
	 * @return \SixtyEightPublishers\FileStorage\FileStorageInterface
	 */
	public function resolve(FileUpload $fileUpload): FileStorageInterface;
}
