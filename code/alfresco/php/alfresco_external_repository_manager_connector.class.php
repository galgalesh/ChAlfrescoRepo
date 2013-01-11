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
    private $authorization_string;
    private $alfresco_url;
    
    
    function __construct($external_repository_instance)
    {
        parent::__construct($external_repository_instance);   
        
        // Get authorization string, by encoding username/password from settings
        $this->username = ExternalSetting::get('username', $this->get_external_repository_instance_id());
        $password = ExternalSetting::get('password', $this->get_external_repository_instance_id());
        $this->authorization_string = base64_encode($this->username . ':' . $password);
        
        // Get URL to alfresco, from settings        
        $this->alfresco_url = 'http://' . ExternalSetting::get('alfresco_url', $this->get_external_repository_instance_id());
       
    
        }	
    
    function retrieve_external_repository_object($id) {
        reutrn 
        <content type="application/vnd.openxmlformats-officedocument.presentationml.presentation" src="http://intern.vvs.ac:80/alfresco/service/cmis/s/workspace:SpacesStore/i/4b4c0a79-00fd-4c7d-b581-d48072f0cc80/content.pptx" xmlns="http://www.w3.org/2005/Atom" />
    }

    function retrieve_sites()
    {
   
        // curl init webapi
        $ch = $this->get_curl_handle_sites(); 
        $result = curl_exec($ch);
       
        
        // 200 All ok
        // 302 Found but something wrong    
        // 401 Unauthorized
        // 405 Method Not Allowed
        $http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        echo $http_status;
        
        // Array which holds the sites found for the user
        $sites = array();
        
         // Close handle
        curl_close($ch);
        
        
        if ($http_status == 302 || $http_status == 401 || $http_status == 402) {
            
        }
        
        else if ($http_status == 200) {

            // Decode result
            $decode = json_decode($result);   
                       
            // Iterate over all the decoded JSON
            foreach ($decode as $site) {
                
                $node = end(explode('/', $site->node));
                
                // Array which holds the site details
                $sub_site = array();       
                
                // Title
                $sub_site['title'] = $site->shortName;
                
                // URL (later gets modified)
                $sub_site['url'] = array($node, $site->shortName);
                
                // Class (icon)
                $sub_site['class'] = 'external_instance';
                
                // DocumentLibrary
                $sub_folders = $this->get_site_tree($node, $site->shortName, false);        
                $sub_site['sub'] = $sub_folders;
                
                // Put the site in the sites array
                $sites[] = $sub_site;
            }
        }

        return $sites;
    }

    /*
     * 
     * $recursive: wheter or not this function is called recursively
     */
    function get_site_tree($current_uuid, $current_site_name, $recursive)
    {
        
        $folderIsDocumentLibary = false;
        
        
        // curl init webapi
        $ch = $this->get_curl_handle_tree($current_uuid);
        $data = curl_exec($ch); 
        
        echo curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        
        $xml = simplexml_load_string($data);
        

        
        // Site nodes
        $siteNodes = array();
        
        $subNodes = array();
        foreach ($xml->entry as $entry) {
            $node = array();
            
            $uuid = end(explode(':', $entry->id));
            
            if ($entry->title[0] == 'documentLibrary') {
                $node['title'] = Translation :: get('DocumentLibrary');
                
                if ($uuid == Request::get(AlfrescoExternalRepositoryManager::PARAM_UUID)) {
                    $folderIsDocumentLibary = true;
                }
            }
            
            else {
                $node['title'] = (string)$entry->title[0];
            }
            
            $node['url'] = array($uuid, $current_site_name);
            
            $node['class'] = 'category';
            
            // If site name is same as the one in param, get the folder we're at
            if (Request::get(AlfrescoExternalRepositoryManager::PARAM_SITE) == $current_site_name && !$recursive) {
                $node['sub'] = $this->get_site_tree(Request::get(AlfrescoExternalRepositoryManager::PARAM_UUID), $current_site_name, true);
            }
            
            $subNodes[] = $node;       
        }
        
            
        // Get folder above current folder
        $topNode = array();
            
        // Get additional folders
        if (Request::get(AlfrescoExternalRepositoryManager::PARAM_SITE) == $current_site_name && !$recursive) {
            
            var_dump($folderIsDocumentLibary);
            if (!$folderIsDocumentLibary) {
                
                // Get folder above where we are now 
                foreach ($xml->link as $link) {

                    // Check if current link is up (aka parent)
                    if ($link['rel'] == 'up') {

                        $href_split = explode('/', $link['href']);
                        $node = $href_split[count($href_split) - 2];

                        $ch = $this->get_curl_handle_tree($node);
                        $resultFolderUp = curl_exec($ch);
                        curl_close($ch);
                        
                        $xmlFolderUp = simplexml_load_string($resultFolderUp);

                        var_dump($xmlFolderUp);
                        
                        
                        $topNode['title'] = $xmlFolderUp->title;
                        $topNode['url'] = $node;
                        $topNode['class'] = 'category';

              
                    }
                }
            }
        }
        
        if (!empty($topNode)) {
            $topNode['sub'] = $subNodes;
            $siteNodes[] = $topNode;
        }
        
        else {
            $siteNodes[] = $subNodes;
        }
   
        
        //var_dump($nodes);
        return (count($siteNodes) > 0) ? $siteNodes : null;
    }
    
    function get_curl_handle($url) {
        
        $ch = curl_init($url);

        //
        // WARNING: This would prevent curl from detecting a 'man in the middle' attack
        // FIX: Get certificate from VVS
        // TODO : Ask if user wants HTTPS
        // TODO : Certificate?
        //
        curl_setopt ($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, 0); 
        
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        // Set headers
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(  
            'Authorization: Basic ' . $this->authorization_string,
            'WWW-Authenticate: Basic realm="Alfresco"',
            'Host: intern.vvs.ac'));  
        
        return $ch;
    }
    
    function get_curl_handle_sites() {
        $url = $this->alfresco_url . '/service/api/people/' . $this->username . '/sites';
        return $this->get_curl_handle($url);
    }
    
    function get_curl_handle_tree($uuid) {
        $url = $this->alfresco_url . '/service/api/node/workspace/SpacesStore/' . $uuid . '/tree';
        return $this->get_curl_handle($url);
    }
    
    function get_curl_handle_descendants($uuid) {
        return $this->get_curl_handle($this->alfresco_url . '/service/api/node/workspace/SpacesStore/' . $uuid . '/descendants');
    }
    
    function get_curl_handle_content($uuid) {
        return $this->get_curl_handle('http://intern.vvs.ac:80/alfresco/service/cmis/s/workspace:SpacesStore/i/' . $uuid);
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
        $ch = $this->get_curl_handle_descendants($uuid);

        // Execute
        $data = curl_exec($ch);

        
        curl_close($ch);
        
        $xml = simplexml_load_string($data);
        
        $arr = array();
        
        foreach ($xml->entry as $entry) {

            
            foreach ($entry->link as $link) {
                
                // Check if entry isn't a folder
                if ($link['rel'] == 'describedby') {
                    $describedBy = end(explode('/', $link['href']));
                    
                    // Check if entry isn't a folder
                    if ($describedBy != 'cmis:folder') {
                        $obj = new AlfrescoExternalRepositoryObject();
                        
                        $obj->set_created((string)$entry->published[0]);
                        $obj->set_description((string)$entry->summary[0]);
                        $obj->set_modified((string)$entry->updated[0]);

                        $obj->set_author((string)$entry->author->name[0]);
                        $obj->set_rights($this->determine_rights());
                        // Set title
                        $obj->set_title((string)$entry->title[0]);
                        $obj->set_id((string)$entry->content->src);
                        
                        switch ($entry->content['type']) {
                            case 'application/zip':
                                $obj->set_type('zip');
                                break;
                            case 'application/pdf':
                                $obj->set_type('pdf');
                                break;
                            case 'application/vnd.openxmlformats-officedocument.wordprocessingml.document':
                                $obj->set_type('document');
                                break;
                            case 'application/vnd.openxmlformats-officedocument.presentationml.presentation':
                                $obj->set_type('presentation');
                                break;
                            case 'text/plain':
                                break;
                        }
                        
                        $arr[] = $obj;
                    }
                }
            }
        }

        return new ArrayResultSet($arr);
    }

        function determine_rights()
    {
        $rights = array();
        $rights[ExternalRepositoryObject :: RIGHT_USE] = true;
        $rights[ExternalRepositoryObject :: RIGHT_EDIT] = false;
        $rights[ExternalRepositoryObject :: RIGHT_DELETE] = false;
        $rights[ExternalRepositoryObject :: RIGHT_DOWNLOAD] = true;
        return $rights;
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