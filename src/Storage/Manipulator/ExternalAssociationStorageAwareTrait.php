<?php

declare(strict_types=1);

namespace SixtyEightPublishers\FileBundle\Storage\Manipulator;

use SixtyEightPublishers\FileBundle\Storage\ExternalAssociation\ExternalAssociationStorageInterface;

trait ExternalAssociationStorageAwareTrait
{
	/** @var \SixtyEightPublishers\FileBundle\Storage\ExternalAssociation\ExternalAssociationStorageInterface|NULL */
	private $associationStorage;

	/**
	 * @param \SixtyEightPublishers\FileBundle\Storage\ExternalAssociation\ExternalAssociationStorageInterface $externalAssociationStorage
	 *
	 * @return void
	 */
	public function setExternalAssociationStorage(ExternalAssociationStorageInterface $externalAssociationStorage): void
	{
		$this->associationStorage = $externalAssociationStorage;
	}

	/**
	 * @return \SixtyEightPublishers\FileBundle\Storage\ExternalAssociation\ExternalAssociationStorageInterface|NULL
	 */
	public function getExternalAssociationStorage(): ?ExternalAssociationStorageInterface
	{
		return $this->associationStorage;
	}
}
