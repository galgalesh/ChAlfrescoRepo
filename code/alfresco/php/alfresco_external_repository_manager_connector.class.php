<?php
namespace common\extensions\external_repository_manager\implementation\alfresco;

use common\libraries\Path;
use common\libraries\Request;
use common\libraries\Redirect;
use common\libraries\ActionBarSearchForm;
use common\libraries\ArrayResultSet;
use common\libraries\Session;
use common\libraries\Translation;

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
    private $password;
    private $username;
    
    function __construct($external_repository_instance)
    {
        parent::__construct($external_repository_instance);       
    }	
    
    function login() {
    
    }
    
    function retrieve_external_repository_object($id) {
    	
    }

    function retrieve_sites($siteXYZ)
    {
        // Array which holds the sites found for the user
        $sub_sites = array();
        
        // Get sites
        // Encode username:password
        
        $this->username = "username";
        $this->username = "password";
        
        $encoded = base64_encode($this->username . ':' . $this->password);
        // curl init webapi
        $ch = curl_init('https://intern.vvs.ac/alfresco/service/api/people/' . $this->username . '/sites'); 
        
        //
        // WARNING: This would prevent curl from detecting a 'man in the middle' attack
        // FIX: Get certificate from VVS
        //
        curl_setopt ($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, 0); 
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        // Set headers
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(  
            'Authorization: Basic ' . $encoded,
            'WWW-Authenticate: Basic realm="Alfresco"',
            'Host: intern.vvs.ac'));     
        
        // Execute
        $result = curl_exec($ch);
       
        // 405 NOT ALLOWED
        // 200 ALL OWKEY
        // 302 AUTHORIZATION ERRUR      
        $http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        if ($http_status == 403 || $http_status == 302) {
            
        }
        
        else if ($http_status == 200) {

            // Decode result
            $decode = json_decode($result);   
                       
            // We will store every site that the user has access to here
            $sub_sites = array();
        
            // Iterate over all the decoded JSON
            foreach ($decode as $site) {
                $sub_site = array();
                $sub_site['title'] = $site->shortName;
                $sub_site['url'] = $site->shortName;
                $sub_site['class'] = 'external_instance';
                $sub_sites[] = $sub_site;
                
            }
        }
        // Close handle
        curl_close($ch);
        
        return $sub_sites;
    }

    function get_folder_tree($index, $folders, $folder_url)
    {
        $items = array();
        foreach ($folders[$index] as $child)
        {
            $sub_site = array();
            $sub_site['title'] = $child->getTitle()->getText();
            $sub_site['url'] = str_replace('__PLACEHOLDER__', $child->getResourceId()->getId(), $folder_url);
            $sub_site['class'] = 'category';

            $children = $this->get_folder_tree($child->getResourceId()->getId(), $folders, $folder_url);

            if (count($children) > 0)
            {
                $sub_site['sub'] = $children;
            }

            $items[] = $sub_site;
        }
        return $items;
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
    
    function validate_settings() {
        
    }
	
		
	
}
?>