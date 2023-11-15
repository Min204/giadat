<?php

class ThemeHouse_Thumbnails_Model_ThumbnailProxy extends XenForo_Model
{
    /**
     * Fetches thumbnail info from the cache, or requests it if it is not
     * available.
     *
     * @param string $url
     * @param bool   $forceRefresh If true, the thumbnail is always refreshed
     *
     * @return array
     *
     * @throws InvalidArgumentException
     */
    public function getThumbnail($url, $forceRefresh = false)
    {
        $thumbnail = $this->getThumbnailByUrl($url);
        if ($thumbnail) {
            if ($forceRefresh) {
                $this->_fetchAndCacheThumbnail($url, $thumbnail);
            } else {
                $thumbnail = $this->refreshThumbnailIfRequired($thumbnail);
            }
        } else {
            $thumbnail = $this->_fetchAndCacheThumbnail($url);
        }

        return $thumbnail;
    }

    /**
     * Gets thumbnail info for an thumbnail known by ID.
     *
     * @param int $thumbnailId
     *
     * @return array
     */
    public function getThumbnailById($thumbnailId)
    {
        return $this->_getDb()->fetchRow(
            '
			SELECT *
			FROM xf_thumbnail_proxy_th
			WHERE thumbnail_id = ?
		', $thumbnailId);
    }

    /**
     * Gets the cached thumbnail for a URL.
     *
     * @param string $url
     *
     * @return array
     */
    public function getThumbnailByUrl($url)
    {
        if (!$url || !Zend_Uri::check($url)) {
            throw new InvalidArgumentException('Invalid URL');
        }

        return $this->_getDb()->fetchRow(
            '
			SELECT *
			FROM xf_thumbnail_proxy_th
			WHERE url_hash = ?
		', md5($url));
    }

    /**
     * Prepares an thumbnail for output.
     *
     * @param array $thumbnail
     *
     * @return array
     */
    public function prepareThumbnail(array $thumbnail)
    {
        $thumbnail['file_path'] = $this->getThumbnailPath($thumbnail);
        $thumbnail['use_file'] = file_exists($thumbnail['file_path']);
        $thumbnail['refreshable'] = $this->_requiresRefetch($thumbnail);

        return $thumbnail;
    }

    /**
     * @param array $thumbnails
     *
     * @return array
     */
    public function prepareThumbnails(array $thumbnails)
    {
        foreach ($thumbnails as &$thumbnail) {
            $thumbnail = $this->prepareThumbnail($thumbnail);
        }

        return $thumbnails;
    }

    /**
     * Refreshes the thumbnail if required.
     *
     * @param array $thumbnail
     *
     * @return array
     */
    public function refreshThumbnailIfRequired(array $thumbnail)
    {
        if ($this->_requiresRefetch($thumbnail)) {
            $thumbnail = $this->_fetchAndCacheThumbnail($thumbnail['url'], $thumbnail);
        }

        return $thumbnail;
    }

    /**
     * Logs an thumbnail view.
     *
     * @param array $thumbnail
     *
     * @return bool
     */
    public function logThumbnailView(array $thumbnail)
    {
        if (empty($thumbnail['thumbnail_id'])) {
            return false;
        }

        $this->_getDb()->query(
            '
			UPDATE xf_thumbnail_proxy_th SET
				views = views + 1,
				last_request_date = ?
			WHERE thumbnail_id = ?
		', array(
                XenForo_Application::$time,
                $thumbnail['thumbnail_id'],
            ));

        return true;
    }

    /**
     * Determines if a refresh is needed.
     *
     * @param array $thumbnail
     *
     * @return bool
     */
    protected function _requiresRefetch(array $thumbnail)
    {
        $filePath = $this->getThumbnailPath($thumbnail);

        if ($thumbnail['is_processing'] && XenForo_Application::$time - $thumbnail['is_processing'] < 5) {
            if (file_exists($filePath)) {
                // likely being refreshed
                return false;
            }

            sleep(5 - (XenForo_Application::$time - $thumbnail['is_processing']));

            $newThumbnail = $this->getThumbnailByUrl($thumbnail['url']);
            if ($newThumbnail) {
                $thumbnail = $newThumbnail;
            }
        }

        if ($thumbnail['failed_date'] && $thumbnail['fail_count']) {
            $nextCheck = $this->_failedGetNextCheckDate($thumbnail['failed_date'], $thumbnail['fail_count']);

            return (XenForo_Application::$time >= $nextCheck);
        }

        if ($thumbnail['pruned']) {
            return true;
        }

        if (XenForo_Application::getOptions()->thumbnailCacheTTL) {
            if ($thumbnail['fetch_date'] <
                 XenForo_Application::$time - 86400 * XenForo_Application::getOptions()->thumbnailCacheTTL) {
                return true;
            }
        }

        if (!file_exists($filePath)) {
            return true;
        }

        if (XenForo_Application::getOptions()->thumbnailCacheRefresh && !$thumbnail['fail_count']) {
            if ($thumbnail['fetch_date'] <
                 XenForo_Application::$time - 86400 * XenForo_Application::getOptions()->thumbnailCacheRefresh) {
                return true;
            }
        }

        return false;
    }

    /**
     * Based on the last failure and the number of consecutive failures,
     * determine
     * the next time we can refresh a failed thumbnail.
     * After 10, we stop trying.
     *
     * @param int $failDate  Last fail date
     * @param int $failCount Total failures
     *
     * @return int
     */
    protected function _failedGetNextCheckDate($failDate, $failCount)
    {
        if (!$failCount) {
            // not failed - may need to check now
            return XenForo_Application::$time;
        }

        if ($failCount > 10) {
            // too many failures, always in the future
            return XenForo_Application::$time + 86400;
        }

        switch ($failCount) {
            case 1:
                $delay = 60;
                break; // 1 minute
            case 2:
                $delay = 5 * 60;
                break; // 5 minutes
            case 3:
                $delay = 30 * 60;
                break; // 30 minutes
            case 4:
                $delay = 3600;
                break; // 1 hour
            case 5:
                $delay = 6 * 3600;
                break; // 6 hours


            default:
                $delay = ($failCount - 5) * 86400; // 1, 2, 3... days
        }

        return $failDate + $delay;
    }

    /**
     * Fetches a remote thumbnail, stores it in the file system and records it
     * in the database.
     *
     * @param string     $url
     * @param array|null $thumbnail
     *
     * @return array
     */
    protected function _fetchAndCacheThumbnail($url, array $thumbnail = null)
    {
        $urlHash = md5($url);
        $time = XenForo_Application::$time;

        if (!$thumbnail || empty($thumbnail['thumbnail_id'])) {
            $thumbnail = array(
                'url' => $url,
                'url_hash' => $urlHash,
                'fetch_date' => $time,
                'file_size' => 0,
                'file_name' => '',
                'mime_type' => '',
                'views' => 0,
                'first_request_date' => $time,
                'last_request_date' => $time,
                'pruned' => 1,
                'failed_date' => 0,
                'fail_count' => 0,
            );
        }

        $thumbnail['is_processing'] = time(); // intentionally time() as we might have slept


        $db = $this->_getDb();

        if (empty($thumbnail['thumbnail_id'])) {
            $db->insert('xf_thumbnail_proxy_th', $thumbnail);
            $thumbnail['thumbnail_id'] = $db->lastInsertId();
        } else {
            $db->query(
                '
				UPDATE xf_thumbnail_proxy_th
				SET is_processing = ?
				WHERE thumbnail_id = ?
			',
                array(
                    $thumbnail['is_processing'],
                    $thumbnail['thumbnail_id'],
                ));
        }

        $results = $this->_fetchThumbnailForProxy($url);
        $requestFailed = $results['failed'];
        $streamFile = $results['tempFile'];
        $fileName = $results['fileName'];
        $mimeType = $results['mimeType'];
        $fileSize = $results['fileSize'];

        if (!$requestFailed) {
            $filePath = $this->getThumbnailPath($thumbnail);
            $dirName = dirname($filePath);
            @unlink($filePath);
            @unlink($filePath.'.tmp');

            if (XenForo_Helper_File::createDirectory($dirName, true) &&
                 XenForo_Helper_File::safeRename($streamFile, $filePath.'.tmp')) {
                if (function_exists('exif_imagetype')) {
                    $imageType = exif_imagetype($filePath.'.tmp');
                } else {
                    $imageSize = getimagesize($filePath.'.tmp');
                    $imageType = $imageSize[2];
                }
                $image = XenForo_Image_Abstract::createFromFile($filePath.'.tmp', $imageType);
                if ($image && $image->thumbnail(XenForo_Application::get('options')->attachmentThumbnailDimensions)) {
					if (XenForo_Application::get('options')->th_resizeUrls_thumbnails) {
						$image->thumbnailFixedShorterSide('100');
					}

                    $image->output($imageType, $filePath);
                    @unlink($filePath.'.tmp');
                    $fileSize = filesize($filePath);
                } else {
                    XenForo_Helper_File::safeRename($filePath.'.tmp', $filePath);
                }
                unset($image);

                // ensure the filename fits -- if it's too long, take off from the beginning to keep extension
                $length = utf8_strlen($fileName);
                if ($length > 250) {
                    $fileName = utf8_substr($fileName, $length - 250);
                }

                $data = array(
                    'fetch_date' => time(),
                    'file_size' => $fileSize,
                    'file_name' => $fileName,
                    'mime_type' => $mimeType,
                    'pruned' => 0,
                    'is_processing' => 0,
                    'failed_date' => 0,
                    'fail_count' => 0,
                );
                $thumbnail = array_merge($thumbnail, $data);

                $db->update('xf_thumbnail_proxy_th', $data,
                    'thumbnail_id = '.$db->quote($thumbnail['thumbnail_id']));
            }
        }

        @unlink($streamFile);

        if ($requestFailed) {
            $data = array(
                'is_processing' => 0,
                'failed_date' => time(),
                'fail_count' => $thumbnail['fail_count'] + 1,
            );
            $thumbnail = array_merge($thumbnail, $data);

            $db->update('xf_thumbnail_proxy_th', $data,
                'thumbnail_id = '.$db->quote($thumbnail['thumbnail_id']));
        }

        return $thumbnail;
    }

    /**
     * Does a test fetch for the specified thumbnail for debugging purposes.
     * The thumbnail will always be fetched and the temporary file will be
     * removed.
     *
     * @param string $url
     *
     * @return array Associative array of information about the fetch
     */
    public function testThumbnailProxyFetch($url)
    {
        $results = $this->_fetchThumbnailForProxy($url);
        @unlink($results['tempFile']);
        unset($results['tempFile']);

        return $results;
    }

    /**
     * Fetches the thumbnail at the specified URL using the standard proxy
     * config.
     *
     * @param string $url
     *
     * @return array
     */
    protected function _fetchThumbnailForProxy($url)
    {
        $urlHash = md5($url);
        $urlParts = parse_url($url);

        XenForo_ImageProxyStream::register();

        // convert kilobytes to bytes
        XenForo_ImageProxyStream::setMaxSize(XenForo_Application::getOptions()->imageProxyMaxSize * 1024);

        $streamUri = 'xf-image-proxy://'.$urlHash.'-'.uniqid();
        $streamFile = XenForo_ImageProxyStream::getTempFile($streamUri);

        $requestFailed = true;
        $error = false;
        $thumbnailMeta = null;
        $fileName = !empty($urlParts['path']) ? basename($urlParts['path']) : '';
        $mimeType = '';
        $fileSize = 0;
        $thumbnail = false;

        try {
            $response = XenForo_Helper_Http::getClient($url,
                array(
                    'output_stream' => $streamUri,
                    'timeout' => 10,
                ))->setHeaders('Accept-encoding', 'identity')->request('GET');
            if ($response->isSuccessful()) {
                $disposition = $response->getHeader('Content-Disposition');
                if (is_array($disposition)) {
                    $disposition = end($disposition);
                }
                if ($disposition && preg_match('/filename=(\'|"|)(.+)\\1/siU', $disposition, $match)) {
                    $fileName = $match[2];
                }
                if (!$fileName) {
                    $fileName = 'thumbnail';
                }

                $mimeHeader = $response->getHeader('Content-Type');
                if (is_array($mimeHeader)) {
                    $mimeHeader = end($mimeHeader);
                }
                $mimeType = $mimeHeader ? $mimeHeader : 'unknown/unknown';

                $imageMeta = XenForo_ImageProxyStream::getMetaData($streamUri);
                if (!empty($imageMeta['error'])) {
                    switch ($imageMeta['error']) {
                        case 'not_thumbnail':
                            $error = new XenForo_Phrase('file_not_an_thumbnail');
                            break;

                        case 'too_large':
                            $error = new XenForo_Phrase('file_is_too_large');
                            break;

                        case 'invalid_type':
                            $error = new XenForo_Phrase('image_is_invalid_type');
                            break;

                        default:
                            $error = $imageMeta['error'];
                    }
                } else {
                    $requestFailed = false;
                    $thumbnail = $imageMeta['image'];
                    $mimeType = $thumbnail['mime'];
                    $fileSize = $imageMeta['length'];

                    $extension = XenForo_Helper_File::getFileExtension($fileName);
                    $extensionMap = array(
                        IMAGETYPE_GIF => array(
                            'gif',
                        ),
                        IMAGETYPE_JPEG => array(
                            'jpg',
                            'jpeg',
                            'jpe',
                        ),
                        IMAGETYPE_PNG => array(
                            'png',
                        ),
                    );
                    $validExtensions = $extensionMap[$thumbnail[2]];
                    if (!in_array($extension, $validExtensions)) {
                        $extensionStart = strrpos($fileName, '.');
                        $fileName = ($extensionStart ? substr($fileName, 0, $extensionStart) : $fileName).'.'.
                             $validExtensions[0];
                    }
                }
            } else {
                $error = new XenForo_Phrase('received_unexpected_response_code_x_message_y',
                    array(
                        'code' => $response->getStatus(),
                        'message' => $response->getMessage(),
                    ));
            }
        } catch (Exception $e) {
            $error = $e->getMessage();
            $response = null;
        }

        $response = null;

        return array(
            'url' => $url,
            'failed' => $requestFailed,
            'error' => $error,
            'thumbnail' => $thumbnail,
            'fileName' => $fileName,
            'mimeType' => $mimeType,
            'fileSize' => $fileSize,
            'tempFile' => $streamFile,
        );
    }

    /**
     * Deletes an thumbnail from the file system thumbnail cache.
     *
     * @param array $thumbnail
     */
    protected function _deleteFile(array $thumbnail)
    {
        $filePath = $this->getThumbnailPath($thumbnail);

        @unlink($filePath);
    }

    /**
     * Gets the path to an thumbnail in the file system thumbnail cache.
     *
     * @param array $thumbnail
     *
     * @return string
     */
    public function getThumbnailPath(array $thumbnail)
    {
        return sprintf('%s/thumbnail_cache/%d/%d-%s.data', XenForo_Helper_File::getInternalDataPath(),
            floor($thumbnail['thumbnail_id'] / 1000), $thumbnail['thumbnail_id'], $thumbnail['url_hash']);
    }

    /**
     * Prunes thumbnails from the file system cache that have expired.
     *
     * @param int|null $pruneDate
     */
    public function pruneThumbnailCache($pruneDate = null)
    {
        $db = $this->_getDb();

        if ($pruneDate === null) {
            if (!XenForo_Application::getOptions()->imageCacheTTL) {
                return;
            }

            $pruneDate = XenForo_Application::$time - (86400 * XenForo_Application::getOptions()->imageCacheTTL);
        }

        $thumbnails = $this->fetchAllKeyed(
            '
			SELECT *
			FROM xf_thumbnail_proxy_th
			WHERE fetch_date < ?
				AND pruned = 0
		', 'thumbnail_id', $pruneDate);

        if ($thumbnails) {
            foreach ($thumbnails as $thumbnailId => $thumbnail) {
                $this->_deleteFile($thumbnail);
            }

            $db->update('xf_thumbnail_proxy_th', array(
                'pruned' => 1,
            ), 'thumbnail_id IN ('.$db->quote(array_keys($thumbnails)).')');
        }
    }

    /**
     * Prunes unused thumbnail proxy log entries.
     *
     * @param null|int $pruneDate
     *
     * @return int
     */
    public function pruneThumbnailProxyLogs($pruneDate = null)
    {
        if ($pruneDate === null) {
            $options = XenForo_Application::getOptions();

            if (!$options->imageLinkProxyLogLength) {
                return 0;
            }
            if (!$options->imageCacheTTL) {
                // we're keeping thumbnails forever - can't prune
                return 0;
            }

            $maxTtl = max($options->imageLinkProxyLogLength, $options->imageCacheTTL);
            $pruneDate = XenForo_Application::$time - (86400 * $maxTtl);
        }

        // we can only remove logs where we've pruned the thumbnail
        return $this->_getDb()->delete('xf_thumbnail_proxy_th',
            'pruned = 1 AND last_request_date < '.intval($pruneDate));
    }

    /**
     * Prepares a collection of thumbnail proxy fetching related conditions into
     * an SQL clause.
     *
     * @param array $conditions   List of conditions
     * @param array $fetchOptions Modifiable set of fetch options (may have
     *                            joins pushed on to it)
     *
     * @return string SQL clause (at least 1=1)
     */
    public function prepareThumbnailProxyConditions(array $conditions, array &$fetchOptions)
    {
        $sqlConditions = array();
        $db = $this->_getDb();

        if (!empty($conditions['url'])) {
            if (is_array($conditions['url'])) {
                $sqlConditions[] = 'thumbnail_proxy.url LIKE '.
                     XenForo_Db::quoteLike($conditions['url'][0], $conditions['url'][1], $db);
            } else {
                $sqlConditions[] = 'thumbnail_proxy.url LIKE '.XenForo_Db::quoteLike($conditions['url'], 'lr', $db);
            }
        }

        return $this->getConditionsForClause($sqlConditions);
    }

    /**
     * Fetches thumbnail proxy items for log display.
     *
     * @param array $conditions
     * @param array $fetchOptions
     *
     * @return array
     */
    public function getThumbnailProxyLogs(array $conditions = array(), array $fetchOptions = array())
    {
        $limitOptions = $this->prepareLimitFetchOptions($fetchOptions);
        $whereConditions = $this->prepareThumbnailProxyConditions($conditions, $fetchOptions);

        $orderBy = 'last_request_date';
        if (!empty($fetchOptions['order'])) {
            switch ($fetchOptions['order']) {
                case 'last_request_date':
                case 'first_request_date':
                case 'views':
                case 'file_size':
                    $orderBy = $fetchOptions['order'];
            }
        }

        return $this->fetchAllKeyed(
            $this->limitQueryResults(
                "
				SELECT thumbnail_proxy.*
				FROM xf_thumbnail_proxy_th AS thumbnail_proxy
				WHERE $whereConditions
				ORDER BY thumbnail_proxy.$orderBy DESC
			", $limitOptions['limit'],
                $limitOptions['offset']), 'thumbnail_id');
    }

    /**
     * Counts all thumbnail proxy items.
     *
     * @param array $conditions
     *
     * @return int
     */
    public function countThumbnailProxyItems(array $conditions = array())
    {
        $fetchOptions = array();
        $whereConditions = $this->prepareThumbnailProxyConditions($conditions, $fetchOptions);

        return $this->_getDb()->fetchOne(
            "
			SELECT COUNT(*)
			FROM xf_thumbnail_proxy_th AS thumbnail_proxy
			WHERE $whereConditions
		");
    }

    /**
     * Gets the placeholder thumbnail fallback for errors.
     *
     * @return array
     */
    public function getPlaceHolderThumbnail()
    {
        $path = 'styles/default/xenforo/icons/missing-image.png';
        $url = XenForo_Application::getOptions()->boardUrl.'/'.$path;
        $filePath = XenForo_Application::getInstance()->getRootDir().'/'.$path;
        $lastModified = filemtime($filePath);

        return array(
            'url' => $url,
            'url_hash' => md5($url),
            'file_size' => filesize($filePath),
            'file_name' => 'missing-image.png',
            'mime_type' => 'image/png',
            'fetch_date' => $lastModified,
            'first_request_date' => $lastModified,
            'last_request_date' => XenForo_Application::$time,
            'views' => 1,
            'pruned' => 0,
            'is_processing' => 0,
            'failed_date' => 0,
            'fail_count' => 0,
            'file_path' => $filePath,
            'use_file' => true,
        );
    }
}
