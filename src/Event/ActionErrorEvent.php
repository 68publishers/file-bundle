<?php

declare(strict_types=1);

namespace SixtyEightPublishers\FileBundle\Event;

use Symfony\Contracts\EventDispatcher\Event;
use SixtyEightPublishers\FileBundle\Exception\ExceptionInterface;

final class ActionErrorEvent extends Event
{
	public const NAME = 'file_bundle.action_error';

	/** @var string  */
	private $actionName;

	/** @var \SixtyEightPublishers\FileBundle\Exception\ExceptionInterface  */
	private $exception;

	/**
	 * @param string                                                        $actionName
	 * @param \SixtyEightPublishers\FileBundle\Exception\ExceptionInterface $exception
	 */
	public function __construct(string $actionName, ExceptionInterface $exception)
	{
		$this->actionName = $actionName;
		$this->exception = $exception;
	}

	/**
	 * @return string
	 */
	public function getActionName(): string
	{
		return $this->actionName;
	}

	/**
	 * @return \SixtyEightPublishers\FileBundle\Exception\ExceptionInterface
	 */
	public function getException(): ExceptionInterface
	{
		return $this->exception;
	}
}
