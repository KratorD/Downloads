<?php
/**
 * Downloads
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 */

class Downloads_Model_Categories extends Doctrine_Record
{

    public function setTableDefinition()
    {
        $this->setTableName('downloads_categories');
        $this->hasColumn('pn_cid as cid', 'integer', 4, array(
            'type' => 'integer',
            'length' => 4,
            'fixed' => false,
            'unsigned' => false,
            'primary' => true,
            'autoincrement' => true,
        ));
        $this->hasColumn('pn_pid as pid', 'integer', 4, array(
            'type' => 'integer',
            'length' => 4,
            'fixed' => false,
            'unsigned' => false,
            'primary' => false,
            'default' => '0',
            'notnull' => true,
            'autoincrement' => false,
        ));
        $this->hasColumn('pn_title as title', 'string', 100, array(
            'type' => 'string',
            'length' => 100,
            'fixed' => false,
            'unsigned' => false,
            'primary' => false,
            'default' => '0',
            'notnull' => false,
            'autoincrement' => false,
        ));
        $this->hasColumn('pn_description as description', 'string', 254, array(
            'type' => 'string',
            'length' => 254,
            'fixed' => false,
            'unsigned' => false,
            'primary' => false,
            'default' => '',
            'notnull' => true,
            'autoincrement' => false,
        ));
    }

    public function setUp()
    {
        $this->hasMany('Downloads_Model_Download as download', array(
            'local' => 'cid',
            'foreign' => 'cid'
        ));
    }

}