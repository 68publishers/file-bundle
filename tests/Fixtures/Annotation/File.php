<?php

declare(strict_types=1);

namespace SixtyEightPublishers\FileBundle\Tests\Fixtures\Annotation;

use Doctrine\ORM\Mapping as ORM;
use SixtyEightPublishers\FileBundle\Entity\AbstractFile;

/**
 * An application entity mapped with annotations (doctrine/orm 2) that extends the mapped superclass.
 *
 * @ORM\Entity
 * @ORM\Table(indexes={
 *     @ORM\Index(name="IDX_FILE_CREATED", columns={"created"}),
 *     @ORM\Index(name="IDX_GIN_FILE_METADATA", columns={"metadata"}, flags={"gin"}),
 * })
 */
class File extends AbstractFile
{
}
