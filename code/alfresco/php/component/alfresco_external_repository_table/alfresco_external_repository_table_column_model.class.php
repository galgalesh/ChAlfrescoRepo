<?php
namespace common\extensions\external_repository_manager\implementation\alfresco;

use common\libraries\ObjectTableColumn;
use common\libraries\ObjectTableColumnModel;
use common\libraries\Path;
use common\libraries\StaticTableColumn;
use common\libraries\Translation;


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
    }
    
    private static function get_default_columns()
    {
        $columns = array();
        $columns[] = new ObjectTableColumn(AlfrescoExternalRepositoryObject::PROPERTY_TITLE, true);
        $columns[] = new ObjectTableColumn(AlfrescoExternalRepositoryObject::PROPERTY_CREATED, true);
        $columns[] = new ObjectTableColumn(AlfrescoExternalRepositoryObject::PROPERTY_MODIFIED, true);
        $columns[] = self::get_modification_column();
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
            self::$modification_column = new StaticTableColumn(Translation::get('Actions'));
        }
        return self::$modification_column;
    }
}
?>