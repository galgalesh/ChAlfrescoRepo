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

use SimplePie;
require_once Path :: get_plugin_path(__NAMESPACE__) . 'SimplePie/autoloader.php';

/**
 * @author Merlijn Sebrechts
 *
 */

class AlfrescoExternalRepositoryManagerConnector extends ExternalRepositoryManagerConnector
{   
    private $username;
    private $password;
    private $authorization_string;
    
    
    function __construct($external_repository_instance)
    {
        parent::__construct($external_repository_instance);   
        $this->username = ExternalSetting :: get('username', $this->get_external_repository_instance_id());
        $this->password = ExternalSetting :: get('password', $this->get_external_repository_instance_id());
        $this->authorization_string = base64_encode($this->username . ':' . $this->password);
    }	
    
    function retrieve_external_repository_object($id) {
        // TODO
    }

    function retrieve_sites($current_site, $current_folder)
    {
        
        // Array which holds the sites found for the user
        $sub_sites = array();
        
        // Get sites
        // 

        // Encode username:password               
        $authorization_string = base64_encode($this->username . ':' . $this->password);
        
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
            'Authorization: Basic ' . $authorization_string,
            'WWW-Authenticate: Basic realm="Alfresco"',
            'Host: intern.vvs.ac'));     
        
        // Execute
        $result = curl_exec($ch);
       
        
        // 200 All ok
        // 302 Found but something wrong    
        // 401 Unauthorized
        // 405 Method Not Allowed
        $http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        if ($http_status == 302 || $http_status == 401 || $http_status == 402) {
            
        }
        
        else if ($http_status == 200) {

            // Decode result
            $decode = json_decode($result);   
                       
            // We will store every site that the user has access to here
            $sites = array();
        
            // Iterate over all the decoded JSON
            foreach ($decode as $site) {
                
                // Array which holds the site details
                $sub_site = array();       
                
                // Title
                $sub_site['title'] = $site->shortName;
                
                // URL (later gets modified)
                $sub_site['url'] = $site->shortName;
                
                // Class (icon)
                $sub_site['class'] = 'external_instance';
                
                // Subfolders
                $sub_folders = $this->get_folder_tree(end(explode("/", $site->node)));            
                
                $sub_site['sub'] = $sub_folders;
                
                // Put the site in the sites array
                $sites[] = $sub_site;
            }
        }
        // Close handle
        curl_close($ch);
        
        return $sites;
    }

    function get_folder_tree($id)
    {
             
        // curl init webapi
        $ch = curl_init('https://intern.vvs.ac/alfresco/service/api/node/workspace/SpacesStore/' . $id . '/tree');

        //
        // WARNING: This would prevent curl from detecting a 'man in the middle' attack
        // FIX: Get certificate from VVS
        //
        curl_setopt ($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, 0); 
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        // Set headers
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(  
            'Authorization: Basic ' . $this->authorization_string,
            'WWW-Authenticate: Basic realm="Alfresco"',
            'Host: intern.vvs.ac'));     
        
        // Execute
        $data = curl_exec($ch);

        
        curl_close($ch);
        
        
        
        $xml = simplexml_load_string($data);
        

        $nodes = array();
        
        foreach ($xml->entry as $entry) {
            
            $id = end(explode(':', $entry->id));
            
            $node = array();
            
            $node['title'] = ($entry->title[0] == 'documentLibrary') ? Translation :: get('DocumentLibrary') : (string)$entry->title[0];
            $node['url'] = $id;
            $node['class'] = 'category';
            //$node['sub'] = $this->get_folder_tree($id);
                       
            $nodes[] = $node;       
        }
   
        
        //var_dump($nodes);
        return (count($nodes) > 0) ? $nodes : null;
    }
    
    /**
     * @param mixed $condition
     * @param ObjectTableOrder $order_property
     * @param int $offset
     * @param int $count
     */
    function retrieve_external_repository_objects($condition, $order_property, $offset, $count) {
        
        $uuid = Request :: get(AlfrescoExternalRepositoryManager::PARAM_UUID);

        // curl init webapi
        $ch = curl_init('https://intern.vvs.ac/alfresco/service/api/node/workspace/SpacesStore/' . $uuid . '/children?types=documents');

        //
        // WARNING: This would prevent curl from detecting a 'man in the middle' attack
        // FIX: Get certificate from VVS
        //
        curl_setopt ($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, 0); 
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        // Set headers
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(  
            'Authorization: Basic ' . $this->authorization_string,
            'WWW-Authenticate: Basic realm="Alfresco"',
            'Host: intern.vvs.ac'));     
        
        // Execute
        $data = curl_exec($ch);

        
        curl_close($ch);
        
        $xml = simplexml_load_string($data);
        
        foreach ($xml->entry as $entry) {
            var_dump($entry->title);
        }
        
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
    
    	/**
        * Dumps the output of a variable in a more readable manner
        *
        * @return string
        * @param bool[optional] $echo
        * @param bool[optional] $exit
        */
        public static function dump($var, $echo = true, $exit = true)
        {

            // fetch var
            ob_start();
            var_dump($var);
            $output = ob_get_clean();

            // neaten the output
            $output = preg_replace("/\]\=\>\n(\s+)/m", "] => ", $output);

            // print
            if($echo) echo '<pre>'. htmlentities($output, ENT_QUOTES) .'</pre>';

            // return
            if(!$exit) return $output;
            exit;

        }
        
        public function outputToFile($data) {
        // Open another output buffering context
          ob_start();

          print_r($data);

          $_output = ob_get_contents();
          // Destroy the context so that Laravel's none the wiser
          ob_end_clean();

          $_fp = fopen("C:/tmp/myfile2.txt", "w");
          fwrite($_fp, $_output);
          fclose($_fp);
          // Remove awkward traces
          unset($_fp, $_output);
        }
	
		
	
}
?>