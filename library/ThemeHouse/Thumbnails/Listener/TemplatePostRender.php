<?php

class ThemeHouse_Thumbnails_Listener_TemplatePostRender extends ThemeHouse_Listener_TemplatePostRender
{
    protected function _getTemplates()
    {
        return array(
            'PAGE_CONTAINER',
            'thread_view',
            'thread_create',
            'thread_edit',
            'thread_list_item_edit',
            'node_forum_level_1',
            'node_forum_level_2',
            'th_node_level_1_social_groups',
            'th_node_level_2_social_groups',
            'th_library_level_1_library',
            'th_library_level_2_library',
        );
    }

    public static function templatePostRender($templateName, &$content, array &$containerData,
        XenForo_Template_Abstract $template)
    {
        $templatePostRender = new self($templateName, $content,
            $containerData, $template);
        list($content, $containerData) = $templatePostRender->run();
    }

    protected function _pageContainer()
    {
        if (isset($GLOBALS['th_thread_open_graph'])) {
            $this->_contents = preg_replace('/(<meta property="og:site_name" content="(.*?)" \/>)/',
                '$1<meta property="og:image" content="'.$GLOBALS['th_thread_open_graph'].'" \/>',
                $this->_contents);
        }
    }

    protected function _threadView()
    {
        $viewParams = $this->_fetchViewParams();
        if (isset($viewParams['thread']['open_graph'])) {
            $GLOBALS['th_thread_open_graph'] = $viewParams['thread']['open_graph'];
        }
    }

    protected function _threadCreate()
    {
        $this->_template->addRequiredExternal('js', 'js/themehouse/thumbnails/thumbnails.js');
        $viewParams = $this->_fetchViewParams();
        $forum = $viewParams['forum'];
        $pattern = '#(<form\s*action="[^"]*\s*[^>]*class="[^"]*)("[^>]*)(>)#Us';
        $replacement = '${1} Thumbnails${2} data-thumbnailsurl="'.
             XenForo_Link::buildPublicLink('forums/create-thread/thumbnails', $forum).'"${3}';
        $this->_patternReplace($pattern, $replacement);
    }

    protected function _threadEdit()
    {
        $viewParams = $this->_fetchViewParams();
        $thumbnailsModel = XenForo_Model::create('ThemeHouse_Thumbnails_Model_Thumbnails');
        if ($thumbnailsModel->isInThumbSources(1, $viewParams['forum'])) {
            $codeSnippet = '<dl class="ctrlUnit submitUnit">';
            $this->_appendTemplateBeforeCodeSnippet($codeSnippet, 'th_thread_edit_url_thumbnails');
        }
    }

    protected function _threadListItemEdit()
    {
        $viewParams = $this->_fetchViewParams();
        $thumbnailsModel = XenForo_Model::create('ThemeHouse_Thumbnails_Model_Thumbnails');
        if ($thumbnailsModel->isInThumbSources(1, $viewParams['forum'])) {
            $codeSnippet = '<div class="buttons editBlock">';
            $this->_appendTemplateBeforeCodeSnippet($codeSnippet, 'th_thread_list_item_edit_url_thumbnails');
        }
    }

    protected function _nodeForumLevel1()
    {
        $this->_nodeForumLevel2();
    } /* END _nodeForumLevel1 */

    protected function _nodeForumLevel2()
    {
        $viewParams = $this->_fetchViewParams();
        $thumbnailsModel = $this->_getThumbnailsModel();
        if (isset($viewParams['forum'])) {
            $node = $viewParams['forum'];
            $showInForums = XenForo_Application::get('options')->th_showInForums_thumbnails;
            if ($showInForums['_type'] == '_some' && !isset($showInForums[$node['node_id']])) {
                return;
            }
        } elseif (isset($viewParams['library'])) {
            $node = $viewParams['library'];
            $showInLibraries = XenForo_Application::get('options')->th_showInLibraries_thumbnails;
            if ($showInLibraries['_type'] == '_some' && !isset($showInLibraries[$node['node_id']])) {
                return;
            }
        }

        if (!$node['privateInfo']) {
            if (isset($node['article']) && isset($node['article']['thumbnail'])) {
                $viewParams['article'] = $node['article'];
                $codeSnippet = $this->_render('th_library_list_replace_thumbnails');
                $this->_replaceWithTemplateAtCodeSnippet($codeSnippet, 'th_library_list_thumbnail_thumbnails',
                    $viewParams);
            } elseif (isset($node['thread']) && isset($node['thread']['thumbnail'])) {
                $viewParams['thread'] = $node['thread'];
                $codeSnippet = $this->_render('th_forum_list_replace_thumbnails');
                $this->_replaceWithTemplateAtCodeSnippet($codeSnippet, 'th_forum_list_thumbnail_thumbnails',
                    $viewParams);
            }
        }
    } /* END _nodeForumLevel2 */

    protected function _thNodeLevel1SocialGroups()
    {
        $this->_nodeForumLevel1();
    } /* END _thNodeLevel1SocialGroups */

    protected function _thNodeLevel2SocialGroups()
    {
        $this->_nodeForumLevel2();
    } /* END _thNodeLevel2SocialGroups */

    protected function _thLibraryLevel1Library()
    {
        $this->_nodeForumLevel1();
    } /* END _thLibraryLevel1Library */

    protected function _thLibraryLevel2Library()
    {
        $this->_nodeForumLevel2();
    } /* END _thLibraryLevel2Library */

    /**
     * @return ThemeHouse_Thumbnails_Model_Thumbnails
     */
    protected function _getThumbnailsModel()
    {
        $this->getModelFromCache('ThemeHouse_Thumbnails_Model_Thumbnails');
    }
}
