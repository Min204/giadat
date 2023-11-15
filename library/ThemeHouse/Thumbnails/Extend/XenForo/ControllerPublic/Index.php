<?php

/**
 * @see XenForo_ControllerPublic_Index
 */
class ThemeHouse_Thumbnails_Extend_XenForo_ControllerPublic_Index extends XFCP_ThemeHouse_Thumbnails_Extend_XenForo_ControllerPublic_Index
{
    /**
     * @see XenForo_ControllerPublic_Index::actionIndex()
     */
    public function actionIndex()
    {
        $responseView = parent::actionIndex();

        if ($responseView instanceof XenForo_ControllerResponse_View) {
            /* @var $thumbnailsModel ThemeHouse_Thumbnails_Model_Thumbnails */
            $thumbnailsModel = XenForo_Model::create('ThemeHouse_Thumbnails_Model_Thumbnails');

            if (!empty($responseView->params['nodeList']['nodesGrouped']) &&
                 XenForo_Application::get('options')->th_thumbsNodeView_thumbnails) {
                $thumbnailsModel->addThumbsToNodesGrouped($responseView->params['nodeList']['nodesGrouped']);
            }
        }

        return $responseView;
    }
}
