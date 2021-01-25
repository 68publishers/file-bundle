<?php

declare(strict_types=1);

namespace SixtyEightPublishers\FileBundle\Storage\Manipulator\Sortable;

use SixtyEightPublishers\FileBundle\Entity\FileInterface;
use SixtyEightPublishers\FileBundle\Storage\Options\OptionsInterface;
use SixtyEightPublishers\FileBundle\Storage\Manipulator\AbstractManipulator;
use SixtyEightPublishers\FileBundle\Storage\Manipulator\ExternalAssociationStorageAwareTrait;
use SixtyEightPublishers\FileBundle\Storage\Manipulator\ExternalAssociationStorageAwareInterface;

abstract class AbstractSortableManipulator extends AbstractManipulator implements SortableManipulatorInterface, ExternalAssociationStorageAwareInterface
{
	use ExternalAssociationStorageAwareTrait;

	/**
	 * {@inheritdoc}
	 */
	public function __invoke(OptionsInterface $options, FileInterface $sortedFile, ?FileInterface $previousFile = NULL, ?FileInterface $nextFile = NULL): void
	{
		if (FALSE === $this->doSort($options, $sortedFile, $previousFile, $nextFile)) {
			return;
		}

		# External associations
		$associationStorage = $this->getExternalAssociationStorage();

		if (NULL === $associationStorage) {
			return;
		}

		$references = $associationStorage->getReferences();
		$sortedReference = $references->find((string) $sortedFile->getId());

		if (NULL === $sortedReference) {
			return;
		}

		$previousReference = NULL !== $previousFile ? $references->find((string) $previousFile->getId()) : NULL;

		if (NULL !== $previousReference) {
			$references->moveAfter($sortedReference, $previousReference);
			$associationStorage->flush();

			return;
		}

		$nextReference = NULL !== $nextFile ? $references->find((string) $nextFile->getId()) : NULL;

		if (NULL !== $nextReference) {
			$references->moveBefore($sortedReference, $nextReference);
			$associationStorage->flush();
		}
	}

	/**
	 * Return TRUE if everything is OK otherwise return FALSE or better throw an exception.
	 *
	 * @param \SixtyEightPublishers\FileBundle\Storage\Options\OptionsInterface $options
	 * @param \SixtyEightPublishers\FileBundle\Entity\FileInterface             $sortedFile
	 * @param \SixtyEightPublishers\FileBundle\Entity\FileInterface|NULL        $previousFile
	 * @param \SixtyEightPublishers\FileBundle\Entity\FileInterface|NULL        $nextFile
	 *
	 * @return bool
	 */
	abstract public function doSort(OptionsInterface $options, FileInterface $sortedFile, ?FileInterface $previousFile, ?FileInterface $nextFile): bool;
}
