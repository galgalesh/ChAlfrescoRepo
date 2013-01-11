<?php
namespace common\extensions\external_repository_manager\implementation\alfresco;

use common\extensions\external_repository_manager\ExternalRepositoryManager;
use common\extensions\external_repository_manager\ExternalRepositoryObject;
use common\libraries\Application;
use common\libraries\OrCondition;
use common\libraries\Path;
use common\libraries\PatternMatchCondition;
use common\libraries\Theme;
use common\libraries\ToolbarItem;
use common\libraries\Translation;
use common\libraries\Utilities;
use repository\content_object\document\Document;
use repository\ExternalSetting;

require_once dirname(__FILE__) . '/alfresco_external_repository_manager_connector.class.php';

/**
 * @author Merlijn Sebrechts
 */
class AlfrescoExternalRepositoryManager extends ExternalRepositoryManager
{
    const REPOSITORY_TYPE = 'alfresco';

    const PARAM_UUID = 'uuid';
    const PARAM_SITE = 'site';
    const PARAM_EXPORT_FORMAT = 'export_format';

    /**
     * @param Application $application
     */
    function __construct($external_repository, $application)
    {
        parent :: __construct($external_repository, $application);
        //$this->set_parameter(self :: PARAM_FEED_TYPE, Request :: get(self :: PARAM_FEED_TYPE));
    }

    /* (non-PHPdoc)
     * @see application/common/external_repository_manager/ExternalRepositoryManager#get_application_component_path()
     */
    function get_application_component_path()
    {
        return Path :: get_common_extensions_path() . 'external_repository_manager/implementation/alfresco/php/component/';
    }

    /* (non-PHPdoc)
     * @see application/common/external_repository_manager/ExternalRepositoryManager#validate_settings()
     */
    function validate_settings($external_repository)
    {
        $settings = array('alfresco_url', 'username', 'password');

        foreach ($settings as $variable)
        {
                $value = ExternalSetting :: get($variable, $external_repository->get_id());
                if (!$value)
                {
                        return false;
                }
        }
        return true;
    }

    /* (non-PHPdoc)
     * @see application/common/external_repository_manager/ExternalRepositoryManager#support_sorting_direction()
     */
    function support_sorting_direction()
    {
        return true;
    }

    /**
     * @param ExternalRepositoryObject $object
     * @return string
     */
    function get_external_repository_object_viewing_url(ExternalRepositoryObject $object)
    {
        $parameters = array();
        $parameters[self :: PARAM_EXTERNAL_REPOSITORY_MANAGER_ACTION] = self :: ACTION_VIEW_EXTERNAL_REPOSITORY;
        $parameters[self :: PARAM_EXTERNAL_REPOSITORY_ID] = $object->get_id();

        return $this->get_url($parameters);
    }

    /* (non-PHPdoc)
     * @see application/common/external_repository_manager/ExternalRepositoryManager#get_menu_items()
     */
    function get_menu_items()
    {
        $menu_items = array();

        $site_root = array();
        $site_root['title'] = Translation :: get('AllSites');
        $site_root['url'] = $this->get_url(array(self::PARAM_UUID => "null"));
        $site_root['class'] = 'external_instance';
               
        $sub_sites = $this->get_external_repository_manager_connector()->retrieve_sites();
        
        $main = $this->array_recursive_change_url($sub_sites);

        $site_root['sub'] = $main;
        $menu_items[] = $site_root;
        return $menu_items;
    }

    function array_recursive_change_url($array) {   
        $main = $array;

        // has subs
        if (array_key_exists('sub', $main)) 
        {
            
            if (!empty($main['sub'])) {
                $subs = array();

                foreach($main['sub'] as $sub) 
                {
                    $subs[] = $this->array_recursive_change_url($sub);
                }

                $main['sub'] = $subs;
            }
        }
        
        // has sites
        elseif (!array_key_exists('title', $main))
        {
            $subs = array();
            
            foreach($main as $sub) 
            {
                $subs[] = $this->array_recursive_change_url($sub);
            }
            
            $main = $subs;
        }
        
        if (array_key_exists('url', $main)) {
            
            if (array_key_exists('url', $main)) {
                
            $main['url'] = $this->get_url(array(
                self::PARAM_UUID => $main['url'][0], 
                self::PARAM_SITE => $main['url'][1]));
            }
        }
        
        return $main;
    }
    
    /* (non-PHPdoc)
     * @see application/common/external_repository_manager/ExternalRepositoryManager#is_ready_to_be_used()
     */
    function is_ready_to_be_used()
    {
        return false;
    }

    /* (non-PHPdoc)
     * @see application/common/external_repository_manager/ExternalRepositoryManager#get_content_object_type_conditions()
     */
    function get_content_object_type_conditions()
    {
        $image_types = Document :: get_image_types();
        $image_conditions = array();
        foreach ($image_types as $image_type)
        {
            $image_conditions[] = new PatternMatchCondition(Document :: PROPERTY_FILENAME, '*.' . $image_type, Document :: get_type_name());
        }

        return new OrCondition($image_conditions);
    }
    
    function get_external_repository_object_actions(AlfrescoExternalRepositoryObject $object)
    {
        $actions = parent :: get_external_repository_object_actions($object);
        if (in_array(ExternalRepositoryManager :: ACTION_IMPORT_EXTERNAL_REPOSITORY, array_keys($actions)))
        {
            unset($actions[ExternalRepositoryManager :: ACTION_IMPORT_EXTERNAL_REPOSITORY]);
            $export_types = $object->get_export_types();

            foreach ($export_types as $export_type)
            {
                $actions[$export_type] = new ToolbarItem(Translation :: get('Import' . Utilities :: underscores_to_camelcase($export_type)), Theme :: get_image_path() . 'import/' . $export_type . '.png', $this->get_url(array(self :: PARAM_EXTERNAL_REPOSITORY_MANAGER_ACTION => self :: ACTION_IMPORT_EXTERNAL_REPOSITORY, self :: PARAM_EXTERNAL_REPOSITORY_ID => $object->get_id(), self :: PARAM_EXPORT_FORMAT => $export_type)), ToolbarItem :: DISPLAY_ICON);
            }
        }

        return $actions;
    }

    /**
     * @return string
     */
    function get_repository_type()
    {
        return self :: REPOSITORY_TYPE;
    }

    /**
     * Helper function for the SubManager class,
     * pending access to class constants via variables in PHP 5.3
     * e.g. $name = $class :: DEFAULT_ACTION
     *
     * DO NOT USE IN THIS SUBMANAGER'S CONTEXT
     * Instead use:
     * - self :: DEFAULT_ACTION in the context of this class
     * - YourSubManager :: DEFAULT_ACTION in all other application classes
     */
    static function get_default_action()
    {
        return self :: DEFAULT_ACTION;
    }

    /**
     * Helper function for the SubManager class,
     * pending access to class constants via variables in PHP 5.3
     * e.g. $name = $class :: PARAM_ACTION
     *
     * DO NOT USE IN THIS SUBMANAGER'S CONTEXT
     * Instead use:
     * - self :: PARAM_ACTION in the context of this class
     * - YourSubManager :: PARAM_ACTION in all other application classes
     */
    static function get_action_parameter()
    {
        return self :: PARAM_EXTERNAL_REPOSITORY_MANAGER_ACTION;
    }
	
	
}
?>