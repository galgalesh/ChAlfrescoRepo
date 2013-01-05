<?php
namespace common\extensions\external_repository_manager\implementation\alfresco;

//require_once dirname(__FILE__) . '/external_repository_browser_gallery_table/external_repository_browser_gallery_table.class.php';
require_once dirname(__FILE__) . './alfresco_external_repository_table/alfresco_external_repository_table.class.php';

use common\extensions\external_repository_manager\ExternalRepositoryComponent;


class AlfrescoExternalRepositoryManagerBrowserComponent extends AlfrescoExternalRepositoryManager
{

    function run()
    {
    	
		ExternalRepositoryComponent::launch($this);
    }
}
?>