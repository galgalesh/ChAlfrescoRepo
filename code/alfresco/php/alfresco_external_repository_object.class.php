<?php
namespace common\extensions\external_repository_manager\implementation\alfresco;

use common\extensions\external_repository_manager\ExternalRepositoryObject;

use common\libraries\Translation;
use common\libraries\Utilities;
use common\libraries\Theme;
use common\libraries\ToolbarItem;

class AlfrescoExternalRepositoryObject extends ExternalRepositoryObject
{
    const OBJECT_TYPE = 'alfresco';
    const FILE_NAME = 'Bestandsnaam';
    
    private $author;

    static function get_object_type()
    {
        return self :: OBJECT_TYPE;
    } 
    
    function get_author() {
        return $this->author;
    }
    
    function set_author($author) {
        $this->author = $author;
    }
    
    function get_export_types()
    {
        switch ($this->get_type())
        {
            case 'document' :
                return array('pdf', 'odt', 'doc');
                break;
            case 'presentation' :
                return array('pdf', 'ppt', 'swf');
                break;
            case 'spreadsheet' :
                return array('pdf', 'ods', 'xls');
                break;
            case 'pdf' :
                return array('pdf');
                break;
        }
    }

}
?>