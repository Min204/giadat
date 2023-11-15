<?php

/**
 * @see XenForo_ControllerPublic_FindNew
 */
class ThemeHouse_Thumbnails_Extend_XenForo_ControllerPublic_FindNew extends XFCP_ThemeHouse_Thumbnails_Extend_XenForo_ControllerPublic_FindNew
{
    /**
     * @see XenForo_ControllerPublic_FindNew::actionPosts()
     */
    public function actionPosts()
    {
        $response = parent::actionPosts();

        if ($response instanceof XenForo_ControllerResponse_View) {
            $xenOptions = XenForo_Application::get('options');
            if ($xenOptions->th_thumbsNewPosts_thumbnails) {
                if (isset($response->subView->params['threads'])) {
                    $thumbnailsModel = $this->_getThumbnailsModel();
                    $response->subView->params['threads'] = $thumbnailsModel->addThumbsToSearchThreads(
                        $response->subView->params['threads']);
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
