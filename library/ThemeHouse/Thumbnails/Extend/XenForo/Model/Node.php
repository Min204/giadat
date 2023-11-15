<?php

/**
 * @see XenForo_Model_Node
 */
class ThemeHouse_Thumbnails_Extend_XenForo_Model_Node extends XFCP_ThemeHouse_Thumbnails_Extend_XenForo_Model_Node
{
    /**
     * @see XenForo_Model_Node::getNodeDataForListDisplay()
     */
    public function getNodeDataForListDisplay($parentNode, $displayDepth, array $nodePermissions = null)
    {
        $nodeData = parent::getNodeDataForListDisplay($parentNode, $displayDepth, $nodePermissions);
        if (!empty($nodeData)) {
            $groupedNodes = $nodeData['nodesGrouped'];

            if (XenForo_Application::get('options')->th_thumbsNodeView_thumbnails) {
                $this->_addThreadThumbnailInfoForLastPosts($groupedNodes);
            }

            if (XenForo_Application::$versionId < 1020000) {
                $nodeData['nodeParents'] = array();
            }

            return array(
                'nodeParents' => $nodeData['nodeParents'],
                'nodesGrouped' => $groupedNodes,
                'parentNodeId' => $nodeData['parentNodeId'],
                'nodeHandlers' => $nodeData['nodeHandlers'],
                'nodePermissions' => $nodeData['nodePermissions'],
            );
        } else {
            return array();
        }
    }

    /**
     * @param array $nodes
     */
    protected function _addThreadThumbnailInfoForLastPosts(array &$nodes)
    {
        $posts = array();
        $articlePages = array();
        foreach ($nodes as &$depthNodes) {
            foreach ($depthNodes as &$node) {
                if (isset($node['lastPost']['post_id'])) {
                    $posts[$node['lastPost']['post_id']] = array();
                }
                if (isset($node['lastArticlePage']['article_page_id'])) {
                    $articlePages[$node['lastArticlePage']['article_page_id']] = array();
                }
            }
        }
        if (isset($posts[0])) {
            unset($posts[0]);
        }
        if (isset($articlePages[0])) {
            unset($articlePages[0]);
        }

        $threads = array();
        if (!empty($posts)) {
            $threads = $this->_getThreadInfoForPosts(array_keys($posts));
        }
        $articles = array();
        if (!empty($articlePages)) {
            $articles = $this->_getArticleInfoForArticlePages(array_keys($articlePages));
        }

        if (!empty($threads) || !empty($articles)) {
            foreach ($nodes as &$depthNodes) {
                foreach ($depthNodes as &$node) {
                    if (($node['node_type_id'] == 'Forum' || $node['node_type_id'] == 'SocialCategory') &&
                         isset($node['lastPost']['post_id']) && !empty($threads[$node['lastPost']['post_id']])) {
                        $node['lastPost']['avatar_date'] = $threads[$node['lastPost']['post_id']]['avatar_date'];
                        $node['lastPost']['gravatar'] = $threads[$node['lastPost']['post_id']]['gravatar'];
                        $node['firstPost']['avatar_date'] = $threads[$node['lastPost']['post_id']]['first_post_user_avatar_date'];
                        $node['firstPost']['gravatar'] = $threads[$node['lastPost']['post_id']]['first_post_user_gravatar'];
                        $node['thread'] = $threads[$node['lastPost']['post_id']];
                        unset($node['thread']['avatar_date']);
                        unset($node['thread']['gravatar']);
                        unset($node['thread']['first_post_user_avatar_date']);
                        unset($node['thread']['first_post_user_gravatar']);
                    } elseif (($node['node_type_id'] == 'Library') && isset($node['lastArticlePage']['article_page_id']) &&
                             !empty($articles[$node['lastArticlePage']['article_page_id']])) {
                        $node['lastArticlePage']['avatar_date'] = $articles[$node['lastArticlePage']['article_page_id']]['avatar_date'];
                        $node['lastArticlePage']['gravatar'] = $articles[$node['lastArticlePage']['article_page_id']]['gravatar'];
                        $node['firstArticlePage']['avatar_date'] = $articles[$node['lastArticlePage']['article_page_id']]['first_article_page_user_avatar_date'];
                        $node['firstArticlePage']['gravatar'] = $articles[$node['lastArticlePage']['article_page_id']]['first_article_page_user_gravatar'];
                        $node['article'] = $articles[$node['lastArticlePage']['article_page_id']];
                        unset($node['articlePage']['avatar_date']);
                        unset($node['articlePage']['gravatar']);
                        unset($node['articlePage']['first_article_page_avatar_date']);
                        unset($node['articlePage']['first_article_page_gravatar']);
                    }
                }
            }
        }
    }

    /**
     * @param array $postIds
     *
     * @return array
     */
    protected function _getThreadInfoForPosts(array $postIds)
    {
        if (!$postIds) {
            return array();
        }

        return $this->fetchAllKeyed(
            '
				SELECT post_id, user.avatar_date, user.gravatar, thread.*,
					first_post_user.avatar_date AS first_post_user_avatar_date,
					first_post_user.gravatar AS first_post_user_gravatar
				FROM xf_post AS post
				LEFT JOIN xf_user AS user ON (post.user_id = user.user_id)
				LEFT JOIN xf_thread AS thread ON (post.thread_id = thread.thread_id)
				LEFT JOIN xf_user AS first_post_user ON (thread.user_id = user.user_id)
				WHERE post.post_id IN ('.$this->_getDb()
                ->quote($postIds).')
				', 'post_id');
    }

    /**
     * @param array $articlePageIds
     *
     * @return array
     */
    protected function _getArticleInfoForArticlePages(array $articlePageIds)
    {
        if (!$articlePageIds) {
            return array();
        }

        return $this->fetchAllKeyed(
            '
				SELECT article_page_id, user.avatar_date, user.gravatar, article.*,
					first_article_page_user.avatar_date AS first_article_page_user_avatar_date,
					first_article_page_user.gravatar AS first_article_page_user_gravatar
				FROM xf_article_page AS article_page
				LEFT JOIN xf_user AS user ON (article_page.user_id = user.user_id)
				LEFT JOIN xf_article AS article ON (article_page.article_id = article.article_id)
				LEFT JOIN xf_user AS first_article_page_user ON (article.user_id = user.user_id)
				WHERE article_page.article_page_id IN ('.$this->_getDb()
                ->quote($articlePageIds).')
				', 'article_page_id');
    }
}
