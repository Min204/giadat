<?php

class ThemeHouse_Thumbnails_Listener_LoadClass extends ThemeHouse_Listener_LoadClass
{
    protected function _getExtendedClasses()
    {
        return array(
            'ThemeHouse_Thumbnails' => array(
                'controller' => array(
                    'ThemeHouse_SocialGroups_ControllerAdmin_SocialCategory',
                    'XenForo_ControllerAdmin_Forum',
                    'XenForo_ControllerPublic_Index',
                    'XenForo_ControllerPublic_Forum',
                    'XenForo_ControllerPublic_Category',
                    'ThemeHouse_Library_ControllerPublic_Library',
                    'XenForo_ControllerPublic_Thread',
                    'ThemeHouse_SocialGroups_ControllerPublic_SocialForum',
                    'XenForo_ControllerPublic_FindNew',
                    'XenForo_ControllerPublic_Watched',
                    'XenForo_ControllerAdmin_Option',
                    'XenForo_ControllerAdmin_Log',
                ),
                'datawriter' => array(
                    'XenForo_DataWriter_Discussion_Thread',
                    'XenForo_DataWriter_Forum',
                    'XenForo_DataWriter_Option',
                    'XenForo_DataWriter_DiscussionMessage_Post',
                ),
                'installer_th' => array(
                    'ThemeHouse_SocialGroups_Install',
                    'ThemeHouse_SocialGroups_Install_Controller',
                ),
                'model' => array(
                    'XenForo_Model_Attachment',
                    'XenForo_Model_Node',
                    'ThemeHouse_NoForo_Model_NoForo',
                ),
                'search_data' => array(
                    'XenForo_Search_DataHandler_Thread',
                    'ThemeHouse_Library_Search_DataHandler_Article',
                ),
                'view' => array(
                    'XenForo_ViewPublic_Thread_Save_ThreadListItem',
                ),
                'proxyoutput' => array(
                    'XenForo_ProxyOutput',
                ),
                'widget_renderer' => array(
                    'WidgetFramework_WidgetRenderer_Threads',
                ),
            ),
        );
    }

    public static function loadClassController($class, array &$extend)
    {
        $loadClassController = new self($class, $extend, 'controller');
        $extend = $loadClassController->run();
    }

    public static function loadClassDataWriter($class, array &$extend)
    {
        $loadClassDataWriter = new self($class, $extend, 'datawriter');
        $extend = $loadClassDataWriter->run();
    }

    public static function loadClassInstallerThemeHouse($class, array &$extend)
    {
        $loadClassInstallerThemeHouse = new self($class, $extend, 'installer_th');
        $extend = $loadClassInstallerThemeHouse->run();
    }

    public static function loadClassModel($class, array &$extend)
    {
        $loadClassModel = new self($class, $extend, 'model');
        $extend = $loadClassModel->run();
    }

    public static function loadClassSearchData($class, array &$extend)
    {
        $loadClassSearchData = new self($class, $extend, 'search_data');
        $extend = $loadClassSearchData->run();
    }

    public static function loadClassView($class, array &$extend)
    {
        $loadClassView = new self($class, $extend, 'view');
        $extend = $loadClassView->run();
    }

    public static function loadClassProxyoutput($class, array &$extend)
    {
        $loadClassProxyoutput = new self($class, $extend, 'proxyoutput');
        $extend = $loadClassProxyoutput->run();
    }

    public static function loadClassWidgetRenderer($class, array &$extend)
    {
        $loadClassWidgetRenderer = new self($class, $extend, 'widget_renderer');
        $extend = $loadClassWidgetRenderer->run();
    }
}
