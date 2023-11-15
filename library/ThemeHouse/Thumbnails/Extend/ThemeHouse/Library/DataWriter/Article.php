<?php

/**
 * @see ThemeHouse_Library_DataWriter_Article
 */
class ThemeHouse_Thumbnails_Extend_ThemeHouse_Library_DataWriter_Article extends XFCP_ThemeHouse_Thumbnails_Extend_ThemeHouse_Library_DataWriter_Article
{
    /**
     * @see ThemeHouse_Library_DataWriter_Article::_getFields()
     */
    protected function _getFields()
    {
        $fields = parent::_getFields();
        $fields['xf_article']['thumbnail_url'] = array(
            'type' => self::TYPE_STRING,
            'default' => '',
        );

        return $fields;
    }

    /**
     * @see ThemeHouse_Library_DataWriter_Article::_discussionPreSave()
     */
    protected function _discussionPreSave()
    {
        parent::_discussionPreSave();

        if (!empty($GLOBALS['ThemeHouse_Library_ControllerPublic_Article'])) {
            /* @var $controller ThemeHouse_Library_ControllerPublic_Article */
            $controller = $GLOBALS['ThemeHouse_Library_ControllerPublic_Article'];

            $thumbnailUrl = $controller->getInput()->filterSingle('thumbnail_url', XenForo_Input::STRING);
            $this->set('thumbnail_url', $thumbnailUrl);
        }

        if (!empty($GLOBALS['ThemeHouse_Library_ControllerPublic_Library'])) {
            /* @var $controller ThemeHouse_Library_ControllerPublic_Library */
            $controller = $GLOBALS['ThemeHouse_Library_ControllerPublic_Library'];

            $thumbnailUrl = $controller->getInput()->filterSingle('thumbnail_url', XenForo_Input::STRING);
            $this->set('thumbnail_url', $thumbnailUrl);
        }
    }
}
