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
    const EXTRA = 'Extra';
    
    private $filename;
    private $type;
    
    

    static function get_object_type()
    {
        return self :: OBJECT_TYPE;
    }
    
    function get_filename() 
    {
        return $this->filename;
    }
    
    function set_filename($filename) 
    {
        $this->filename = $filename;
    }
    
    function get_type() 
    {
        return $this->type;
    }
    
    function set_type($type) 
    {
        $this->type = $type;
    }
    
    
}
?>