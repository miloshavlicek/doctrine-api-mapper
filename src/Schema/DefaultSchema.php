<?php

namespace Miloshavlicek\DoctrineApiMapper\Schema;

class DefaultSchema
{

    const LIMIT_KEY = '_limit';

    const OFFSET_KEY = '_offset';

    const PAGE_KEY = '_page';

    const SORT_KEY = '_sort';

    const ORDER_KEY = '_order';

    const FIELDS_KEY = '_fields';

    const FILTER_PREFIX = 'f_';

    const ENTITY_PREFIX = 'e_';

    const ENTITY_REQUEST_ID_KEY = 'id';

    /**
     * @param array $data
     * @return array
     */
    public static function mapOutput(array $data): array
    {
        return $data;
    }

}