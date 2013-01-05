<?php
namespace common\extensions\external_repository_manager\implementation\alfresco;

use common\libraries\Path;
use common\libraries\Request;
use common\libraries\Redirect;
use common\libraries\ActionBarSearchForm;
use common\libraries\ArrayResultSet;
use common\libraries\Session;

use repository\ExternalUserSetting;
use repository\ExternalSetting;
use repository\RepositoryDataManager;

use common\extensions\external_repository_manager\ExternalRepositoryManagerConnector;
use common\extensions\external_repository_manager\ExternalRepositoryObject;


require_once dirname(__FILE__) . '/alfresco_external_repository_object.class.php';

/**
 * @author Merlijn Sebrechts
 *
 */

class AlfrescoExternalRepositoryManagerConnector extends ExternalRepositoryManagerConnector
{
	
    /**
     * @var string
     */
    private $username;

    /**
     * @var string
     */
    private $password;

	
	function __construct($external_repository_instance)
    {
        parent :: __construct($external_repository_instance);

        $this->username = ExternalSetting :: get('username', $this->get_external_repository_instance_id());
        $this->password = ExternalSetting :: get('password', $this->get_external_repository_instance_id());
		
		
    }
	
	
    function retrieve_external_repository_object($id) {
    	
    }



    /**
     * @param mixed $condition
     * @param ObjectTableOrder $order_property
     * @param int $offset
     * @param int $count
     */
    function retrieve_external_repository_objects($condition, $order_property, $offset, $count) {
        $arr = array();
    	// OPHALEN VAN BESTANDEN
        return new ArrayResultSet($arr);
    }

    /**
     * @param mixed $condition
     */
    function count_external_repository_objects($condition) {
    	
    }
	
	 function delete_external_repository_object($id) {
	 	
	 }

    /**
     * @param string $id
     */
    function export_external_repository_object($id) {
    	
    }

    /**
     * @param string $query
     */
    static function translate_search_query($query) {
    	
    }
	
		
	
}
?>