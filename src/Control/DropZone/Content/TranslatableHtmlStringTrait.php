<?php

declare(strict_types=1);

namespace SixtyEightPublishers\FileBundle\Control\DropZone\Content;

use Nette\Localization\ITranslator;
use SixtyEightPublishers\FileBundle\Exception\InvalidStateException;

trait TranslatableHtmlStringTrait
{
	/** @var \Nette\Localization\ITranslator|NULL */
	private $translator;

	/**
	 * @param \Nette\Localization\ITranslator $translator
	 *
	 * @return void
	 */
	public function setTranslator(ITranslator $translator): void
	{
		$this->translator = $translator;
	}

	/**
	 * @return \Nette\Localization\ITranslator
	 * @throws \SixtyEightPublishers\FileBundle\Exception\InvalidStateException
	 */
	protected function getTranslator(): ITranslator
	{
		if (NULL === $this->translator) {
			throw new InvalidStateException(sprintf(
				'Translator for class %s is not set.',
				static::class
			));
		}

		return $this->translator;
	}
}
