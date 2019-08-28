<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageBundle\Storage;

use Doctrine;

interface IDataStorage
{
	/**
	 * The parameter $manipulator is class that manipulates with Image eg. instance of IRemovable, ISaveable, IRotatable et..
	 *
	 * @param object $manipulator
	 *
	 * @return void
	 */
	public function addManipulator($manipulator): void;

	/**
	 * @param string $className
	 *
	 * @return bool
	 */
	public function hasManipulator(string $className): bool;

	/**
	 * @param string $className
	 *
	 * @return object
	 * @throws \SixtyEightPublishers\ImageBundle\Exception\InvalidArgumentException
	 */
	public function getManipulator(string $className);

	/**
	 * @return \Doctrine\Common\Collections\Collection|\SixtyEightPublishers\ImageBundle\DoctrineEntity\IImage[]
	 */
	public function getImages(): Doctrine\Common\Collections\Collection;
}