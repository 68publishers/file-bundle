<?php

declare(strict_types=1);

namespace SixtyEightPublishers\FileBundle\Control\DropZone\Content;

use Nette\Localization\Translator;
use SixtyEightPublishers\FileBundle\Exception\InvalidStateException;

trait TranslatableHtmlStringTrait
{
	/** @var \Nette\Localization\Translator|NULL */
	private $translator;

	/**
	 * @param \Nette\Localization\Translator $translator
	 *
	 * @return void
	 */
	public function setTranslator(Translator $translator): void
	{
		$this->translator = $translator;
	}

	/**
	 * @return \Nette\Localization\Translator
	 * @throws \SixtyEightPublishers\FileBundle\Exception\InvalidStateException
	 */
	protected function getTranslator(): Translator
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
