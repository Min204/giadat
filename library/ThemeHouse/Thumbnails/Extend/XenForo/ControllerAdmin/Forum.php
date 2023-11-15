<?php

/**
 * @see XenForo_ControllerAdmin_Forum
 */
class ThemeHouse_Thumbnails_Extend_XenForo_ControllerAdmin_Forum extends XFCP_ThemeHouse_Thumbnails_Extend_XenForo_ControllerAdmin_Forum
{
    /**
     * @see XenForo_ControllerAdmin_Forum::actionEdit()
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
     * @see XenForo_ControllerAdmin_Forum::actionSave()
     */
    public function actionSave()
    {
        $GLOBALS['XenForo_ControllerAdmin_Forum'] = $this;

        return parent::actionSave();
    }

    /**
     * @return ThemeHouse_Thumbnails_Model_Thumbnails
     */
    protected function _getThumbnailsModel()
    {
        return $this->getModelFromCache('ThemeHouse_Thumbnails_Model_Thumbnails');
    }
}
