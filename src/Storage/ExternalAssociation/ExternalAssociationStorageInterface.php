<?php

declare(strict_types=1);

namespace SixtyEightPublishers\FileBundle\Storage\ExternalAssociation;

interface ExternalAssociationStorageInterface
{
	/**
	 * @param string $namespace
	 *
	 * @return void
	 */
	public function setNamespace(string $namespace): void;

	/**
	 * @return \SixtyEightPublishers\FileBundle\Storage\ExternalAssociation\ReferenceCollectionInterface
	 */
	public function getReferences(): ReferenceCollectionInterface;

	/**
	 * Save collection into storage
	 */
	public function flush(): void;

	/**
	 * Clean & Destroy storage section
	 *
	 * @return void
	 */
	public function clean(): void;
}
