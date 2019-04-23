<?php

namespace Solspace\ExpressForms\events\export;

use yii\base\Event;
use yii\db\Query;

class BuildExportQueryEvent extends Event
{
    /** @var Query */
    private $query;

    /**
     * @param Query $query
     */
    public function __construct(Query $query)
    {
        $this->query = $query;

        parent::__construct();
    }

    /**
     * @return Query
     */
    public function getQuery(): Query
    {
        return $this->query;
    }
}
