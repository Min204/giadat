<?php

/**
 * @see XenForo_ControllerPublic_Category
 */
class ThemeHouse_Thumbnails_Extend_XenForo_ControllerPublic_Category extends XFCP_ThemeHouse_Thumbnails_Extend_XenForo_ControllerPublic_Category
{
    /**
     * @see XenForo_ControllerPublic_Category::actionIndex()
     */
    public function actionIndex()
    {
        $responseView = parent::actionIndex();

        if ($responseView instanceof XenForo_ControllerResponse_View) {
            $thumbnailsModel = $this->_getThumbnailsModel();

            if (!empty($responseView->params['nodeList']['nodesGrouped']) &&
                 XenForo_Application::get('options')->th_thumbsNodeView_thumbnails) {
                $thumbnailsModel->addThumbsToNodesGrouped($responseView->params['nodeList']['nodesGrouped']);
            }
        }

        return $responseView;
    }

    /**
     * @return ThemeHouse_Thumbnails_Model_Thumbnails
     */
    protected function _getThumbnailsModel()
    {
        return $this->getModelFromCache('ThemeHouse_Thumbnails_Model_Thumbnails');
    }
}
