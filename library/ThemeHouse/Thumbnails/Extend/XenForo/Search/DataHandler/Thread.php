<?php

/**
 * @see XenForo_Search_DataHandler_Thread
 */
class ThemeHouse_Thumbnails_Extend_XenForo_Search_DataHandler_Thread extends XFCP_ThemeHouse_Thumbnails_Extend_XenForo_Search_DataHandler_Thread
{
    protected $_thumbnailsModel;

    /**
     * @see XenForo_Search_DataHandler_Thread::getDataForResults()
     */
    public function getDataForResults(array $ids, array $viewingUser, array $resultsGrouped)
    {
        $threads = parent::getDataForResults($ids, $viewingUser, $resultsGrouped);

        $thumbnailsModel = $this->_getThumbnailsModel();
        $threads = $thumbnailsModel->addThumbsToSearchThreads($threads);

        return $threads;
    }

    /**
     * @see XenForo_Search_DataHandler_Thread::renderResult()
     */
    public function renderResult(XenForo_View $view, array $result, array $search)
    {
        $node = array(
            'node_id' => $result['node_id'],
            'node_type_id' => 'Forum',
        );
        if (!$this->_getThumbnailsModel()->canShowThumbs($node) or
             !XenForo_Application::get('options')->th_thumbSearch_thumbnails) {
            return parent::renderResult($view, $result, $search);
        } else {
            $template = parent::renderResult($view, $result, $search);
            if ($template instanceof XenForo_Template_Abstract) {
                /* @var $template XenForo_Template_Abstract */
                $contents = $template->render();
            } else {
                $contents = $template;
                $template = $view->createOwnTemplateObject();
            }

            $name = 'th_search_result_thread_thumbnails';
            $params = array(
                'thread' => $result,
                'forum' => array(
                    'node_id' => $result['node_id'],
                    'title' => $result['node_title'],
                ),
                'post' => $result,
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
