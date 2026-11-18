<?php

namespace Nextcloud\Rector\Test\Rector\OcHelperToOcpUtilRector\Fixture;

class SomeClass
{
    public function foo()
    {
        $size = \OC_Helper::humanFileSize(1223);
        $size2 = \OC_Helper::computerFileSize('12 KB');
        $path = \OC_Helper::buildNotExistingFileName('/', 'a.txt');
        $other = \OC_Util::humanFileSize(1223);
    }
}

?>
-----
<?php

namespace Nextcloud\Rector\Test\Rector\OcHelperToOcpUtilRector\Fixture;

class SomeClass
{
    public function foo()
    {
        $size = \OCP\Util::humanFileSize(1223);
        $size2 = \OCP\Util::computerFileSize('12 KB');
        $path = \OC_Helper::buildNotExistingFileName('/', 'a.txt');
        $other = \OC_Util::humanFileSize(1223);
    }
}

?>
