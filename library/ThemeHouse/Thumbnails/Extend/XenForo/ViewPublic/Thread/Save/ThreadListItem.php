<?php

/**
 * @see XenForo_ViewPublic_Thread_Save_ThreadListItem
 */
class ThemeHouse_Thumbnails_Extend_XenForo_ViewPublic_Thread_Save_ThreadListItem extends XFCP_ThemeHouse_Thumbnails_Extend_XenForo_ViewPublic_Thread_Save_ThreadListItem
{
    protected $_threadModel;

    protected $_thumbnailsModel;

    /**
     * @return ThemeHouse_Thumbnails_Model_Thumbnails
     */
    protected function _getThumbnailsModel()
    {
        if (!$this->_thumbnailsModel) {
            $this->_thumbnailsModel = XenForo_Model::create('ThemeHouse_Thumbnails_Model_Thumbnails');
        }

        return $this->_thumbnailsModel;
    }

    /**
     * @return XenForo_Model_Thread
     */
    protected function _getThreadModel()
    {
        if (!$this->_threadModel) {
            $this->_threadModel = XenForo_Model::create('XenForo_Model_Thread');
        }

        return $this->_threadModel;
    }
}
