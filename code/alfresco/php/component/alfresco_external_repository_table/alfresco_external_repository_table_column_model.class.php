<?php
namespace common\extensions\external_repository_manager\implementation\alfresco;

use common\libraries\Path;
use common\libraries\ObjectTableColumnModel;
use common\libraries\ObjectTableColumn;
use common\libraries\StaticTableColumn;

use common\extensions\external_repository_manager\ExternalRepositoryObject;


require_once Path::get_common_libraries_class_path() . '/html/table/object_table/object_table_column_model.class.php';

/**
 * Table to display a set of matterhorn external repository objects.
 */
//require_once dirname(__file__) . './alfresco_external_repository_table_cell_renderer.class.php';
//require_once dirname(__file__) . './alfresco_external_repository_table_data_provider.class.php';
//require_once dirname(__file__) . './alfresco_external_repository_table_column_model.class.php';

class AlfrescoExternalRepositoryTableColumnModel extends ObjectTableColumnModel
{
    const DEFAULT_NAME = 'alfresco_external_repository_table_column_model';
    /**
     * The tables modification column
     */
    private static $modification_column;
    
    /**
     * Constructor
     * @see ContentObjectTable::ContentObjectTable()
     */
    function __construct()
    {
        parent::__construct(self::get_default_columns(), 2, SORT_DESC);
        //$this->set_default_order_column(1);
        $this->add_column(self::get_modification_column());
    }
    
    private static function get_default_columns()
    {
        $columns = array();
       // $columns[] = new ObjectTableColumn(ExternalRepositoryObject::PROPERTY_TYPE, false);
        //$columns[] = new ObjectTableColumn('PROPERTY_TITLE', true);
       // $columns[] = new ObjectTableColumn(ExternalRepositoryObject::PROPERTY_DESCRIPTION);
       // $columns[] = new ObjectTableColumn('PROPERTY_CREATED', true);
        $columns[] = new ObjectTableColumn('Bestandsnaam', true);
        //$columns[] = new ObjectTableColumn(ExternalRepositoryObject::PROPERTY_DESCRIPTION, false);
        return $columns;
    }

    /**
     * Gets the modification column
     * @return ContentObjectTableColumn
     */
    static function get_modification_column()
    {
        if (! isset(self::$modification_column))
        {
            self::$modification_column = new StaticTableColumn('');
        }
        return self::$modification_column;
    }
}
?>