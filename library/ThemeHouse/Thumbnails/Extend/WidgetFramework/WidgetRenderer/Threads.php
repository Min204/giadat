<?php

/**
 * @see WidgetFramework_WidgetRenderer_Threads
 */
class ThemeHouse_Thumbnails_Extend_WidgetFramework_WidgetRenderer_Threads extends XFCP_ThemeHouse_Thumbnails_Extend_WidgetFramework_WidgetRenderer_Threads
{
    protected function _getThreads($widget, $positionCode, $params, $renderTemplateObject)
    {
        $threads = parent::_getThreads($widget, $positionCode, $params, $renderTemplateObject);

        /* @var $thumbnailsModel ThemeHouse_Thumbnails_Model_Thumbnails */
        $thumbnailsModel = XenForo_Model::create('ThemeHouse_Thumbnails_Model_Thumbnails');

        return $thumbnailsModel->addThumbsToSearchThreads($threads);
    }
}
