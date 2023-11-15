<?php

/**
 * @see XenForo_DataWriter_Discussion_Thread
 */
class ThemeHouse_Thumbnails_Extend_XenForo_DataWriter_Discussion_Thread extends XFCP_ThemeHouse_Thumbnails_Extend_XenForo_DataWriter_Discussion_Thread
{
    /**
     * @see XenForo_DataWriter_Discussion_Thread::_getFields()
     */
    protected function _getFields()
    {
        $fields = parent::_getFields();
        $fields['xf_thread']['thumbnail_url'] = array(
            'type' => self::TYPE_STRING,
            'default' => '',
            'maxLength' => 250,
        );
        $fields['xf_thread']['thumbnail_cache_th'] = array(
            'type' => self::TYPE_STRING,
            'default' => '',
        );

        return $fields;
    }

    /**
     * @see XenForo_DataWriter_Discussion_Thread::_discussionPreSave()
     */
    protected function _discussionPreSave()
    {
        parent::_discussionPreSave();

        if (!empty($GLOBALS['XenForo_ControllerPublic_Forum']) || !empty($GLOBALS['XenForo_ControllerPublic_Thread'])) {
            if (!empty($GLOBALS['XenForo_ControllerPublic_Thread'])) {
                /* @var $controller XenForo_ControllerPublic_Thread */
                $controller = $GLOBALS['XenForo_ControllerPublic_Thread'];
            } else {
                /* @var $controller XenForo_ControllerPublic_Forum */
                $controller = $GLOBALS['XenForo_ControllerPublic_Forum'];
            }
            $thumbnailUrlShown = $controller->getInput()->filterSingle('thumbnail_url_shown', XenForo_Input::UINT);
            if ($thumbnailUrlShown) {
                $thumbnailUrl = $controller->getInput()->filterSingle('thumbnail_url', XenForo_Input::STRING);
                $this->set('thumbnail_url', $thumbnailUrl);
            }
        }

        if ($this->isChanged('thumbnail_url')) {
            $this->set('thumbnail_cache_th', '');
        }

        $forum = $this->_getForumData();

        $xenOptions = XenForo_Application::get('options');
        if ($forum['require_thumb'] || $xenOptions->th_thumbnails_requireThumb) {
            $this->_requireThumbnail();
        }
    }

    protected function _requireThumbnail()
    {
        $forum = $this->_getForumData();

        /* @var $thumbnailsModel ThemeHouse_Thumbnails_Model_Thumbnails */
        $thumbnailsModel = $this->getModelFromCache('ThemeHouse_Thumbnails_Model_Thumbnails');

        if (!$thumbnailsModel->canShowThumbs($forum)) {
            return true;
        }

        $options = $thumbnailsModel->getOptions($forum);

        $allowThumbnailUrl = false;

        foreach ($options as $option) {
            switch ($option) {
                case 1:
                    $allowThumbnailUrl = true;
                    // user entered URL
                    if ($this->get('thumbnail_url')) {
                        return true;
                    }
                    break;
                case 2:
                    /* @var $firstMessageDw XenForo_DataWriter_DiscussionMessage_Post */
                    $firstMessageDw = $this->getFirstMessageDw();
                    $attachCount = $firstMessageDw->get('attach_count');
                    if ($attachCount) {
                        return true;
                    } else {
                        $attachmentHash = $firstMessageDw->getExtraData(
                            XenForo_DataWriter_DiscussionMessage_Post::DATA_ATTACHMENT_HASH);
                        $attachments = $this->_db->fetchOne(
                            '
                            SELECT COUNT(*)
                            FROM xf_attachment
                            WHERE temp_hash = '.
                                 $this->_db->quote($attachmentHash));
                        if ($attachments) {
                            return true;
                        }
                    }
                    break;
                case 3:
                    $firstMessageDw = $this->getFirstMessageDw();
                    $message = $firstMessageDw->get('message');
                    $images = $thumbnailsModel->getImagesFromMessage($message, $forum);
                    if ($images) {
                        return true;
                    }
                    break;
            }
        }

        if ($allowThumbnailUrl) {
            $this->error(new XenForo_Phrase('th_please_add_a_thumbnail_image_thumbnails'), 'thumbnail_url');
        } else {
            $this->error(new XenForo_Phrase('th_please_add_a_thumbnail_image_thumbnails'), 'message');
        }

        return false;
    }
}
