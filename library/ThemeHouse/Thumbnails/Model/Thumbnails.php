<?php

class ThemeHouse_Thumbnails_Model_Thumbnails extends Xenforo_Model
{
    protected $_defaultThumbnail = array();

    protected static $_attachments = array();

    protected static $_posts = array();

    protected static $_articlePages = array();

    /**
     * @param array $forum
     *
     * @return array
     */
    public function getOptionValue(array &$forum)
    {
        if (!isset($forum['node_id'])) {
            return array();
        }
        if (!isset($forum['thumb_sources'])) {
            $dw = XenForo_DataWriter::create('XenForo_DataWriter_Forum');
            $dw->setExistingData($forum['node_id']);
            $forum['thumb_sources'] = $dw->getExisting('thumb_sources');
            $forum['thumb_width'] = $dw->getExisting('thumb_width');
            $forum['thumb_height'] = $dw->getExisting('thumb_height');
        }
        $options = array_filter(explode(',', $forum['thumb_sources']));
        if (empty($options)) {
            return array(
                '0' => '1',
            );
        } else {
            return $options;
        }
    }

    /**
     * @param array $preparedOption
     *
     * @return array
     */
    public function getPreparedOption(array $preparedOption)
    {
        $preparedOption['count'] = array(
            1,
            2,
            3,
            4
        );

        $preparedOption['sources'] = array(
            1 => new XenForo_Phrase('th_user_entered_url_thumbnails'),
            2 => new XenForo_Phrase('th_first_attachment_thumbnails'),
            3 => new XenForo_Phrase('th_first_image_in_post_thumbnails'),
            4 => new XenForo_Phrase('th_no_thumbnail_image_thumbnails'),
            5 => new XenForo_Phrase('th_author_avatar_thumbnails'),
        );

        $columns = array(
            1 => null,
            2 => null,
            3 => null,
            4 => null
        );
        foreach ($preparedOption['option_value'] as $row => $column) {
            $columns[$column] = $row;
        }
        $columnClasses = array(
            1 => '',
            2 => '',
            3 => '',
            4 => ''
        );
        foreach ($columns as $column => $row) {
            if (!isset($columnClasses[$column - 1])) {
                if (isset($row)) {
                    $columnClasses[$column] = 'selected';
                }
            } else {
                if ($columnClasses[$column - 1] == 'disabled') {
                    $columnClasses[$column] = 'disabled';
                } elseif ($row) {
                    $columnClasses[$column] = 'selected';
                } elseif ($columnClasses[$column - 1] == '') {
                    $columnClasses[$column] = 'disabled';
                }
            }
        }
        $preparedOption['columnClasses'] = $columnClasses;

        return $preparedOption;
    }

    /**
     * @param array $nodesGrouped
     */
    public function addThumbsToNodesGrouped(array &$nodesGrouped)
    {
        $threads = array();
        $articles = array();

        foreach ($nodesGrouped as $parentNodeId => $parentNode) {
            foreach ($parentNode as $nodeId => $node) {
                if (isset($node['thread'])) {
                    $threadIds[$parentNodeId][$nodeId] = $node['thread']['thread_id'];
                    $threads[$node['thread']['thread_id']] = $node['thread'];
                    $forums[$nodeId] = $node;
                }
                if (isset($node['article'])) {
                    $articleIds[$parentNodeId][$nodeId] = $node['article']['article_id'];
                    $articles[$node['article']['article_id']] = $node['article'];
                    $libraries[$nodeId] = $node;
                }
            }
        }
        if ($threads) {
            $threads = $this->addThumbsToSearchThreads($threads, $forums);
            foreach ($threadIds as $parentNodeId => $nodeIds) {
                foreach ($nodeIds as $nodeId => $threadId) {
                    $nodesGrouped[$parentNodeId][$nodeId]['thread'] = $threads[$threadId];
                }
            }
        }
        if ($articles) {
            $articles = $this->addThumbsToSearchArticles($articles, $libraries);
            foreach ($articleIds as $parentNodeId => $nodeIds) {
                foreach ($nodeIds as $nodeId => $articleId) {
                    $nodesGrouped[$parentNodeId][$nodeId]['article'] = $articles[$articleId];
                }
            }
        }
    }

    /**
     * @param array $threads
     * @param array $nodes
     *
     * @return array
     */
    public function addThumbsToSearchThreads(array $threads, array $nodes = array())
    {
        $threadIds = array_keys($threads);
        $postIds = $this->_getFirstPosts($threadIds, $threads);
        if (class_exists('ThemeHouse_LastPostAvatar_Listener_TemplateHook')) {
            if (XenForo_Application::get('options')->th_lastPostAvatar_thumbnails) {
                $postIds = array_unique(array_merge($postIds, $this->_getLastPosts($threadIds, $threads)));
            }
        }

        $attachments = $this->_getAttachments('post', $postIds);
        $posts = $this->_getPosts($postIds);

        $forumThreads = $this->_arrangeThreadsByForum($threads);
        $oldThreads = $threads;
        $newThreads = array();
        foreach ($forumThreads as $forumId => $threads) {
            if (isset($nodes[$forumId])) {
                $node = $nodes[$forumId];
            } else {
                $node = array(
                    'node_id' => $forumId,
                );
            }
            $newThreads[$forumId] = $this->addThumbsToThreads($threads, $node);
        }
        $threads = array();
        foreach ($oldThreads as $threadId => $thread) {
            $threads[$threadId] = $newThreads[$thread['node_id']][$threadId];
        }

        return $threads;
    }

    /**
     * @param array $articles
     * @param array $nodes
     *
     * @return array
     */
    public function addThumbsToSearchArticles(array $articles, array $nodes = array())
    {
        $articleIds = array_keys($articles);
        $articlePageIds = $this->_getFirstArticlePages($articleIds, $articles);
        $attachments = $this->_getAttachments('library-article', $articlePageIds);
        $articlePages = $this->_getArticlePages($articlePageIds);

        $libraryArticles = $this->_arrangeArticlesByLibrary($articles);
        $oldArticles = $articles;
        $newArticles = array();
        foreach ($libraryArticles as $libraryId => $articles) {
            if (isset($nodes[$libraryId])) {
                $node = $nodes[$libraryId];
            } else {
                $node = array(
                    'node_id' => $libraryId,
                );
            }
            $newArticles[$libraryId] = $this->addThumbsToArticles($articles, $node);
        }
        $articles = array();
        foreach ($oldArticles as $articleId => $article) {
            $articles[$articleId] = $newArticles[$article['node_id']][$articleId];
        }

        return $articles;
    }

    /**
     * @param array $threads
     *
     * @return array
     */
    protected function _arrangeThreadsByForum(array $threads)
    {
        $forumThreads = array();
        foreach ($threads as $threadId => $thread) {
            $forumThreads[$thread['node_id']][$threadId] = $thread;
        }

        return $forumThreads;
    }

    /**
     * @param array $articles
     *
     * @return array
     */
    protected function _arrangeArticlesByLibrary(array $articles)
    {
        $libraryArticles = array();
        foreach ($articles as $articleId => $article) {
            $libraryArticles[$article['node_id']][$articleId] = $article;
        }

        return $libraryArticles;
    }

    /**
     * @param string $original
     *
     * @return string
     */
    protected function _urlMap($original)
    {
        $newUrl = $original;
        $requestPaths = XenForo_Application::get('requestPaths');
        $baseUrl = $requestPaths['fullBasePath'];

        if (strpos($newUrl, 'http://') === false) {
            $newUrl = $baseUrl.$newUrl;
        }

        return $newUrl;
    }

    /**
     * @param array $thread
     * @param array $forum
     * @param array $options
     *
     * @return array
     */
    public function addOpenGraphToThread(array $thread, array $forum, array $options = null)
    {
        $thread = $this->addThumbToThread($thread, $forum, $options);
        if (isset($thread['thumbnail']['thumbnail_url'])) {
            $thread['open_graph'] = $this->_urlMap($thread['thumbnail']['thumbnail_url']);
        }

        return $thread;
    }

    /**
     * @param array $thread
     * @param array $forum
     * @param array $options
     *
     * @return array
     */
    public function addThumbToThread(array $thread, array $forum, array $options = null)
    {
        $threads = $this->addThumbsToThreads(array(
            $thread['thread_id'] => $thread,
        ), $forum, $options);

        return $threads[$thread['thread_id']];
    }

    /**
     * @param array $article
     * @param array $library
     * @param array $options
     *
     * @return array
     */
    public function addThumbToArticle(array $article, array $library, array $options = null)
    {
        $articles = $this->addThumbsToArticles(array(
            $article['article_id'] => $article,
        ), $library, $options);

        return $articles[$article['article_id']];
    }

    /**
     * @param array $articles
     * @param array $library
     * @param array $options
     *
     * @return array
     */
    public function addThumbsToArticles(array $articles, array $library, array $options = null)
    {
        if (!isset($options)) {
            $options = $this->getOptions($library);
        }

        $contents = array(
            'articles' => $articles,
        );

        $contents = $this->addThumbsToContents($contents, $library, $options);

        return $contents['articles'];
    }

    /**
     * @param array $threads
     * @param array $forum
     * @param array $options
     *
     * @return array
     */
    public function addThumbsToThreads(array $threads, array $forum, array $options = null)
    {
        if (!isset($options)) {
            $options = $this->getOptions($forum);
        }

        $contents = array(
            'threads' => $threads,
        );

        if (class_exists('ThemeHouse_LastPostAvatar_Listener_TemplateHook')) {
            if (XenForo_Application::get('options')->th_lastPostAvatar_thumbnails) {
                $contents['lastPostThreads'] = $this->_getLastPostThreads($threads);
            }
        }

        $contents = $this->addThumbsToContents($contents, $forum, $options);

        if (XenForo_Application::get('options')->th_enableThumbnailCache_thumbnails) {
            $this->updateThreadThumbnailCache($contents['threads']);
        }

        if (isset($contents['lastPostThreads'])) {
            $contents['threads'] = $this->_combineLastPostThumbsIntoThreads($contents['lastPostThreads'],
                $contents['threads']);
        }

        return $contents['threads'];
    }

    public function updateThreadThumbnailCache(array $threads)
    {
        foreach ($threads as $threadId => $thread) {
            if (empty($thread['thumbnail_cache_th']) && !empty($thread['thumbnail'])) {
                $this->_getDb()->update('xf_thread',
                    array(
                        'thumbnail_cache_th' => ThemeHouse_Helper_Php::serialize($thread['thumbnail']),
                    ), 'thread_id = '.$threadId);
            }
        }
    }

    /**
     * @param array $contents
     * @param array $node
     * @param array $options
     *
     * @return array
     */
    public function addThumbsToContents(array $contents, array $node, array $options = null)
    {
        foreach ($contents as $contentType => $content) {
            foreach ($content as $contentItemId => $contentItem) {
                if (XenForo_Application::get('options')->th_enableThumbnailCache_thumbnails &&
                     !empty($contentItem['thumbnail_cache_th'])) {
                    $contents[$contentType][$contentItemId]['thumbnail'] = ThemeHouse_Helper_Php::unserialize(
                        $contentItem['thumbnail_cache_th']);
                }
            }
        }

        foreach ($options as $option) {
            switch ($option) {
                case 1:
                    $contents = $this->_addUserEnteredURLs($contents, $node);
                    break;
                case 2:
                    $contents = $this->_addFirstAttachments($contents, $node);
                    break;
                case 3:
                    $contents = $this->_addFirstImages($contents, $node);
                    break;
                case 4:
                    $contents = $this->_addNoThumbnailImages($contents, $node);
                case 5:
                    $contents = $contents;
            }
        }

        return $contents;
    }

    /**
     * @param array $threads
     *
     * @return array
     */
    protected function _getLastPostThreads(array $threads)
    {
        foreach ($threads as $threadId => $thread) {
            $threads[$threadId]['first_post_id'] = $thread['last_post_id'];
        }

        return $threads;
    }

    /**
     * @param array $lastPostThreads
     * @param array $threads
     *
     * @return array
     */
    protected function _combineLastPostThumbsIntoThreads(array $lastPostThreads, array $threads)
    {
        foreach ($lastPostThreads as $threadId => $thread) {
            if (isset($thread['thumbnail'])) {
                $threads[$threadId]['lastPostInfo']['thumbnail'] = $thread['thumbnail'];
            }
        }

        return $threads;
    }

    /**
     * @param array $forum
     *
     * @return array
     */
    public function getOptions(array $forum)
    {
        $forumOptions = $this->_sortOptions($this->getOptionValue($forum));
        $defaultOptions = $this->_sortOptions(XenForo_Application::get('options')->th_thumbSources_thumbnails);
        $options = array();
        foreach ($forumOptions as $option) {
            if ($option == 0) {
                $options = array_merge_recursive($options, $defaultOptions);
            } else {
                $options[] = $option;
            }
        }

        return array_unique($options);
    }

    /**
     * @param array $contents
     * @param array $node
     *
     * @return array
     */
    protected function _addUserEnteredURLs(array $contents, array $node = array())
    {
        if (isset($contents['threads'])) {
            $contents['threads'] = $this->_addUserEnteredURLsToContents(
                $contents['threads'], $node);
        }
        if (isset($contents['lastPostThreads'])) {
            $contents['lastPostThreads'] = $this->_addUserEnteredURLsToContents(
                $contents['lastPostThreads'], $node);
        }
        if (isset($contents['articles'])) {
            $contents['articles'] = $this->_addUserEnteredURLsToContents(
                $contents['articles'], $node);
        }

		return $contents;
    }

    /**
     * @param array $contents
     * @param array $node
     *
     * @return array
     */
    protected function _addUserEnteredURLsToContents(array $contents, array $node = array())
    {
		foreach ($contents as $contentItemId => $contentItem) {
			if (isset($contentItem['thumbnail_url']) and $contentItem['thumbnail_url']) {
				$newThumbnail = array(
					'thumbnail_url' => $contentItem['thumbnail_url']
				);

				if (XenForo_Application::$versionId >= 1030033) {
					$newThumbnail['thumbnailUrl'] = $this->_handleThumbnailProxyOption($newThumbnail['thumbnail_url']);
				}

				$contents[$contentItemId]['thumbnail'] = array_merge($newThumbnail,
					$this->_calculateDimensions(array(), $node));
			}
		}

		return $contents;
    }

    /**
     * @param array $contents
     * @param array $node
     *
     * @return array
     */
    protected function _addFirstAttachments(array $contents, array $node)
    {
        if (isset($contents['threads']) || isset($contents['lastPostThreads'])) {
            $firstPostIds = array();
            if (isset($contents['threads'])) {
                $firstPostIds = $this->_getFirstPosts($contents['threads']);
            }
            if (isset($contents['lastPostThreads'])) {
                $firstPostIds = array_merge($firstPostIds,
                    $this->_getFirstPosts($contents['lastPostThreads']));
            }
            if ($firstPostIds) {
                $attachments = $this->_getAttachments('post', $firstPostIds);
                if (isset($contents['threads'])) {
					$contents['threads'] = $this->_addFirstAttachmentsToThreads(
                        $contents['threads'], $attachments, $node);
                }
                if (isset($contents['lastPostThreads'])) {
                    $contents['lastPostThreads'] = $this->_addFirstAttachmentsToThreads(
                        $contents['lastPostThreads'], $attachments, $node, true);
                }
            }
        }

        if (!empty($contents['articles'])) {
            $attachments = $this->_getAttachments('library_article', $contents['articles']);
            $contents['articles'] = $this->_addFirstAttachmentsToArticles(
                $contents['articles'], $attachments, $node);
        }

		return $contents;
    }

    /**
     * @param array $threads
     * @param array $attachments
     * @param array $forum
     * @param bool  $lastPost
     *
     * @return array
     */
    protected function _addFirstAttachmentsToThreads(array $threads, array $attachments,
        array $forum = array(), $lastPost = false)
    {
        $attachmentModel = $this->_getAttachmentModel();

        foreach ($threads as $threadId => $thread) {
            if ($lastPost) {
                $postId = $thread['last_post_id'];
            } else {
                $postId = $thread['first_post_id'];
            }

            if (isset($attachments[$postId])) {
                $threads[$threadId]['thumbnail'] = array_merge(
                    array(
                        'thumbnail_url' => $attachmentModel->getAttachmentThumbnailUrl($attachments[$postId]),
                    ), $this->_calculateDimensions($attachments[$postId], $forum));
            }
        }

		return $threads;
    }

    /**
     * @param array $articles
     * @param array $attachments
     * @param array $library
     *
     * @return array
     */
    protected function _addFirstAttachmentsToArticles(array $articles, array $attachments,
        array $library = array())
    {
        $attachmentModel = $this->_getAttachmentModel();

        foreach ($articles as $articleId => $article) {
            if (isset($attachments[$articleId])) {
                $article['thumbnail'] = array_merge(
                    array(
                        'thumbnail_url' => $attachmentModel->getAttachmentThumbnailUrl($attachments[$articleId]),
                    ), $this->_calculateDimensions($attachments[$article['article_id']], $library));
            }
        }

		return $articles;
    }

    /**
     * @param string $message
     * @param array  $forum
     *
     * @return array
     */
    public function getImagesFromMessage($message, array $forum = array())
    {
        preg_match_all('/\[img\](.*?)\[\/img\]/i', $message, $matches);
        $thumbnails = array();
        foreach ($matches[1] as $thumbnailUrl) {
            $newThumbnail = array(
                'thumbnail_url' => $thumbnailUrl,
            );
            if (XenForo_Application::$versionId >= 1030033) {
                $newThumbnail['thumbnailUrl'] = $this->_handleThumbnailProxyOption($thumbnailUrl);
            }
            $thumbnails[] = array_merge($newThumbnail, $this->_calculateDimensions(array(), $forum));
        }

        return $thumbnails;
    }

    /**
     * @param string $message
     */
    public function getImageFromMessage($message)
    {
        preg_match('/\[img\](.*?)\[\/img\]/i', $message, $matches);
        if (isset($matches[1])) {
            return $matches[1];
        } else {
            return;
        }
    }

    /**
     * @param array $contents
     * @param array $node
     *
     * @return array
     */
    protected function _addFirstImages(array $contents, array $node)
    {
        if (isset($contents['threads']) || isset($contents['lastPostThreads'])) {
            $firstPostIds = array();
            if (isset($contents['threads'])) {
                $firstPostIds = $this->_getFirstPosts($contents['threads']);
            }
            if (isset($contents['lastPostThreads'])) {
                $firstPostIds = array_merge($firstPostIds,
                    $this->_getFirstPosts($contents['lastPostThreads']));
            }
            $firstPosts = $this->_getPosts($firstPostIds);

            if (isset($contents['threads'])) {
                $contents['threads'] = $this->_addFirstImageInPostToThreads(
                    $contents['threads'], $firstPosts, $node);
            }

            if (isset($contents['lastPostThreads'])) {
                $contents['lastPostThreads'] = $this->_addFirstImageInPostToThreads(
                    $contents['lastPostThreads'], $firstPosts, $node);
            }
        }

        if (isset($contents['articles'])) {
            $articlePageModel = $this->_getArticlePageModel();
            $firstArticlePageIds = $this->_getFirstArticlePages($contents['articles']);
            $firstArticlePages = $articlePageModel->getArticlePagesByIds($firstArticlePageIds);
            $contents['articles'] = $this->_addFirstImageInPostToArticles(
                $contents['articles'], $firstArticlePages, $node);
        }

		return $contents;
    }

    /**
     * @param array $threads
     * @param array $firstPosts
     * @param array $forum
     *
     * @return array
     */
    protected function _addFirstImageInPostToThreads(array $threads, array $firstPosts,
        array $forum = array())
    {
        foreach ($firstPosts as $postId => $post) {
            if (isset($post['message']) and $thumbnailUrl = $this->getImageFromMessage($post['message'])) {
                $newThumbnail = array(
                    'thumbnail_url' => $thumbnailUrl,
                );
                if (XenForo_Application::$versionId >= 1030033) {
                    $newThumbnail['thumbnailUrl'] = $this->_handleThumbnailProxyOption($newThumbnail['thumbnail_url']);
                }

                $threads[$post['thread_id']]['thumbnail'] = array_merge($newThumbnail,
                    $this->_calculateDimensions(array(), $forum));
                unset($threads[array_search($post['thread_id'], $threads)]);
            }
        }

		return $threads;
    }

    /**
     * @param array $articles
     * @param array $firstArticlePages
     * @param array $library
     *
     * @return array
     */
    protected function _addFirstImageInPostToArticles(array $articles, array $firstArticlePages,
        array $library = array())
    {
        foreach ($firstArticlePages as $articlePageId => $articlePage) {
            if (isset($articlePage['message']) and $thumbnailUrl = $this->getImageFromMessage($articlePage['message'])) {
                $newThumbnail = array(
                    'thumbnail_url' => $thumbnailUrl,
                );
                if (XenForo_Application::$versionId >= 1030033) {
                    $newThumbnail['thumbnailUrl'] = $this->_handleThumbnailProxyOption($newThumbnail['thumbnail_url']);
                }

                $articles[$articlePage['article_id']]['thumbnail'] = array_merge($newThumbnail,
                    $this->_calculateDimensions(array(), $library));
                unset($articles[array_search($articlePage['article_id'], $articles)]);
            }
        }

		return $articles;
    }

    /**
     * @param array $contents
     * @param array $node
     *
     * @return array
     */
    protected function _addNoThumbnailImages(array $contents, array $node)
    {
        foreach ($contents as $contentType => $content) {
            foreach ($content as $contentItemId => $contentItem) {
				if (!isset($contentItem['thumbnail'])) {
					$contents[$contentType][$contentItemId]['thumbnail'] = $this->_getDefaultThumbnail($node);
				}
			}
		}

        return $contents;
    }

    /**
     * @param array $node
     *
     * @return array
     */
    protected function _getDefaultThumbnail(array $node = array())
    {
        if (!isset($this->_defaultThumbnail[$node['node_id']])) {
            list($width, $height) = $this->_getDefaultDimensions($node);
            $maxDimension = max($width, $height);
            $attachment = array();
            $noThumbImage = XenForo_Application::get('options')->th_noThumbImage_thumbnails;
            if ($noThumbImage) {
                $attachment['thumbnail_width'] = $width;
                $attachment['thumbnail_height'] = $height;
                $newThumbnail = array(
                    'thumbnail_url' => $noThumbImage,
                );
                if (XenForo_Application::$versionId >= 1030033) {
                    $newThumbnail['thumbnailUrl'] = $this->_handleThumbnailProxyOption($newThumbnail['thumbnail_url']);
                }
                $this->_defaultThumbnail[$node['node_id']] = array_merge($newThumbnail,
                    $this->_calculateDimensions($attachment, $node));
            } elseif ($maxDimension <= 48) {
                $attachment['thumbnail_width'] = 48;
                $attachment['thumbnail_height'] = 48;
                $this->_defaultThumbnail[$node['node_id']] = array_merge(
                    array(
                        'thumbnail_url' => 'styles/default/xenforo/avatars/avatar_s.png',
                    ), $this->_calculateDimensions($attachment, $node));
            } elseif ($maxDimension <= 96) {
                $attachment['thumbnail_width'] = 96;
                $attachment['thumbnail_height'] = 96;
                $this->_defaultThumbnail[$node['node_id']] = array_merge(
                    array(
                        'thumbnail_url' => 'styles/default/xenforo/avatars/avatar_m.png',
                    ), $this->_calculateDimensions($attachment, $node));
            } else {
                $attachment['thumbnail_width'] = 192;
                $attachment['thumbnail_height'] = 192;
                $this->_defaultThumbnail[$node['node_id']] = array_merge(
                    array(
                        'thumbnail_url' => 'styles/default/xenforo/avatars/avatar_l.png',
                    ), $this->_calculateDimensions($attachment, $node));
            }
        }

        return $this->_defaultThumbnail[$node['node_id']];
    }

    /**
     * @param array $forum
     *
     * @return array
     */
    protected function _getDefaultDimensions(array $forum = array())
    {
        $xenOptions = XenForo_Application::get('options');

        $width = $xenOptions->th_thumbWidth_thumbnails;
        $height = $xenOptions->th_thumbHeight_thumbnails;
        if (!$width) {
            $width = 48;
        }
        if (!$height) {
            $height = 48;
        }
        if (!empty($forum['thumb_width'])) {
            $width = $forum['thumb_width'];
        }
        if (!empty($forum['thumb_height'])) {
            $height = $forum['thumb_height'];
        }

        return array(
            $width,
            $height,
        );
    }

    /**
     * @param array $attachment
     * @param array $forum
     *
     * @return array
     */
    protected function _calculateDimensions(array $attachment = array(), array $forum = array())
    {
        list($width, $height) = $this->_getDefaultDimensions($forum);
        $newWidth = $width;
        $newHeight = $height;
        $newRatio = $newWidth / $newHeight;

        $oldWidth = $newWidth;
        $oldHeight = $newHeight;

        if (isset($attachment['thumbnail_width'])) {
            $oldWidth = $attachment['thumbnail_width'];
        }

        if (isset($attachment['thumbnail_height'])) {
            $oldHeight = $attachment['thumbnail_height'];
        }

        if ($oldWidth != 0 and $oldHeight != 0) {
            $oldRatio = $oldWidth / $oldHeight;
        } else {
            $oldRatio = 1;
        }

        if ($newRatio > $oldRatio) {
            $newHeight = round($newWidth / $oldRatio);
        } elseif ($newRatio < $oldRatio) {
            $newWidth = round($newHeight * $oldRatio);
        }

        return array(
            'width' => $newWidth,
            'height' => $newHeight,
            'max-width' => $width,
            'max-height' => $height
        );
    }

    /**
     * @param array $threads
     *
     * @return array
     */
    protected function _getFirstPosts(array $threads)
    {
        $firstPosts = array();
		foreach ($threads as $threadId => $thread) {
            $firstPosts[$threadId] = $thread['first_post_id'];
		}

        return $firstPosts;
    }

    /**
     * @param array $threads
     *
     * @return array
     */
    protected function _getLastPosts(array $threads)
    {
        $lastPosts = array();
		foreach ($threads as $threadId => $thread) {
            $lastPosts[$threadId] = $thread['last_post_id'];
		}
	
        return $lastPosts;
    }

    /**
     * @param array $postIds
     *
     * @return array
     */
    protected function _getPosts(array $postIds)
    {
        $postModel = $this->_getPostModel();

        $posts = array();
        $checkPostIds = $postIds;
        foreach ($checkPostIds as $key => $postId) {
            if (isset(self::$_posts[$postId])) {
                $posts[$postId] = self::$_posts[$postId];
                unset($postIds[$key]);
            }
        }

        if (!empty($postIds)) {
            $posts = $posts + $postModel->getPostsByIds($postIds);
        }

        self::$_posts = self::$_posts + $posts;

        return $posts;
    }

    /**
     * @param array $articlePageIds
     *
     * @return array
     */
    protected function _getArticlePages(array $articlePageIds)
    {
        $articlePageModel = $this->_getArticlePageModel();

        $articlePages = array();
        $checkArticlePageIds = $articlePageIds;
        foreach ($checkArticlePageIds as $key => $articlePageId) {
            if (isset(self::$_articlePages[$articlePageId])) {
                $articlePages[$articlePageId] = self::$_articlePages[$articlePageId];
                unset($articlePageIds[$key]);
            }
        }

        if (!empty($articlePageIds)) {
            $articlePages = $articlePages + $articlePageModel->getArticlePagesByIds($articlePageIds);
        }

        self::$_articlePages = self::$_articlePages + $articlePages;

        return $articlePages;
    }

    /**
     * @param array $articles
     *
     * @return array
     */
    protected function _getFirstArticlePages(array $articles)
    {
        $firstArticlePages = array();
        foreach ($articles as $articleId => $article) {
            if ($article['discussion_type'] != 'redirect') {
                $firstArticlePages[$articleId] = $article['first_article_page_id'];
            }
        }

        return $firstArticlePages;
    }

    /**
     * @param string $contentType
     * @param array  $contentIds
     *
     * @return array
     */
    protected function _getAttachments($contentType, array $contentIds)
    {
        $attachmentModel = $this->_getAttachmentModel();

        $attachments = array();
        $checkContentIds = $contentIds;
        foreach ($checkContentIds as $key => $contentId) {
            if (isset(self::$_attachments[$contentType][$contentId])) {
                if (!empty(self::$_attachments[$contentType][$contentId])) {
                    $attachments[$contentId] = self::$_attachments[$contentType][$contentId];
                }
                unset($contentIds[$key]);
            } else {
                self::$_attachments[$contentType][$contentId] = array();
            }
        }

        if (!empty($contentIds)) {
            $attachments = $attachments + $attachmentModel->getFirstAttachmentsByContentIds($contentType, $contentIds);
        }

        if (!empty(self::$_attachments[$contentType])) {
            self::$_attachments[$contentType] = array_merge($attachments, self::$_attachments[$contentType]);
        }

        return $attachments;
    }

    /**
     * @param array $thumbSources
     *
     * @return array
     */
    protected function _sortOptions(array $thumbSources)
    {
        $thumbSources = array_flip($thumbSources);
        ksort($thumbSources);
        for ($i = 1; $i <= 4; ++$i) {
            if (!isset($thumbSources[$i])) {
                $thumbSources = array_slice($thumbSources, 0, 4);
            }
        }

        return $thumbSources;
    }

    /**
     * @param int   $sourceId
     * @param array $forum
     *
     * @return bool
     */
    public function isInThumbSources($sourceId, array $forum)
    {
        if (!$this->canShowThumbs($forum)) {
            return false;
        }
        $thumbSources = $this->getOptions($forum);

        return in_array($sourceId, $thumbSources);
    }

    /**
     * Determines if the specified forum shows thumbnails with the given
     * permissions.
     *
     * @param array      $node            Info about the node
     * @param string     $errorPhraseKey  Returned phrase key for a specific error
     * @param array|null $nodePermissions List of permissions for this page; if
     *                                    not provided, use visitor's permissions
     * @param array|null $viewingUser
     *
     * @return bool
     */
    public function canShowThumbs($node, &$errorPhraseKey = '', array $nodePermissions = null, array $viewingUser = null)
    {
        if (!is_array($node)) {
            return false;
        }

        if (isset($node['node_type_id'])) {
            if ($node['node_type_id'] == 'Forum' || $node['node_type_id'] == 'SocialCategory') {
                $showInForums = XenForo_Application::get('options')->th_showInForums_thumbnails;
                if (($showInForums['_type'] == '_some' and !isset($showInForums[$node['node_id']]))) {
                    return false;
                } elseif ($showInForums['_type'] == '_permissions') {
                    $this->standardizeViewingUserReferenceForNode($node['node_id'], $viewingUser, $nodePermissions);

                    return XenForo_Permission::hasContentPermission($nodePermissions, 'canViewThumbs');
                }

                return true;
            } elseif ($node['node_type_id'] == 'Library') {
                $showInLibraries = XenForo_Application::get('options')->th_showInLibraries_thumbnails;
                if (($showInLibraries['_type'] == '_some' and !isset($showInLibraries[$node['node_id']]))) {
                    return false;
                } elseif ($showInLibraries['_type'] == '_permissions') {
                    $this->standardizeViewingUserReferenceForNode($node['node_id'], $viewingUser, $nodePermissions);

                    return XenForo_Permission::hasContentPermission($nodePermissions, 'articleThumbs');
                }

                return true;
            }
        }

        return false;
    }

    protected function _handleThumbnailProxyOption($url)
    {
        list($class, $target, $type, $schemeMatch) = XenForo_Helper_String::getLinkClassTarget($url);

        if ($type == 'external') {
            $options = XenForo_Application::getOptions();
            if (!empty($options->imageLinkProxy['thumbnails'])) {
                $url = $this->_generateProxyLink('thumbnail', $url);
            } elseif (!empty($options->imageLinkProxy['images'])) {
                $url = $this->_generateProxyLink('image', $url);
            }
        }

        return $url;
    }

    protected function _generateProxyLink($proxyType, $url)
    {
        $hash = hash_hmac('md5', $url,
            XenForo_Application::getConfig()->globalSalt.XenForo_Application::getOptions()->imageLinkProxyKey);

        return 'proxy.php?'.$proxyType.'='.urlencode($url).'&hash='.$hash;
    }

    /**
     * @return ThemeHouse_Library_Model_ArticlePage
     */
    protected function _getArticlePageModel()
    {
        return $this->getModelFromCache('ThemeHouse_Library_Model_ArticlePage');
    }

    /**
     * @return XenForo_Model_Post
     */
    protected function _getPostModel()
    {
        return $this->getModelFromCache('XenForo_Model_Post');
    }

    /**
     * @return XenForo_Model_Attachment
     */
    protected function _getAttachmentModel()
    {
        return $this->getModelFromCache('XenForo_Model_Attachment');
    }
}
