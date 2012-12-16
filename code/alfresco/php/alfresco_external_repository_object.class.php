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

    static function get_object_type()
    {
        return self :: OBJECT_TYPE;
    }
}
?>