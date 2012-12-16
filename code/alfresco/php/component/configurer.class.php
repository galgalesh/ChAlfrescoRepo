<?php
namespace common\extensions\external_repository_manager\implementation\alfresco;

use common\extensions\external_repository_manager\ExternalRepositoryComponent;

class AlfrescoExternalRepositoryManagerConfigurerComponent extends AlfrescoExternalRepositoryManager
{

    function run()
    {
        ExternalRepositoryComponent :: launch($this);
    }
}
?>