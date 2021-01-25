<?php

declare(strict_types=1);

namespace SixtyEightPublishers\FileBundle\Storage;

use SixtyEightPublishers\FileBundle\Storage\Options\Options;
use SixtyEightPublishers\FileBundle\Storage\Options\OptionsInterface;
use SixtyEightPublishers\FileBundle\Exception\InvalidArgumentException;
use SixtyEightPublishers\FileBundle\Storage\Manipulator\ManipulatorInterface;

trait DataStorageTrait
{
	/** @var \SixtyEightPublishers\FileBundle\Storage\Options\Options|NULL */
	private $options;

	/** @var \SixtyEightPublishers\FileBundle\Storage\Manipulator\ManipulatorInterface[]  */
	private $manipulators = [];

	/**
	 * {@inheritdoc}
	 */
	public function getOptions(): OptionsInterface
	{
		if (NULL === $this->options) {
			$this->options = new Options();
		}

		return $this->options;
	}

	/**
	 * {@inheritdoc}
	 */
	public function addManipulator(ManipulatorInterface $manipulator): void
	{
		$this->manipulators[] = $manipulator;
	}

	/**
	 * {@inheritdoc}
	 */
	public function hasManipulator(string $className): bool
	{
		try {
			$this->getManipulator($className);

			return TRUE;
		} catch (InvalidArgumentException $e) {
			return FALSE;
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function getManipulator(string $className): Manipulator\ManipulatorInterface
	{
		foreach ($this->manipulators as $manipulator) {
			if ($manipulator instanceof $className) {
				return $manipulator;
			}
		}

		throw new InvalidArgumentException(sprintf(
			'Manipulator %s is not defined in DataStorage.',
			$className
		));
	}

	/**
	 * @param string $manipulatorClassName
	 * @param mixed  ...$args
	 *
	 * @return mixed
	 * @throws \SixtyEightPublishers\FileBundle\Exception\FileManipulationException
	 */
	public function manipulate(string $manipulatorClassName, ...$args)
	{
		return $this->getManipulator($manipulatorClassName)->manipulate($this->getOptions(), ...$args);
	}
}
