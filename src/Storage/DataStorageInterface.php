<?php

declare(strict_types=1);

namespace SixtyEightPublishers\FileBundle\Storage;

use Doctrine;
use SixtyEightPublishers\FileBundle\Storage\Options\OptionsInterface;
use SixtyEightPublishers\FileBundle\Storage\Manipulator\ManipulatorInterface;

interface DataStorageInterface
{
	/**
	 * @return \SixtyEightPublishers\FileBundle\Storage\Options\OptionsInterface
	 */
	public function getOptions(): OptionsInterface;

	/**
	 * @param \SixtyEightPublishers\FileBundle\Storage\Manipulator\ManipulatorInterface $manipulator
	 *
	 * @return void
	 */
	public function addManipulator(ManipulatorInterface $manipulator): void;

	/**
	 * @param string $className
	 *
	 * @return bool
	 */
	public function hasManipulator(string $className): bool;

	/**
	 * @param string $className
	 *
	 * @return \SixtyEightPublishers\FileBundle\Storage\Manipulator\ManipulatorInterface
	 * @throws \SixtyEightPublishers\FileBundle\Exception\InvalidArgumentException
	 */
	public function getManipulator(string $className): ManipulatorInterface;

	/**
	 * @param string $manipulatorClassName
	 * @param mixed  ...$args
	 *
	 * @return mixed
	 * @throws \SixtyEightPublishers\FileBundle\Exception\FileManipulationException
	 */
	public function manipulate(string $manipulatorClassName, ...$args);

	/**
	 * @return \Doctrine\Common\Collections\Collection|\SixtyEightPublishers\FileBundle\Entity\FileInterface[]
	 */
	public function getFiles(): Doctrine\Common\Collections\Collection;
}
