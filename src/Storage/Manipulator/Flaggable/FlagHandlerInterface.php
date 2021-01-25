<?php

declare(strict_types=1);

namespace SixtyEightPublishers\FileBundle\Storage\Manipulator\Flaggable;

use SixtyEightPublishers\FileBundle\Entity\FileInterface;
use SixtyEightPublishers\FileBundle\Storage\Options\OptionsInterface;

interface FlagHandlerInterface
{
	/**
	 * @param \SixtyEightPublishers\FileBundle\Entity\FileInterface $file
	 *
	 * @return bool
	 */
	public function canHandle(FileInterface $file): bool;

	/**
	 * @param \SixtyEightPublishers\FileBundle\Storage\Options\OptionsInterface $options
	 * @param \SixtyEightPublishers\FileBundle\Entity\FileInterface             $file
	 * @param bool                                                              $unique
	 *
	 * @return void
	 */
	public function __invoke(OptionsInterface $options, FileInterface $file, bool $unique = FALSE): void;
}
