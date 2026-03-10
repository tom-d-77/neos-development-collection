<?php
declare(strict_types=1);

namespace Neos\ContentRepository\LegacyNodeMigration\Helpers;

use Doctrine\DBAL\Connection;

/**
 * @implements \IteratorAggregate<int, array<string, mixed>>
 */
final class NodeDataLoader implements \IteratorAggregate
{
    public function __construct(
        private readonly Connection $connection,
    ) {
    }

    public function getIterator(): \Traversable
    {
        $query = $this->connection->executeQuery('
            SELECT
                *
            FROM
                neos_contentrepository_domain_model_nodedata
            WHERE
                workspace = \'live\'
                AND (movedto IS NULL OR removed=false)
                AND path != \'/\'
            ORDER BY
                -- dimensionshash d7... is {} (the empty dimension).
                -- Because there is a fallback from a dimension value to no dimension value in the old CR (if nothing is found),
                -- we need to ensure that the empty dimensionshash comes LAST.
                -- see NodeDataToEventsProcessor::processNodeData() which handles this special case
                parentpath, sortingindex, path, CASE WHEN dimensionshash=\'d751713988987e9331980363e24189ce\' THEN 1 ELSE 0 END
        ');
        return $query->iterateAssociative();
    }
}


