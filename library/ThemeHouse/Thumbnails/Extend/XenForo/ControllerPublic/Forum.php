<?php

/**
 * @see XenForo_ControllerPublic_Forum
 */
class ThemeHouse_Thumbnails_Extend_XenForo_ControllerPublic_Forum extends XFCP_ThemeHouse_Thumbnails_Extend_XenForo_ControllerPublic_Forum
{
    /**
     * @see XenForo_ControllerPublic_Forum::actionIndex()
     */
    public function actionIndex()
    {
        /* @var $response XenForo_ControllerResponse_View */
        $response = parent::actionIndex();

        if (XenForo_Application::$versionId < 1020000) {
            return $this->_getThumbnailsResponse($response);
        }

        if ($response instanceof XenForo_ControllerResponse_View) {
            /* @var $thumbnailsModel ThemeHouse_Thumbnails_Model_Thumbnails */
            $thumbnailsModel = XenForo_Model::create('ThemeHouse_Thumbnails_Model_Thumbnails');

            if (!empty($response->params['nodeList']['nodesGrouped']) &&
                 XenForo_Application::get('options')->th_thumbsNodeView_thumbnails) {
                $thumbnailsModel->addThumbsToNodesGrouped($response->params['nodeList']['nodesGrouped']);
            }
        }

        return $response;
    }

    /**
     * @see XenForo_ControllerPublic_Forum::actionForum()
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

                if (!empty($responseView->params['nodeList']['nodesGrouped'])) {
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

        return $this->responseView('ThemeHouse_Thumbnails_ViewPublic_Forum_CreateThread_Thumbnails',
            'th_thread_create_thumbs_thumbnails', $viewParams);
    }

    /**
     * @see XenForo_ControllerPublic_Forum::actionAddThread()
     */
    public function actionAddThread()
    {
        $GLOBALS['XenForo_ControllerPublic_Forum'] = $this;

        return parent::actionAddThread();
    }

    /**
     * @return ThemeHouse_Thumbnails_Model_Thumbnails
     */
    protected function _getThumbnailsModel()
    {
        return $this->getModelFromCache('ThemeHouse_Thumbnails_Model_Thumbnails');
    }
}
