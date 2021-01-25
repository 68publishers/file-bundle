<?php

declare(strict_types=1);

namespace SixtyEightPublishers\FileBundle\Storage\Manipulator;

use SixtyEightPublishers\FileBundle\Storage\Options\OptionsInterface;
use SixtyEightPublishers\EventDispatcherExtra\EventDispatcherAwareInterface;

interface ManipulatorInterface extends EventDispatcherAwareInterface
{
	/**
	 * @param \SixtyEightPublishers\FileBundle\Storage\Options\OptionsInterface $options
	 * @param mixed                                                             ...$args
	 *
	 * @return mixed
	 * @throws \SixtyEightPublishers\FileBundle\Exception\FileManipulationException
	 */
	public function manipulate(OptionsInterface $options, ...$args);
}
