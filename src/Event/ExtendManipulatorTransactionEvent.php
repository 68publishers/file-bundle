<?php

declare(strict_types=1);

namespace SixtyEightPublishers\FileBundle\Event;

use Symfony\Contracts\EventDispatcher\Event;
use SixtyEightPublishers\DoctrinePersistence\TransactionInterface;
use SixtyEightPublishers\FileBundle\Storage\Options\OptionsInterface;

final class ExtendManipulatorTransactionEvent extends Event
{
	public const NAME = 'file_bundle.extend_manipulator_transaction';

	/** @var string  */
	private $manipulatorClassName;

	/** @var \SixtyEightPublishers\DoctrinePersistence\TransactionInterface  */
	private $transaction;

	/** @var \SixtyEightPublishers\FileBundle\Storage\Options\OptionsInterface  */
	private $options;

	/**
	 * @param string                                                            $manipulatorClassName
	 * @param \SixtyEightPublishers\DoctrinePersistence\TransactionInterface    $transaction
	 * @param \SixtyEightPublishers\FileBundle\Storage\Options\OptionsInterface $options
	 */
	public function __construct(string $manipulatorClassName, TransactionInterface $transaction, OptionsInterface $options)
	{
		$this->manipulatorClassName = $manipulatorClassName;
		$this->transaction = $transaction;
		$this->options = $options;
	}

	/**
	 * @return string
	 */
	public function getManipulatorClassName(): string
	{
		return $this->manipulatorClassName;
	}

	/**
	 * @return \SixtyEightPublishers\DoctrinePersistence\TransactionInterface
	 */
	public function getTransaction(): TransactionInterface
	{
		return $this->transaction;
	}

	/**
	 * @return \SixtyEightPublishers\FileBundle\Storage\Options\OptionsInterface
	 */
	public function getOptions(): OptionsInterface
	{
		return $this->options;
	}

	/**
	 * @param string $instanceOf
	 *
	 * @return bool
	 */
	public function isManipulatorInstanceOf(string $instanceOf): bool
	{
		return is_subclass_of($this->getManipulatorClassName(), $instanceOf, TRUE) || is_a($this->getManipulatorClassName(), $instanceOf, TRUE);
	}
}
