<?php

/**
 * @see ThemeHouse_Library_Search_DataHandler_Article
 */
class ThemeHouse_Thumbnails_Extend_ThemeHouse_Library_Search_DataHandler_Article extends XFCP_ThemeHouse_Thumbnails_Extend_ThemeHouse_Library_Search_DataHandler_Article
{
    protected $_thumbnailsModel;

    /**
     * @see ThemeHouse_Library_Search_DataHandler_Article::getDataForResults()
     */
    public function getDataForResults(array $ids, array $viewingUser, array $resultsGrouped)
    {
        $articles = parent::getDataForResults($ids, $viewingUser, $resultsGrouped);

        $thumbnailsModel = $this->_getThumbnailsModel();
        $articles = $thumbnailsModel->addThumbsToSearchArticles($articles);

        return $articles;
    }

    /**
     * @see ThemeHouse_Library_Search_DataHandler_Article::renderResult()
     */
    public function renderResult(XenForo_View $view, array $result, array $search)
    {
        $node = array(
            'node_id' => $result['node_id'],
            'node_type_id' => 'Library',
        );
        if (!$this->_getThumbnailsModel()->canShowThumbs($node) or
             !XenForo_Application::get('options')->th_thumbSearch_thumbnails) {
            return parent::renderResult($view, $result, $search);
        } else {
            $template = parent::renderResult($view, $result, $search);
            if (is_subclass_of($template, 'XenForo_Template_Abstract')) {
                /* @var $template XenForo_Template_Abstract */
                $contents = $template->render();
            } else {
                $contents = $template;
                $template = $view->createOwnTemplateObject();
            }

            $name = 'th_search_result_article_thumbnails';
            $params = array(
                'article' => $result,
                'library' => array(
                    'node_id' => $result['node_id'],
                    'title' => $result['node_title'],
                ),
                'article_page' => $result,
                'search' => $search,
            );

            $contents = $template->callTemplateHook($name, $contents, $params);

            return $contents;
        }
    }

    /**
     * @return ThemeHouse_Thumbnails_Model_Thumbnails
     */
    protected function _getThumbnailsModel()
    {
        if (!$this->_thumbnailsModel) {
            $this->_thumbnailsModel = XenForo_Model::create('ThemeHouse_Thumbnails_Model_Thumbnails');
        }

        return $this->_thumbnailsModel;
    }
}
