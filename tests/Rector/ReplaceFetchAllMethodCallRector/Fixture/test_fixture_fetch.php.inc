<?php

/*
 * SPDX-FileCopyrightText: 2024 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCP\AppFramework;

use OCP\IDBConnection;
use PDO;

class SomeClass
{
    public function __construct(IDBConnection $connection) {
        $qb = $connection->getQueryBuilder();
        $result = $qb->select('*')
            ->from('pages')
            ->executeQuery();

        $result->fetchAll();
        $result->fetchAll(PDO::FETCH_COLUMN);
        $result->fetchAll(PDO::FETCH_ASSOC);
        $result->fetchAll(PDO::FETCH_NUM);

        $result->fetch(PDO::FETCH_COLUMN);
        $result->fetch(PDO::FETCH_ASSOC);
        $result->fetch();
        $result->fetch(PDO::FETCH_NUM);
    }
}

?>
-----
<?php

/*
 * SPDX-FileCopyrightText: 2024 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCP\AppFramework;

use OCP\IDBConnection;
use PDO;

class SomeClass
{
    public function __construct(IDBConnection $connection) {
        $qb = $connection->getQueryBuilder();
        $result = $qb->select('*')
            ->from('pages')
            ->executeQuery();

        $result->fetchAllAssociative();
        $result->fetchFirstColumn();
        $result->fetchAllAssociative();
        $result->fetchAllNumeric();

        $result->fetchOne();
        $result->fetchAssociative();
        $result->fetchAssociative();
        $result->fetchNumeric();
    }
}

?>
