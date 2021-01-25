<?php

declare(strict_types=1);

namespace SixtyEightPublishers\FileBundle\Storage\Manipulator;

use SixtyEightPublishers\FileBundle\Storage\ExternalAssociation\ExternalAssociationStorageInterface;

interface ExternalAssociationStorageAwareInterface
{
	/**
	 * @param \SixtyEightPublishers\FileBundle\Storage\ExternalAssociation\ExternalAssociationStorageInterface $externalAssociationStorage
	 *
	 * @return void
	 */
	public function setExternalAssociationStorage(ExternalAssociationStorageInterface $externalAssociationStorage): void;
}
