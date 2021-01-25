<?php

declare(strict_types=1);

namespace SixtyEightPublishers\FileBundle\Event;

use Symfony\Contracts\EventDispatcher\Event;
use SixtyEightPublishers\FileBundle\Entity\FileInterface;

final class ActionSuccessEvent extends Event
{
	public const NAME = 'file_bundle.action_success';

	/** @var string  */
	private $actionName;

	/** @var \SixtyEightPublishers\FileBundle\Entity\FileInterface  */
	private $file;

	/**
	 * @param string                                                $actionName
	 * @param \SixtyEightPublishers\FileBundle\Entity\FileInterface $file
	 */
	public function __construct(string $actionName, FileInterface $file)
	{
		$this->actionName = $actionName;
		$this->file = $file;
	}

	/**
	 * @return string
	 */
	public function getActionName(): string
	{
		return $this->actionName;
	}

	/**
	 * @return \SixtyEightPublishers\FileBundle\Entity\FileInterface
	 */
	public function getFile(): FileInterface
	{
		return $this->file;
	}
}
