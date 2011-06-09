<?php

class Downloads_Api_Search extends Zikula_AbstractApi
{

    /**
     * Search plugin info
     */
    public function info()
    {
        return array('title' => 'Downloads',
            'functions' => array('downloads' => 'search'));
    }

    /**
     * Search form component
     */
    public function options($args)
    {
        if (SecurityUtil::checkPermission('Downloads::', '::', ACCESS_READ)) {
            $render = Zikula_View::getInstance('Downloads');
            $render->assign('active', !isset($args['active']) || isset($args['active']['Downloads']));
            return $render->fetch('search/options.tpl');
        }

        return '';
    }

    /**
     * Search plugin main function
     */
    public function search($args)
    {
        ModUtil::dbInfoLoad('Search');

        $sessionId = session_id();

        $whereArray = $this->constructDoctrineWhere($args, array('title', 'description'));

        $q = Doctrine_Core::getTable('Downloads_Model_Download')->createQuery();
        foreach ($whereArray as $where) {
            $q->$where['method']("{$where['field']}", $where['value']);
        }
        $results = $q->execute()
                    ->toArray();

        foreach ($results as $result) {
            $record = array(
                'title' => $result['title'],
                'text' => '',
                'extra' => serialize(array('lid' => $result['lid'])),
                'created' => $result['date'],
                'module' => 'Downloads',
                'session' => $sessionId
            );

            if (!DBUtil::insertObject($record, 'search_result')) {
                return LogUtil::registerError($this->__('Error! Could not save the search results.'));
            }
        }

        return true;
    }

    /**
     * Do last minute access checking and assign URL to items
     *
     * Access checking is ignored since access check has
     * already been done. But we do add a URL to the found item
     */
    public function search_check($args)
    {
        $datarow = &$args['datarow'];
        $extra = unserialize($datarow['extra']);

        $datarow['url'] = ModUtil::url('downloads', 'user', 'display', array('lid' => $extra['lid']));

        return true;
    }

    /**
     * Contruct array of values suitable for Doctrine processing
     */
    public static function constructDoctrineWhere($args, $fields)
    {
        $where = array();

        if (!isset($args) || empty($args) || !isset($fields) || empty($fields)) {
            return $where;
        }

        if (!empty($args['q'])) {
            $q = DataUtil::formatForStore($args['q']);
            $q = str_replace('%', '\\%', $q);  // Don't allow user input % as wildcard
            if ($args['searchtype'] !== 'EXACT') {
                $searchwords = Search_Api_User::split_query($q);
                $method = $args['searchtype'] == 'AND' ? 'andWhere' : 'orWhere';
                //$method = 'orWhere';
            } else {
                $searchwords = array("%{$q}%");
                $method = 'where';
            }
            foreach ($searchwords as $word) {
                $fieldstrings = array();
                $valueArray = array();
                foreach ($fields as $field) {
                    $fieldstrings[] = "$field LIKE ?";
                    $valueArray[] = $word;
                }
                $fieldstring = '(' . implode(' OR ', $fieldstrings) . ')';
                $where[] = array(
                    'method' => $method,
                    'field' => $fieldstring,
                    'value' => $valueArray);
            }
        }

        return $where;
    }

}