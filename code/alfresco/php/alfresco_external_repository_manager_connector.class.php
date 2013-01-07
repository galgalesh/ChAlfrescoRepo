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

    // Authentication string
    private $authenticationString;

    // Ticket
    private $ticket;
    
	
    function __construct($external_repository_instance)
    {
        parent::__construct($external_repository_instance);

        // Get user name and password
        $username = ExternalSetting :: get('username', $this->get_external_repository_instance_id());
        $password = ExternalSetting :: get('password', $this->get_external_repository_instance_id());
	
        // Make authentication string
        $this->authenticationString = 'Basic  ' . base64_encode($username . ':' . $password);
        
        // Get ticket
        $data = array("username" => $username, "password" => $password);
        json_encode($data);	

        $ch = curl_init('http://vvs.ac/alfresco/service/api/login');                                                                      
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");                                                                     
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);                                                                  
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);                                                                      
                curl_setopt($ch, CURLOPT_HTTPHEADER, array(                                                                          
                    'Content-Type: application/json',                                                                                
                    'Content-Length: ' . strlen($data))                                                                       
        );                                                                                                                   

        $result = json_decode(curl_exec($ch));
        curl_close($ch);
        
        echo $result;
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
        
        for ($i = 0; $i < 100; $i++) {
            
    	$obj = new AlfrescoExternalRepositoryObject();
        //$obj->
        //$obj->set_title("TESTTITEL");
        $obj->set_title("TESTFILENAME");
        $arr[] = $obj;
        
        }
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