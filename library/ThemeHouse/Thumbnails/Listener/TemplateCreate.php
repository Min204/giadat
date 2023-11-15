<?php

class ThemeHouse_Thumbnails_Listener_TemplateCreate extends ThemeHouse_Listener_TemplateCreate
{
    protected function _getTemplates()
    {
        return array(
            'category_view',
            'forum_list',
            'forum_view',
            'search_results',
            'thread_create',
            'thread_edit',
            'th_library_view_library',
        );
    }

    public static function templateCreate(&$templateName, array &$params, XenForo_Template_Abstract $template)
    {
        $templateCreate = new self($templateName, $params, $template);
        list($templateName, $params) = $templateCreate->run();
    }

    protected function _categoryView()
    {
        $this->_preloadTemplate('th_library_list_thumbnail_thumbnails');
        $this->_preloadTemplate('th_library_list_replace_thumbnails');
        $this->_preloadTemplate('th_forum_list_thumbnail_thumbnails');
        $this->_preloadTemplate('th_forum_list_replace_thumbnails');
    }

    protected function _forumList()
    {
        $this->_preloadTemplate('th_library_list_thumbnail_thumbnails');
        $this->_preloadTemplate('th_library_list_replace_thumbnails');
        $this->_preloadTemplate('th_forum_list_thumbnail_thumbnails');
        $this->_preloadTemplate('th_forum_list_replace_thumbnails');
    }

    protected function _forumView()
    {
        $this->_preloadTemplate('thread_list_item');
        $this->_preloadTemplate('th_forum_list_thumbnail_thumbnails');
        $this->_preloadTemplate('th_thread_list_avatar_replace_thumbnails');
        $this->_preloadTemplate('th_thread_list_thumbnail_thumbnails');
        $this->_preloadTemplate('th_thread_list_avatar_last_post_avatar');
    }

    protected function _thLibraryViewLibrary()
    {
        $this->_preloadTemplate('th_article_list_thumbnail_thumbnails');
    }

    protected function _searchResults()
    {
        $this->_preloadTemplate('th_article_list_thumbnail_thumbnails');
        $this->_preloadTemplate('th_thread_list_thumbnail_thumbnails');
    }

    protected function _threadCreate()
    {
        $this->_preloadTemplate('th_thread_create_form_replace_thumbnails');
        $this->_preloadTemplate('th_thread_create_form_thumbnails');
        $this->_preloadTemplate('th_thread_create_fields_thumbnails');
    }

    protected function _threadEdit()
    {
        $this->_preloadTemplate('th_thread_edit_url_thumbnails');
    }
}
