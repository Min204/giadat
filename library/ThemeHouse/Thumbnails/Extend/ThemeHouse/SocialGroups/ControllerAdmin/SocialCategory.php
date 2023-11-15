<?php

/**
 * @see ThemeHouse_SocialGroups_ControllerAdmin_SocialCategory
 */
class ThemeHouse_Thumbnails_Extend_ThemeHouse_SocialGroups_ControllerAdmin_SocialCategory extends XFCP_ThemeHouse_Thumbnails_Extend_ThemeHouse_SocialGroups_ControllerAdmin_SocialCategory
{
    /**
     * @see ThemeHouse_SocialGroups_ControllerAdmin_SocialCategory::actionEdit()
     */
    public function actionEdit()
    {
        $response = parent::actionEdit();

        $forumModel = $this->_getForumModel();

        if ($nodeId = $this->_input->filterSingle('node_id', XenForo_Input::UINT)) {
            // if a node ID was specified, we should be editing, so make sure a forum exists
            $forum = $forumModel->getForumById($nodeId);
        } else {
            $forum = array(
                'thumb_source' => '',
                'thumb_width' => 0,
                'thumb_height' => 0,
            );
        }

        $thumbnailsModel = $this->_getThumbnailsModel();

        $response->params['preparedOption'] = array();
        $response->params['preparedOption']['option_value'] = $thumbnailsModel->getOptionValue($forum);

        $response->params['preparedOption']['width'] = $forum['thumb_width'];
        $response->params['preparedOption']['height'] = $forum['thumb_height'];

        $response->params['preparedOption'] = $thumbnailsModel->getPreparedOption($response->params['preparedOption']);

        array_unshift($response->params['preparedOption']['sources'], new XenForo_Phrase('th_default_thumbnails'));

        return $response;
    }

    /**
     * @see ThemeHouse_SocialGroups_ControllerAdmin_SocialCategory::actionSave()
     */
    public function actionSave()
    {
        $GLOBALS['ThemeHouse_Thumbnails_ControllerAdmin_Forum'] = $this;

        return parent::actionSave();
    }

    /**
     * @param XenForo_DataWriter_Forum $dw
     */
    public function processThumbnails(XenForo_DataWriter_Forum $dw)
    {
        $options = $this->_input->filterSingle('options', XenForo_Input::ARRAY_SIMPLE,
            array(
                'array' => true,
            ));
        if (isset($options['th_thumbSources_Thumbnails'])) {
            $thumbSources = $options['th_thumbSources_thumbnails'];
            for ($i = 0; $i <= 5; ++$i) {
                if (!isset($thumbSources[$i])) {
                    $thumbSources[$i] = '';
                }
            }
            ksort($thumbSources);
            $thumbSources = implode(',', $thumbSources);
            $dw->set('thumb_sources', $thumbSources);
        }
        $width = $this->_input->filterSingle('thumb_width', XenForo_Input::UINT);
        $dw->set('thumb_width', $width);
        $height = $this->_input->filterSingle('thumb_height', XenForo_Input::UINT);
        $dw->set('thumb_height', $height);
    }

    /**
     * @return ThemeHouse_Thumbnails_Model_Thumbnails
     */
    protected function _getThumbnailsModel()
    {
        return $this->getModelFromCache('ThemeHouse_Thumbnails_Model_Thumbnails');
    }
}
