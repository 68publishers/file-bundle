<?php

declare(strict_types=1);

namespace SixtyEightPublishers\FileBundle\FileStorageResolver;

use Nette\Http\FileUpload;
use SixtyEightPublishers\FileStorage\FileStorageInterface;
use SixtyEightPublishers\FileStorage\FileStorageProviderInterface;

final class ContentTypeResolver implements FileStorageResolverInterface
{
	/** @var array[]  */
	private $types = [];

	/** @var \SixtyEightPublishers\FileStorage\FileStorageProviderInterface  */
	private $fileStorageProvider;

	/** @var string|NULL  */
	private $defaultStorageName;

	/**
	 * @param array                                                          $types
	 * @param \SixtyEightPublishers\FileStorage\FileStorageProviderInterface $fileStorageProvider
	 * @param string|NULL                                                    $defaultStorageName
	 */
	public function __construct(array $types, FileStorageProviderInterface $fileStorageProvider, ?string $defaultStorageName = NULL)
	{
		foreach ($types as $storageName => $contentTypes) {
			$this->addTypes((string) $storageName, (array) $contentTypes);
		}

		$this->fileStorageProvider = $fileStorageProvider;
		$this->defaultStorageName = $defaultStorageName;
	}

	/**
	 * @param string $storageName
	 * @param array  $contentTypes
	 *
	 * @return void
	 */
	public function addTypes(string $storageName, array $contentTypes): void
	{
		$this->types[$storageName] = $contentTypes;
	}

	/**
	 * {@inheritDoc}
	 */
	public function resolve(FileUpload $fileUpload): FileStorageInterface
	{
		$contentType = $fileUpload->getContentType();

		foreach ($this->types as $storageName => $contentTypes) {
			if (in_array($contentType, $contentTypes, TRUE)) {
				return $this->fileStorageProvider->get($storageName);
			}
		}

		return $this->fileStorageProvider->get($this->defaultStorageName);
	}
}
