<?php

declare(strict_types=1);

namespace SixtyEightPublishers\FileBundle\Storage\Manipulator\Save;

use Nette\Http\FileUpload;
use SixtyEightPublishers\FileBundle\Entity\FileInterface;
use SixtyEightPublishers\FileBundle\Storage\Options\OptionsInterface;
use SixtyEightPublishers\FileBundle\Storage\Manipulator\ManipulatorInterface;

interface SaveManipulatorInterface extends ManipulatorInterface
{
	/**
	 * @param \SixtyEightPublishers\FileBundle\Storage\Options\OptionsInterface $options
	 * @param \Nette\Http\FileUpload                                            $fileUpload
	 *
	 * @return \SixtyEightPublishers\FileBundle\Entity\FileInterface
	 */
	public function __invoke(OptionsInterface $options, FileUpload $fileUpload): FileInterface;
}
