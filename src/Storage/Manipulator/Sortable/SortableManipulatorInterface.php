<?php

declare(strict_types=1);

namespace SixtyEightPublishers\FileBundle\Storage\Manipulator\Sortable;

use SixtyEightPublishers\FileBundle\Entity\FileInterface;
use SixtyEightPublishers\FileBundle\Storage\Options\OptionsInterface;
use SixtyEightPublishers\FileBundle\Storage\Manipulator\ManipulatorInterface;

interface SortableManipulatorInterface extends ManipulatorInterface
{
	/**
	 * @param \SixtyEightPublishers\FileBundle\Storage\Options\OptionsInterface $options
	 * @param \SixtyEightPublishers\FileBundle\Entity\FileInterface             $sortedFile
	 * @param \SixtyEightPublishers\FileBundle\Entity\FileInterface|NULL        $previousFile
	 * @param \SixtyEightPublishers\FileBundle\Entity\FileInterface|NULL        $nextFile
	 *
	 * @return void
	 */
	public function __invoke(OptionsInterface $options, FileInterface $sortedFile, ?FileInterface $previousFile = NULL, ?FileInterface $nextFile = NULL): void;
}
