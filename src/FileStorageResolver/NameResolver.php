<?php

declare(strict_types=1);

namespace SixtyEightPublishers\FileBundle\FileStorageResolver;

use Nette\Http\FileUpload;
use SixtyEightPublishers\FileStorage\FileStorageInterface;
use SixtyEightPublishers\FileStorage\FileStorageProviderInterface;

final class NameResolver implements FileStorageResolverInterface
{
	/** @var string|NULL  */
	private $name;

	/** @var \SixtyEightPublishers\FileStorage\FileStorageProviderInterface  */
	private $fileStorageProvider;

	/**
	 * @param string|NULL                                                    $name
	 * @param \SixtyEightPublishers\FileStorage\FileStorageProviderInterface $fileStorageProvider
	 */
	public function __construct(?string $name, FileStorageProviderInterface $fileStorageProvider)
	{
		$this->name = $name;
		$this->fileStorageProvider = $fileStorageProvider;
	}

	/**
	 * {@inheritDoc}
	 */
	public function resolve(FileUpload $fileUpload): FileStorageInterface
	{
		return $this->fileStorageProvider->get($this->name);
	}
}
