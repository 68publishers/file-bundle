<?php

declare(strict_types=1);

namespace SixtyEightPublishers\FileBundle\Storage\Manipulator;

use Throwable;
use SixtyEightPublishers\DoctrinePersistence\TransactionInterface;
use SixtyEightPublishers\FileBundle\Exception\InvalidStateException;
use SixtyEightPublishers\FileBundle\Storage\Options\OptionsInterface;
use SixtyEightPublishers\EventDispatcherExtra\EventDispatcherAwareTrait;
use SixtyEightPublishers\FileBundle\Exception\FileManipulationException;
use SixtyEightPublishers\FileBundle\Event\ExtendManipulatorTransactionEvent;

abstract class AbstractManipulator implements ManipulatorInterface
{
	use EventDispatcherAwareTrait;

	/**
	 * {@inheritDoc}
	 *
	 * @throws \SixtyEightPublishers\FileBundle\Exception\InvalidStateException
	 */
	public function manipulate(OptionsInterface $options, ...$args)
	{
		$cb = $this;

		if (!is_callable($cb)) {
			throw new InvalidStateException(sprintf(
				'Class %s is not callable, please implement method %s::__invoke().',
				static::class,
				static::class
			));
		}

		try {
			return $cb($options, ...$args);
		} catch (FileManipulationException $e) {
			throw $e;
		} catch (Throwable $e) {
			throw FileManipulationException::error(static::class, 0, $e);
		}
	}

	/**
	 * @param \SixtyEightPublishers\DoctrinePersistence\TransactionInterface    $transaction
	 * @param \SixtyEightPublishers\FileBundle\Storage\Options\OptionsInterface $options
	 *
	 * @return void
	 */
	protected function dispatchExtendTransactionEvent(TransactionInterface $transaction, OptionsInterface $options): void
	{
		$this->getEventDispatcher()->dispatch(
			new ExtendManipulatorTransactionEvent(static::class, $transaction, $options),
			ExtendManipulatorTransactionEvent::NAME
		);
	}
}
