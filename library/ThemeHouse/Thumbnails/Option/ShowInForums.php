<?php

class ThemeHouse_Thumbnails_Option_ShowInForums
{
    /**
     * Renders multi-select menu allowing the selection of nodes.
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
        /* @var $nodeModel XenForo_Model_Node */
        $nodeModel = XenForo_Model::create('XenForo_Model_Node');

        $nodes = $nodeModel->getAllNodes();

        $options = array();

        foreach ($nodes as $nodeId => $node) {
            $options[$nodeId] = array(
                'value' => $nodeId,
                'label' => $node['title'],
                'selected' => !empty($preparedOption['option_value'][$nodeId]),
                'depth' => $node['depth'],
            );
            if (!in_array($node['node_type_id'], array(
                'Forum',
                'SocialCategory',
            ))) {
                $options[$nodeId]['disabled'] = 'disabled';
            }
        }

        $preparedOption['formatParams'] = $options;

        return XenForo_ViewAdmin_Helper_Option::renderOptionTemplateInternal(
            'th_option_show_in_forums_thumbnails', $view, $fieldPrefix, $preparedOption, $canEdit);
    }

    public static function verifyShowInForums(array &$option, XenForo_DataWriter $dw, $fieldName)
    {
        if ($option['_type'] == '_some') {
            unset($option['_type']);
            $verifiedOption = array(
                '_type' => '_some',
            );
            foreach ($option as $nodeId) {
                $verifiedOption[$nodeId] = $nodeId;
            }
            $option = $verifiedOption;
        }

        return true;
    }
}
