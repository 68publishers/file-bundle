<?php

declare(strict_types=1);

namespace SixtyEightPublishers\FileBundle\Storage\ExternalAssociation;

use Nette\Http\Session;
use Nette\Http\SessionSection;
use SixtyEightPublishers\FileBundle\Exception\InvalidStateException;

class SessionAssociationStorage implements ExternalAssociationStorageInterface
{
	/** @var \Nette\Http\Session  */
	private $session;

	/** @var \Nette\Http\SessionSection|NULL */
	private $sessionSection;

	/** @var string|NULL */
	private $namespace;

	/** @var string|int|\DateTimeInterface  */
	private $expiration = '1 hour';

	/** @var \SixtyEightPublishers\FileBundle\Storage\ExternalAssociation\ReferenceCollection|NULL */
	private $collection;

	/**
	 * @param \Nette\Http\Session $session
	 */
	public function __construct(Session $session)
	{
		$this->session = $session;
	}

	/**
	 * @param string|int|\DateTimeInterface $expiration
	 *
	 * @return \SixtyEightPublishers\FileBundle\Storage\ExternalAssociation\SessionAssociationStorage
	 */
	public function setExpiration($expiration): self
	{
		$this->expiration = $expiration;

		return $this;
	}

	/**
	 * @return \Nette\Http\SessionSection
	 */
	private function getSection(): SessionSection
	{
		if (NULL === $this->sessionSection) {
			$this->sessionSection = $this->session
				->getSection(str_replace('\\', '.', static::class) . (NULL === $this->namespace ? '' : '//' . $this->namespace))
				->setExpiration($this->expiration);

			$this->sessionSection->warnOnUndefined = FALSE;
		}

		return $this->sessionSection;
	}

	/**
	 * {@inheritDoc}
	 */
	public function setNamespace(string $namespace): void
	{
		if (NULL !== $this->sessionSection) {
			throw new InvalidStateException('A namespace can￿\'t be changed if a session section was already created.');
		}

		$this->namespace = $namespace;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getReferences(): ReferenceCollectionInterface
	{
		if (NULL === $this->collection) {
			$this->collection = new ReferenceCollection($this->getSection()['references'] ?? []);
		}

		return $this->collection;
	}

	/**
	 * {@inheritDoc}
	 */
	public function flush(): void
	{
		if (NULL === $this->collection) {
			return;
		}

		$this->getSection()['references'] = $this->collection->toArray();
	}

	/**
	 * {@inheritDoc}
	 */
	public function clean(): void
	{
		$this->collection = NULL;
		$this->getSection()->remove();
	}
}
