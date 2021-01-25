<?php

declare(strict_types=1);

namespace SixtyEightPublishers\FileBundle\Entity\Basic;

use Doctrine\ORM\Mapping as ORM;
use SixtyEightPublishers\FileBundle\Entity\AbstractFile;

/**
 * @ORM\Entity
 * @ORM\Table(indexes={
 *     @ORM\Index(name="IDX_FILE_CREATED", columns={"created"})
 * })
 */
class File extends AbstractFile
{
}
