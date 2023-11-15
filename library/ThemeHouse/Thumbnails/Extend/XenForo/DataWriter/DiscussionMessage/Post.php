<?php

/**
 * @see XenForo_DataWriter_DiscussionMessage_Post
 */
class ThemeHouse_Thumbnails_Extend_XenForo_DataWriter_DiscussionMessage_Post extends XFCP_ThemeHouse_Thumbnails_Extend_XenForo_DataWriter_DiscussionMessage_Post
{
    protected function _messagePreSave()
    {
        parent::_messagePreSave();

        if ($this->_hasParentDiscussion && $this->get('position') == 0) {
            $threadDw = $this->getDiscussionDataWriter();
            $threadDw->set('thumbnail_cache_th', '');
        }
    }
}
