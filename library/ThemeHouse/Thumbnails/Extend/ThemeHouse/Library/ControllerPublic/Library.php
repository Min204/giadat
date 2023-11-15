<?php

/**
 * @see ThemeHouse_Library_ControllerPublic_Library
 */
class ThemeHouse_Thumbnails_Extend_ThemeHouse_Library_ControllerPublic_Library extends XFCP_ThemeHouse_Thumbnails_Extend_ThemeHouse_Library_ControllerPublic_Library
{
    /**
     * @see ThemeHouse_Library_ControllerPublic_Library::actionIndex()
     */
    public function actionIndex()
    {
        $responseView = parent::actionIndex();

        $libraryId = $this->_input->filterSingle('node_id', XenForo_Input::UINT);
        if (!isset($responseView->params['library'])) {
            return $responseView;
        } else {
            $library = $responseView->params['library'];
        }

        $thumbnailsModel = $this->_getThumbnailsModel();
        if (!$thumbnailsModel->canShowThumbs($library)) {
            return $responseView;
        }

        $articles = array();
        $stickyArticles = array();

        if (isset($responseView->params['articles'])) {
            $articles = $responseView->params['articles'];
        }
        if (isset($responseView->params['stickyArticles'])) {
            $stickyArticles = $responseView->params['stickyArticles'];
        }

        $options = $thumbnailsModel->getOptions($library);
        $responseView->params['articles'] = $thumbnailsModel->addThumbsToArticles($articles, $library, $options);
        $responseView->params['stickyArticles'] = $thumbnailsModel->addThumbsToArticles($stickyArticles, $library,
            $options);

        return $responseView;
    }

    /**
     * Shows thumbs of images in the thread being created.
     *
     * @return XenForo_ControllerResponse_Abstract
     */
    public function actionCreateArticleThumbnails()
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
     * @see ThemeHouse_Library_ControllerPublic_Library::actionAddArticle()
     */
    public function actionAddArticle()
    {
        $GLOBALS['ThemeHouse_Library_ControllerPublic_Library'] = $this;

        return parent::actionAddArticle();
    }

    /**
     * @return ThemeHouse_Thumbnails_Model_Thumbnails
     */
    protected function _getThumbnailsModel()
    {
        return $this->getModelFromCache('ThemeHouse_Thumbnails_Model_Thumbnails');
    }
}
