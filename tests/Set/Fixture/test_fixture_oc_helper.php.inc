<?php

namespace Nextcloud\Rector\Test\Set\Fixture;

class SomeClass
{
    public function foo()
    {
        $size = \OC_Helper::humanFileSize(1223);
        $size2 = \OC_Helper::computerFileSize('12 KB');
        $size3 = \OC_Helper::humanFileSize($size2);
        $size2 = \OC_Helper::computerFileSize($size);
    }
}

?>
-----
<?php

namespace Nextcloud\Rector\Test\Set\Fixture;

class SomeClass
{
    public function foo()
    {
        $size = \OCP\Util::humanFileSize(1223);
        $size2 = \OCP\Util::computerFileSize('12 KB');
        $size3 = \OCP\Util::humanFileSize($size2);
        $size2 = \OCP\Util::computerFileSize($size);
    }
}

?>
