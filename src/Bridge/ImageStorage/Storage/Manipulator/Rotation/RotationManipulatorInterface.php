<?php

declare(strict_types=1);

namespace SixtyEightPublishers\FileBundle\Bridge\ImageStorage\Storage\Manipulator\Rotation;

use SixtyEightPublishers\FileBundle\Entity\FileInterface;
use SixtyEightPublishers\FileBundle\Storage\Options\OptionsInterface;
use SixtyEightPublishers\FileBundle\Storage\Manipulator\ManipulatorInterface;

interface RotationManipulatorInterface extends ManipulatorInterface
{
	/**
	 * @param \SixtyEightPublishers\FileBundle\Storage\Options\OptionsInterface $options
	 * @param \SixtyEightPublishers\FileBundle\Entity\FileInterface             $file
	 * @param int                                                               $degrees
	 *
	 * @return void
	 */
	public function __invoke(OptionsInterface $options, FileInterface $file, int $degrees): void;
}
