<?php

/**
 * @see XenForo_ControllerPublic_Watched
 */
class ThemeHouse_Thumbnails_Extend_XenForo_ControllerPublic_Watched extends XFCP_ThemeHouse_Thumbnails_Extend_XenForo_ControllerPublic_Watched
{
    /**
     * @see XenForo_ControllerPublic_FindNew::actionThreads()
     */
    public function actionThreads()
    {
        $response = parent::actionThreads();

        if ($response instanceof XenForo_ControllerResponse_View) {
            $xenOptions = XenForo_Application::get('options');
            if ($xenOptions->th_thumbsWatchedThreads_thumbnails) {
                if (isset($response->params['newThreads'])) {
                    $thumbnailsModel = $this->_getThumbnailsModel();
                    $response->params['newThreads'] = $thumbnailsModel->addThumbsToSearchThreads(
                        $response->params['newThreads']);
                }
            }
        }

        return $response;
    }

    /**
     * @see XenForo_ControllerPublic_FindNew::actionThreadsAll()
     */
    public function actionThreadsAll()
    {
        $response = parent::actionThreadsAll();

        if ($response instanceof XenForo_ControllerResponse_View) {
            $xenOptions = XenForo_Application::get('options');
            if ($xenOptions->th_thumbsWatchedThreads_thumbnails) {
                if (isset($response->params['threads'])) {
                    $thumbnailsModel = $this->_getThumbnailsModel();
                    $response->params['threads'] = $thumbnailsModel->addThumbsToSearchThreads(
                        $response->params['threads']);
                }
            }
        }

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
