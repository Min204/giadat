<?php

/**
 * @see ThemeHouse_SocialGroups_ControllerPublic_SocialForum
 */
class ThemeHouse_Thumbnails_Extend_ThemeHouse_SocialGroups_ControllerPublic_SocialForum extends XFCP_ThemeHouse_Thumbnails_Extend_ThemeHouse_SocialGroups_ControllerPublic_SocialForum
{
    /**
     * @see ThemeHouse_SocialGroups_ControllerPublic_SocialForum::actionIndex()
     */
    public function actionIndex()
    {
        /* @var $response XenForo_ControllerResponse_View */
        $response = parent::actionIndex();

        return $this->_getThumbnailsResponse($response);
    }

    /**
     * @see ThemeHouse_SocialGroups_ControllerPublic_SocialForum::actionForum()
     */
    public function actionForum()
    {
        /* @var $response XenForo_ControllerResponse_View */
        $response = parent::actionForum();

        return $this->_getThumbnailsResponse($response);
    }

    /**
     * @param XenForo_ControllerResponse_Abstract $responseView
     *
     * @return XenForo_ControllerResponse_Abstract
     */
    protected function _getThumbnailsResponse(XenForo_ControllerResponse_Abstract $responseView)
    {
        if ($responseView instanceof XenForo_ControllerResponse_View) {
            if (isset($responseView->params['forum'])) {
                $forumId = $this->_input->filterSingle('node_id', XenForo_Input::UINT);
                $forum = $responseView->params['forum'];

                $thumbnailsModel = $this->_getThumbnailsModel();

                if (!empty($responseView->params['nodeList']['nodesGrouped']) &&
                     XenForo_Application::get('options')->th_thumbsNodeView_thumbnails) {
                    $thumbnailsModel->addThumbsToNodesGrouped($responseView->params['nodeList']['nodesGrouped']);
                }

                if (!$thumbnailsModel->canShowThumbs($forum)) {
                    return $responseView;
                }

                $threads = array();
                $stickyThreads = array();

                if (isset($responseView->params['threads'])) {
                    $threads = $responseView->params['threads'];
                }
                if (isset($responseView->params['stickyThreads'])) {
                    $stickyThreads = $responseView->params['stickyThreads'];
                }

                $options = $thumbnailsModel->getOptions($forum);
                $responseView->params['threads'] = $thumbnailsModel->addThumbsToThreads($threads, $forum, $options);
                $responseView->params['stickyThreads'] = $thumbnailsModel->addThumbsToThreads($stickyThreads, $forum,
                    $options);
            }
        }

        return $responseView;
    }

    /**
     * Shows thumbs of images in the thread being created.
     *
     * @return XenForo_ControllerResponse_Abstract
     */
    public function actionCreateThreadThumbnails()
    {
        $this->_assertPostOnly();

        $forumId = $this->_input->filterSingle('node_id', XenForo_Input::UINT);
        $forumName = $this->_input->filterSingle('node_name', XenForo_Input::STRING);

        $ftpHelper = $this->getHelper('ForumThreadPost');
        $forum = $ftpHelper->assertForumValidAndViewable($forumId ? $forumId : $forumName);

        $forumId = $forum['node_id'];

        $this->_assertCanPostThreadInForum($forum);

        $message = $this->getHelper('Editor')->getMessageText('message', $this->_input);
        $message = XenForo_Helper_String::autoLinkBbCode($message);

        $thumbnailsModel = $this->_getThumbnailsModel();
        $thumbs = $thumbnailsModel->getImagesFromMessage($message);

        $viewParams = array(
            'forum' => $forum,
            'thumbs' => $thumbs,
        );

        return $this->responseView('ThemeHouse_Thumbnails_ViewPublic_Thread_Thumbnails',
            'th_thread_create_thumbs_thumbnails', $viewParams);
    }

    /**
     * @see ThemeHouse_SocialGroups_ControllerPublic_SocialForum::actionAddThread()
     */
    public function actionAddThread()
    {
        $GLOBALS['ThemeHouse_Thumbnails_ControllerPublic_Forum'] = $this;

        return parent::actionAddThread();
    }

    /**
     * @param XenForo_DataWriter_Discussion_Thread $dw
     */
    public function processThumbnails(XenForo_DataWriter_Discussion_Thread $dw)
    {
        $thumbnailUrl = $this->_input->filterSingle('thumbnail_url', XenForo_Input::STRING);
        $dw->set('thumbnail_url', $thumbnailUrl);
    }

    /**
     * @return ThemeHouse_Thumbnails_Model_Thumbnails
     */
    protected function _getThumbnailsModel()
    {
        return $this->getModelFromCache('ThemeHouse_Thumbnails_Model_Thumbnails');
    }
}
