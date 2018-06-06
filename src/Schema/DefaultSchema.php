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

    const FILTER_KEY = '_filter';

    const LANG_KEY = '_lang';

    const FILTER_PREFIX = 'f_';

    const ENTITY_PREFIX = 'e_';

    const ENTITY_REQUEST_ID_KEY = 'id';

    const I_COUNT_KEY = 'i_count';

    const I_PERM_KEY = 'i_perm';

    const I_RESULT_KEY = 'i_res';

    const I_USER_KEY = 'i_user';

    /**
     * @param array $data
     * @return array
     */
    public static function mapOutput(array $data): array
    {
        return $data;
    }

}