<?php

declare(strict_types=1);

namespace SixtyEightPublishers\FileBundle\Control\DropZone\Content;

use Nette\Utils\Html;
use Nette\HtmlStringable;

final class ProgressBar implements HtmlStringable
{
	/** @var array  */
	private $classes = [
		'progress-bar',
	];

	/**
	 * @param array $classes
	 *
	 * @return \SixtyEightPublishers\FileBundle\Control\DropZone\Content\ProgressBar
	 */
	public function setClasses(array $classes): self
	{
		$this->classes = $classes;

		return $this;
	}

	/**
	 * @return \SixtyEightPublishers\FileBundle\Control\DropZone\Content\ProgressBar
	 */
	public function striped(): self
	{
		$this->classes[] = 'progress-bar-striped';

		return $this;
	}

	/**
	 * @return \SixtyEightPublishers\FileBundle\Control\DropZone\Content\ProgressBar
	 */
	public function animated(): self
	{
		$this->classes[] = 'progress-bar-animated';

		return $this;
	}

	/************* interface \Nette\HtmlStringable *************/

	/**
	 * {@inheritdoc}
	 */
	public function __toString(): string
	{
		$html = Html::el('div class="progress my-2"')
			->addHtml(Html::el('div role="progressbar"')->setAttribute('class', $this->classes));

		return (string) $html;
	}
}
