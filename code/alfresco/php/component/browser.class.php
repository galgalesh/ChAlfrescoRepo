<?php
namespace common\extensions\external_repository_manager\implementation\alfresco;

use common\extensions\external_repository_manager\ExternalRepositoryComponent;

class AlfrescoExternalRepositoryManagerBrowserComponent extends AlfrescoExternalRepositoryManager
{

    function run()
    {
        ExternalRepositoryComponent :: launch($this);
    }
}
?>