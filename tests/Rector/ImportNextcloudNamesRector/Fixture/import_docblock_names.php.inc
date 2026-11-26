<?php

namespace Nextcloud\Rector\Test\Rector\ImportNextcloudNamesRector\Fixture;

class ImportDocblockNames
{
    /** @var array<string, \OCP\Files\Node> */
    private array $nodes = [];

    /**
     * @param \OCP\Files\Folder $folder
     * @param \OCP\Share\IManager $manager
     * @param \Psr\Log\LoggerInterface $logger
     * @return list<\OCP\Files\File>
     */
    public function foo($folder, $manager, $logger): array
    {
        return [];
    }
}

?>
-----
<?php

namespace Nextcloud\Rector\Test\Rector\ImportNextcloudNamesRector\Fixture;

use OCP\Files\Node;
use OCP\Files\Folder;
use OCP\Files\File;

class ImportDocblockNames
{
    /** @var array<string, Node> */
    private array $nodes = [];

    /**
     * @param Folder $folder
     * @param \OCP\Share\IManager $manager
     * @param \Psr\Log\LoggerInterface $logger
     * @return list<File>
     */
    public function foo($folder, $manager, $logger): array
    {
        return [];
    }
}

?>
