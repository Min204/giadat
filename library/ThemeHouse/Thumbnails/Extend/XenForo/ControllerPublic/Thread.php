<?php

/**
 * @see XenForo_ControllerPublic_Thread
 */
class ThemeHouse_Thumbnails_Extend_XenForo_ControllerPublic_Thread extends XFCP_ThemeHouse_Thumbnails_Extend_XenForo_ControllerPublic_Thread
{
    /**
     * @see XenForo_ControllerPublic_Thread::actionIndex()
     */
    public function actionIndex()
    {
        $response = parent::actionIndex();

        if ($response instanceof XenForo_ControllerResponse_View) {
            $thumbnailsModel = $this->_getThumbnailsModel();

            $thread = $response->params['thread'];
            $forum = $response->params['forum'];

            $response->params['thread'] = $thumbnailsModel->addOpenGraphToThread($thread, $forum);
        }

        return $response;
    }

    /**
     * @see XenForo_ControllerPublic_Thread::actionSave()
     */
    public function actionSave()
    {
        $GLOBALS['XenForo_ControllerPublic_Thread'] = $this;

        return parent::actionSave();
    }

    /**
     * @see XenForo_ControllerPublic_Thread::actionEdit()
     */
    public function actionEdit()
    {
        $response = parent::actionEdit();

        $threadId = $this->_input->filterSingle('thread_id', XenForo_Input::UINT);

        $ftpHelper = $this->getHelper('ForumThreadPost');
        list($thread, $forum) = $ftpHelper->assertThreadValidAndViewable($threadId);

        $thumbnailsModel = $this->_getThumbnailsModel();

        $response->params['canEditThumbnail'] = true;

        return $response;
    }

    /**
     * @return ThemeHouse_Thumbnails_Model_Thumbnails
     */
    protected function _getThumbnailsModel()
    {
        return $this->getModelFromCache('ThemeHouse_Thumbnails_Model_Thumbnails');
    }
}
