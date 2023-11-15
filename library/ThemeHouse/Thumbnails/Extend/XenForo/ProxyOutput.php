<?php

/**
 * @see XenForo_ProxyOutput
 */
class ThemeHouse_Thumbnails_Extend_XenForo_ProxyOutput extends XFCP_ThemeHouse_Thumbnails_Extend_XenForo_ProxyOutput
{
    /**
     * @see XenForo_ProxyOutput::__construct()
     */
    public function __construct(array $input, XenForo_Dependencies_Abstract $dependencies)
    {
        parent::__construct($input, $dependencies);

        if (!empty($input['thumbnail'])) {
            $this->_mode = 'thumbnail';
            $this->_url = trim(strval($input['thumbnail']));
        }
    }

    /**
     * @see XenForo_ProxyOutput::output()
     */
    public function output()
    {
        if ($this->_mode != 'thumbnail') {
            return parent::output();
        }

        $error = null;

        $isValidRequest = $this->isValidRequest($error);

        $this->_outputThumbnail($error);
    }

    protected function _outputThumbnail($error)
    {
        if (empty(XenForo_Application::getOptions()->imageLinkProxy['thumbnails'])) {
            $error = 'disabled';
        }

        /* @var $proxyModel XenForo_Model_ImageProxy */
        $proxyModel = XenForo_Model::create('ThemeHouse_Thumbnails_Model_ThumbnailProxy');

        $image = false;

        if (!$error) {
            $urlParts = parse_url($this->_url);
            if ($this->_isLocalHost($urlParts['host']) &&
            (empty($_SERVER['SERVER_NAME']) || !$this->_isLocalHost($_SERVER['SERVER_NAME']))) {
                $error = 'local_url';
            }
        }

        if (!$error) {
            $thumbnail = $proxyModel->getThumbnail($this->_url);
            if ($thumbnail) {
                $thumbnail = $proxyModel->prepareThumbnail($thumbnail);
                if ($thumbnail['use_file']) {
                    $proxyModel->logThumbnailView($thumbnail);

                    $eTag = !empty($_SERVER['HTTP_IF_NONE_MATCH']) ? $_SERVER['HTTP_IF_NONE_MATCH'] : null;
                    if ($eTag && $eTag == $thumbnail['fetch_date']) {
                        $this->_response->setHttpResponseCode(304);
                        $this->_response->clearHeader('Last-Modified');
                        $this->_response->sendHeaders();

                        return;
                    }
                } else {
                    $thumbnail = false;
                    $error = 'retrieve_failed';
                }
            }
        }

        if (empty($thumbnail)) {
            $thumbnail = $proxyModel->getPlaceHolderThumbnail();
        }

        $thumbnailTypes = array(
            'image/gif',
            'image/jpeg',
            'image/pjpeg',
            'image/png',
        );

        if (in_array($thumbnail['mime_type'], $thumbnailTypes)) {
            $this->_response->setHeader('Content-type', $thumbnail['mime_type'], true);
            $this->_setDownloadFileName($thumbnail['file_name'], true);
        } else {
            $this->_response->setHeader('Content-type', 'application/octet-stream', true);
            $this->_setDownloadFileName($thumbnail['file_name']);
        }

        $this->_response->setHeader('ETag', $thumbnail['fetch_date'], true);
        if ($thumbnail['file_size']) {
            $this->_response->setHeader('Content-Length', $thumbnail['file_size'], true);
        }
        $this->_response->setHeader('X-Content-Type-Options', 'nosniff');

        if ($error) {
            $this->_response->setHeader('X-Proxy-Error', $error);
        }

        $this->_response->sendHeaders();

        $thumbnailData = new XenForo_FileOutput($thumbnail['file_path']);
        $thumbnailData->output();
    }
}
