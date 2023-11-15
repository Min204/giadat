<?php

/**
 * @see XenForo_ControllerAdmin_Log
 */
class ThemeHouse_Thumbnails_Extend_XenForo_ControllerAdmin_Log extends XFCP_ThemeHouse_Thumbnails_Extend_XenForo_ControllerAdmin_Log
{
    public function actionThumbnailProxy()
    {
        $page = $this->_input->filterSingle('page', XenForo_Input::UINT);
        $perPage = 10;

        $proxyModel = $this->_getThumbnailProxyModel();

        $url = $this->_input->filterSingle('url', XenForo_Input::STRING);
        $sortOrder = $this->_input->filterSingle('order', XenForo_Input::STRING);

        $conditions = array(
            'url' => $url,
        );

        $viewParams = array(
            'thumbnails' => $proxyModel->prepareThumbnails(
                $proxyModel->getThumbnailProxyLogs($conditions,
                    array(
                        'page' => $page,
                        'perPage' => $perPage,
                        'order' => $sortOrder,
                    ))),
            'page' => $page,
            'perPage' => $perPage,
            'total' => $proxyModel->countThumbnailProxyItems($conditions),

            'url' => $url,
            'sortOrder' => $sortOrder,
        );

        return $this->responseView('ThemeHouse_Thumbnails_ViewAdmin_Log_ThumbnailProxy',
            'th_log_thumbnail_proxy_thumbnails', $viewParams);
    }

    public function actionThumbnailProxyViewThumbnail()
    {
        $thumbnail = $this->_getThumbnailOrFallback();

        $viewParams = array(
            'thumbnail' => $thumbnail,
        );

        return $this->responseView('ThemeHouse_Thumbnails_ViewAdmin_Log_ThumbnailProxyViewThumbnail',
            'th_log_thumbnail_proxy_view_thumbnails', $viewParams);
    }

    public function actionThumbnailProxyView()
    {
        $thumbnail = $this->_getThumbnailOrFallback();

        $viewParams = array(
            'thumbnail' => $thumbnail,
        );

        $this->_routeMatch->setResponseType('raw');

        return $this->responseView('ThemeHouse_Thumbnails_ViewAdmin_Log_ThumbnailProxyView', '', $viewParams);
    }

    protected function _getThumbnailOrFallback($thumbnailId = null)
    {
        if ($thumbnailId === null) {
            $thumbnailId = $this->_input->filterSingle('thumbnail_id', XenForo_Input::UINT);
        }

        $thumbnail = $this->_getThumbnailProxyModel()->getThumbnailById($thumbnailId);
        if ($thumbnail) {
            $thumbnail = $this->_getThumbnailProxyModel()->prepareThumbnail($thumbnail);
            if (!$thumbnail['use_file']) {
                $thumbnail = false;
            }
        }

        if (!$thumbnail) {
            $thumbnail = $this->_getThumbnailProxyModel()->getPlaceHolderThumbnail();
        }

        return $thumbnail;
    }

    public function actionThumbnailProxyRecache()
    {
        $url = $this->_input->filterSingle('url', XenForo_Input::STRING);

        $thumbnail = $this->_getThumbnailProxyModel()->getThumbnail($url, true);
        $thumbnail = $this->_getThumbnailProxyModel()->prepareThumbnail($thumbnail);
        if (!$thumbnail['use_file']) {
            $thumbnail['pruned'] = 0;
        }

        $viewParams = array(
            'thumbnail' => $thumbnail,
        );

        return $this->responseView('ThemeHouse_Thumbnails_ViewAdmin_Log_ThumbnailProxyRecache',
            'th_log_thumbnail_proxy_item_thumbnails', $viewParams);
    }

    /**
     * @return ThemeHouse_Thumbnails_Model_ThumbnailProxy
     */
    protected function _getThumbnailProxyModel()
    {
        return $this->getModelFromCache('ThemeHouse_Thumbnails_Model_ThumbnailProxy');
    }
}
