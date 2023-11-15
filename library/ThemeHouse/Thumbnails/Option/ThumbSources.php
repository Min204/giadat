<?php

class ThemeHouse_Thumbnails_Option_ThumbSources
{
    /**
     * Renders checkboxes allowing the selection of nodes.
     *
     * @param XenForo_View $view           View object
     * @param string       $fieldPrefix    Prefix for the HTML form field name
     * @param array        $preparedOption Prepared option info
     * @param bool         $canEdit        True if an "edit" link should appear
     *
     * @return XenForo_Template_Abstract Template object
     */
    public static function renderOption(XenForo_View $view, $fieldPrefix, array $preparedOption, $canEdit)
    {
        $thumbnailsModel = XenForo_Model::create('ThemeHouse_Thumbnails_Model_Thumbnails');

        $preparedOption = $thumbnailsModel->getPreparedOption($preparedOption);

        return XenForo_ViewAdmin_Helper_Option::renderOptionTemplateInternal(
            'th_option_thumb_source_thumbnails', $view, $fieldPrefix, $preparedOption, $canEdit);
    }

    public static function verifyOption(array &$option, XenForo_DataWriter $dw, $fieldName)
    {
        $db = XenForo_Application::getDb();

        $db->update('xf_thread', array('thumbnail_cache_th' => ''));

        return true;
    }
}
