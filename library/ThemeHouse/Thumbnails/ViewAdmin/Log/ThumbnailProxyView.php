<?php

class ThemeHouse_Thumbnails_ViewAdmin_Log_ThumbnailProxyView extends XenForo_ViewAdmin_Base
{
    public function renderRaw()
    {
        $thumbnail = $this->_params['thumbnail'];

        $imageTypes = array(
            'image/gif',
            'image/jpeg',
            'image/pjpeg',
            'image/png',
        );

        if (in_array($thumbnail['mime_type'], $imageTypes)) {
            $this->_response->setHeader('Content-type', $thumbnail['mime_type'], true);
            $this->setDownloadFileName($thumbnail['file_name'], true);
        } else {
            $this->_response->setHeader('Content-type', 'application/octet-stream', true);
            $this->setDownloadFileName($thumbnail['file_name']);
        }

        $this->_response->setHeader('ETag', $thumbnail['fetch_date'], true);
        if ($thumbnail['file_size']) {
            $this->_response->setHeader('Content-Length', $thumbnail['file_size'], true);
        }
        $this->_response->setHeader('X-Content-Type-Options', 'nosniff');

        return new XenForo_FileOutput($thumbnail['file_path']);
    }
}
