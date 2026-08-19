<?php

/*
 * SPDX-FileCopyrightText: 2026 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace Nextcloud\Rector\Test\Rector\OrderBySortDirectionRector\Fixture;

use OCP\IDBConnection;

class SomeClass
{
    public function __construct(IDBConnection $connection)
    {
        $qb = $connection->getQueryBuilder();
        $qb->select('*')
            ->from('pages')
            ->orderBy('name', 'ASC')
            ->addOrderBy('id', 'desc');

        $qb->orderBy('name', order: 'DESC');
        $qb->orderBy('name');
        $qb->orderBy('name', 'notadirection');
    }
}

?>
-----
<?php

/*
 * SPDX-FileCopyrightText: 2026 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace Nextcloud\Rector\Test\Rector\OrderBySortDirectionRector\Fixture;

use OCP\IDBConnection;

class SomeClass
{
    public function __construct(IDBConnection $connection)
    {
        $qb = $connection->getQueryBuilder();
        $qb->select('*')
            ->from('pages')
            ->orderBy('name', \SortDirection::Ascending)
            ->addOrderBy('id', \SortDirection::Descending);

        $qb->orderBy('name', order: \SortDirection::Descending);
        $qb->orderBy('name');
        $qb->orderBy('name', 'notadirection');
    }
}

?>
