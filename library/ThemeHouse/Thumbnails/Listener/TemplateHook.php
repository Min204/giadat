<?php

class ThemeHouse_Thumbnails_Listener_TemplateHook extends ThemeHouse_Listener_TemplateHook
{
    protected function _getHooks()
    {
        return array(
            'admin_forum_edit_tabs',
            'admin_forum_edit_panes',
            'thread_list_threads',
            'thread_list_stickies',
            'th_search_result_article_thumbnails',
            'th_search_result_thread_thumbnails',
            'thread_create_fields_extra',
            'th_thread_edit_thumbnails',
            'th_thread_list_item_edit_thumbnails',
            'th_article_list_item_avatar_library',
        );
    }

    public static function templateHook($hookName, &$contents, array $hookParams, XenForo_Template_Abstract $template)
    {
        $templateHook = new self($hookName, $contents, $hookParams, $template);
        $contents = $templateHook->run();
    }

    protected function _adminForumEditTabs()
    {
        $this->_appendTemplate('th_forum_edit_tab_thumbnails');
    }

    protected function _adminForumEditPanes()
    {
        $this->_appendTemplate('th_forum_edit_pane_thumbnails');
    }

    protected function _threadListThreads()
    {
        $this->_threadListAnyThreads('threads');
    }

    protected function _threadListStickies()
    {
        if (XenForo_Application::get('options')->th_thumbSticky_thumbnails) {
            $this->_threadListAnyThreads('stickyThreads');
        }
    }

    protected function _threadListAnyThreads($threadType)
    {
        if (XenForo_Application::$versionId > 1020000) {
            return;
        }
        $viewParams = $this->_fetchViewParams();
        $showInForums = XenForo_Application::get('options')->th_showInForums_thumbnails;
        if (!isset($viewParams[$threadType]) or
             ($showInForums['_type'] == '_some' and !isset($showInForums[$viewParams['forum']['node_id']]))) {
            return;
        }
        foreach ($viewParams[$threadType] as $viewParams['thread']) {
            $viewParams['showLastPageNumbers'] = true;
            $viewParams['linkPrefix'] = true;
            if (isset($viewParams['thread']['thumbnail'])) {
                $pattern = '#(<li id="thread-'.$viewParams['thread']['thread_id'].
                     '"[^>]*>\s*)<div class="listBlock posterAvatar">\s*<span class="avatarContainer">.*</span>\s*</div>#Us';
                $replacement = '${1}'.
                     $this->_escapeDollars($this->_render('th_forum_list_thumbnail_thumbnails', $viewParams));
                $this->_patternReplace($pattern, $replacement);
            }
            if (!empty($viewParams['thread']['lastPostInfo']['thumbnail'])) {
                $pattern = '#(<li id="thread-'.$viewParams['thread']['thread_id'].
                     '".*<dl class="lastPostInfo">)\s*<a[^>]*><img[^>]*></a>(.*</dl>.*</li>)#Us';
                $replacement = '${1}'.$this->_render('th_thread_list_avatar_last_post_avatar', $viewParams).'${2}';
                $this->_contents = preg_replace($pattern, $replacement, $this->_contents);
            }
        }
    }

    protected function _thSearchResultArticleThumbnails()
    {
        $viewParams = $this->_fetchViewParams();
        if (isset($viewParams['article']['thumbnail'])) {
            $pattern = '#(<li id="article-'.$viewParams['article']['article_id'].
                 '"[^>]*>\s*)<div class="listBlock posterAvatar">.*</div>#Us';
            $replacement = '${1}'.
                 $this->_escapeDollars($this->_render('th_library_list_thumbnail_thumbnails', $viewParams));
            $this->_patternReplace($pattern, $replacement);
        }
    }

    protected function _thSearchResultThreadThumbnails()
    {
        $viewParams = $this->_fetchViewParams();
        if (isset($viewParams['thread']['thumbnail'])) {
            $pattern = '#(<li id="thread-'.$viewParams['thread']['thread_id'].
                 '"[^>]*>\s*)<div class="listBlock posterAvatar">.*</div>#Us';
            $replacement = '${1}'.
                 $this->_escapeDollars($this->_render('th_forum_list_thumbnail_thumbnails', $viewParams));
            $this->_patternReplace($pattern, $replacement);
        }
    }

    protected function _threadCreateFieldsExtra()
    {
        $thumbnailsModel = XenForo_Model::create('ThemeHouse_Thumbnails_Model_Thumbnails');
        if ($thumbnailsModel->isInThumbSources(1, $this->_hookParams['forum'])) {
            $this->_appendTemplate('th_thread_create_fields_thumbnails');
        }
    }

    protected function _thArticleListItemAvatarLibrary()
    {
        $viewParams = $this->_fetchViewParams();
        if (isset($viewParams['article']['thumbnail']) &&
             (!isset($viewParams['sticky']) ||
             XenForo_Application::get('options')->th_thumbStickyArticles_thumbnails)) {
            $this->_replaceWithTemplate('th_library_list_thumbnail_thumbnails');
        }
    }
}
