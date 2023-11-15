<?php

class ThemeHouse_Thumbnails_Option_StickyArticles
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
        if (class_exists('ThemeHouse_Library_Listener_TemplateHook')) {
            return XenForo_ViewAdmin_Helper_Option::renderOptionTemplateInternal('option_list_option_onoff', $view,
                $fieldPrefix, $preparedOption, $canEdit);
        }
    }
}
