<?php

class ThemeHouse_Thumbnails_Install_Controller extends ThemeHouse_Install
{
    protected $_resourceManagerUrl = 'https://xenforo.com/community/resources/th-thumbnails.4834/';

    protected function _getTables()
    {
        return array(
            'xf_thumbnail_th' => array(
                'content_id' => 'int UNSIGNED NOT NULL',
                'content_type' => 'varchar(25) NOT NULL',
                'thumb_url' => 'varchar(255) DEFAULT \'\'',
                'thumb_width' => 'int UNSIGNED NOT NULL DEFAULT 0',
                'thumb_height' => 'int UNSIGNED NOT NULL DEFAULT 0',
            ),
            'xf_thumbnail_proxy_th' => array(
                'thumbnail_id' => 'int UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY',
                'image_id' => 'int UNSIGNED NOT NULL DEFAULT 0',
                'url' => 'text NOT NULL',
                'url_hash' => 'varbinary(32) NOT NULL',
                'file_size' => 'int UNSIGNED NOT NULL DEFAULT 0',
                'file_name' => 'varchar(250) NOT NULL DEFAULT \'\'',
                'mime_type' => 'varchar(100) NOT NULL DEFAULT \'\'',
                'fetch_date' => 'int UNSIGNED NOT NULL DEFAULT 0',
                'first_request_date' => 'int UNSIGNED NOT NULL DEFAULT 0',
                'last_request_date' => 'int UNSIGNED NOT NULL DEFAULT 0',
                'views' => 'int UNSIGNED NOT NULL DEFAULT 0',
                'pruned' => 'int UNSIGNED NOT NULL DEFAULT 0',
                'is_processing' => 'int UNSIGNED NOT NULL DEFAULT 0',
                'failed_date' => 'int UNSIGNED NOT NULL DEFAULT 0',
                'fail_count' => 'smallint UNSIGNED NOT NULL DEFAULT 0',
            ),
        );
    }

    protected function _postInstall()
    {
        $addOn = $this->getModelFromCache('XenForo_Model_AddOn')->getAddOnById('Waindigo_Thumbnails');

        if ($addOn) {
            $db = XenForo_Application::getDb();

            $db->query('
                INSERT INTO xf_thumbnail_th (content_id, content_type, thumb_url, thumb_width, thumb_height)
                SELECT content_id, content_type, thumb_url, thumb_width, thumb_height
                FROM xf_thumbnail_waindigo');

            $db->query('
                INSERT INTO xf_thumbnail_proxy_th (image_id, url, url_hash, file_size, file_name, mime_type, fetch_date, first_request_date, last_request_date, views, pruned, is_processing, failed_date, fail_count)
                SELECT image_id, url, url_hash, file_size, file_name, mime_type, fetch_date, first_request_date, last_request_date, views, pruned, is_processing, failed_date, fail_count
                FROM xf_thumbnail_proxy_waindigo');
        }

        $addOn = $this->getModelFromCache('XenForo_Model_AddOn')->getAddOnById('Hex_Thumbnails');

        if ($addOn) {
            $db = XenForo_Application::getDb();

            $db->query('
                INSERT INTO xf_thumbnail_th (content_id, content_type, thumb_url, thumb_width, thumb_height)
                SELECT content_id, content_type, thumb_url, thumb_width, thumb_height
                FROM xf_thumbnail_hex');

            $db->query('
                INSERT INTO xf_thumbnail_proxy_th (image_id, url, url_hash, file_size, file_name, mime_type, fetch_date, first_request_date, last_request_date, views, pruned, is_processing, failed_date, fail_count)
                SELECT image_id, url, url_hash, file_size, file_name, mime_type, fetch_date, first_request_date, last_request_date, views, pruned, is_processing, failed_date, fail_count
                FROM xf_thumbnail_proxy_hex');
        }
    }

    protected function _getUniqueKeys()
    {
        return array(
            'xf_thumbnail_proxy_th' => array(
                'url_hash' => array(
                    'url_hash',
                ),
            ),
        );
    }

    protected function _getKeys()
    {
        return array(
            'xf_thumbnail_proxy_th' => array(
                'pruned_fetch_date' => array(
                    'pruned',
                    'fetch_date',
                ),
                'last_request_date' => array(
                    'last_request_date',
                ),
            ),
        );
    }

    protected function _getTableChanges()
    {
        return array(
            'xf_thread' => array(
                'thumbnail_url' => 'varchar(255) DEFAULT \'\'',
                'thumbnail_cache_th' => 'mediumblob NULL',
            ),
            'xf_forum' => array(
                'thumb_sources' => 'varchar(255) DEFAULT \'\'',
                'thumb_width' => 'int UNSIGNED NOT NULL DEFAULT \'0\'',
                'thumb_height' => 'int UNSIGNED NOT NULL DEFAULT \'0\'',
                'require_thumb' => 'tinyint UNSIGNED NOT NULL DEFAULT 0',
            ),
        );
    }

    protected function _getAddOnTableChanges()
    {
        return array(
            'ThemeHouse_SocialGroups' => array(
                'xf_social_forum' => array(
                    'thumb_sources' => 'varchar(255) DEFAULT \'\'',
                    'thumb_width' => 'int UNSIGNED NOT NULL DEFAULT \'0\'',
                    'thumb_height' => 'int UNSIGNED NOT NULL DEFAULT \'0\'',
                ),
            ),
        );
    }
}
