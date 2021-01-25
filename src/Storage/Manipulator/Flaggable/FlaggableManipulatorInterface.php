<?php

declare(strict_types=1);

namespace SixtyEightPublishers\FileBundle\Storage\Manipulator\Flaggable;

use SixtyEightPublishers\FileBundle\Entity\FileInterface;
use SixtyEightPublishers\FileBundle\Storage\Options\OptionsInterface;
use SixtyEightPublishers\FileBundle\Storage\Manipulator\ManipulatorInterface;

interface FlaggableManipulatorInterface extends ManipulatorInterface
{
	/**
	 * @param string $flag
	 *
	 * @return bool
	 */
	public function isFlagSupported(string $flag): bool;

	/**
	 * @param string                                                $flag
	 * @param \SixtyEightPublishers\FileBundle\Entity\FileInterface $file
	 *
	 * @return bool
	 */
	public function isFlagApplicableOnFile(string $flag, FileInterface $file): bool;

	/**
	 * @param \SixtyEightPublishers\FileBundle\Storage\Options\OptionsInterface $options
	 * @param \SixtyEightPublishers\FileBundle\Entity\FileInterface             $file
	 * @param string                                                            $flag
	 * @param bool                                                              $unique
	 *
	 * @return void
	 */
	public function __invoke(OptionsInterface $options, FileInterface $file, string $flag, bool $unique = FALSE): void;
}
