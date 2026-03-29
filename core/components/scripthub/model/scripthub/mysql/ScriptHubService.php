<?php
namespace scripthub\mysql;

use xPDO\xPDO;

class ScriptHubService extends \scripthub\ScriptHubService
{

    public static $metaMap = array (
        'package' => 'scripthub',
        'version' => '3.0',
        'table' => 'scripthub_services',
        'extends' => 'xPDOSimpleObject',
        'tableMeta' => 
        array (
            'engine' => 'InnoDB',
        ),
        'fields' => 
        array (
            'service_key' => '',
            'enabled' => 0,
            'config' => NULL,
            'position' => 0,
            'created_at' => NULL,
            'updated_at' => NULL,
        ),
        'fieldMeta' => 
        array (
            'service_key' => 
            array (
                'dbtype' => 'varchar',
                'precision' => '100',
                'phptype' => 'string',
                'null' => false,
                'default' => '',
            ),
            'enabled' => 
            array (
                'dbtype' => 'tinyint',
                'precision' => '1',
                'attributes' => 'unsigned',
                'phptype' => 'boolean',
                'null' => false,
                'default' => 0,
            ),
            'config' => 
            array (
                'dbtype' => 'mediumtext',
                'phptype' => 'string',
                'null' => true,
            ),
            'position' => 
            array (
                'dbtype' => 'int',
                'precision' => '10',
                'attributes' => 'unsigned',
                'phptype' => 'integer',
                'null' => false,
                'default' => 0,
            ),
            'created_at' => 
            array (
                'dbtype' => 'datetime',
                'phptype' => 'datetime',
                'null' => false,
            ),
            'updated_at' => 
            array (
                'dbtype' => 'datetime',
                'phptype' => 'datetime',
                'null' => false,
            ),
        ),
        'indexes' => 
        array (
            'service_key' => 
            array (
                'alias' => 'service_key',
                'primary' => false,
                'unique' => true,
                'type' => 'BTREE',
                'columns' => 
                array (
                    'service_key' => 
                    array (
                        'length' => '',
                        'collation' => 'A',
                        'null' => false,
                    ),
                ),
            ),
            'enabled' => 
            array (
                'alias' => 'enabled',
                'primary' => false,
                'unique' => false,
                'type' => 'BTREE',
                'columns' => 
                array (
                    'enabled' => 
                    array (
                        'length' => '',
                        'collation' => 'A',
                        'null' => false,
                    ),
                ),
            ),
        ),
    );

}
