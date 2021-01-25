<?php

declare(strict_types=1);

namespace SixtyEightPublishers\FileBundle\Storage\Manipulator\Sortable;

use SixtyEightPublishers\FileBundle\Entity\FileInterface;
use SixtyEightPublishers\FileBundle\Storage\Options\OptionsInterface;

final class NonPersistentSortableManipulator extends AbstractSortableManipulator
{
	/**
	 * {@inheritDoc}
	 */
	public function doSort(OptionsInterface $options, FileInterface $sortedFile, ?FileInterface $previousFile, ?FileInterface $nextFile): bool
	{
		return TRUE;
	}
}
