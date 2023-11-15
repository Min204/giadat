<?php

/**
 * @see XenForo_DataWriter_Forum
 */
class ThemeHouse_Thumbnails_Extend_XenForo_DataWriter_Forum extends XFCP_ThemeHouse_Thumbnails_Extend_XenForo_DataWriter_Forum
{
    /**
     * @see XenForo_DataWriter_Forum::_getFields()
     */
    protected function _getFields()
    {
        $fields = parent::_getFields();
        $fields['xf_forum']['thumb_sources'] = array(
            'type' => self::TYPE_STRING,
            'default' => '',
        );
        $fields['xf_forum']['thumb_width'] = array(
            'type' => self::TYPE_UINT,
            'default' => 0,
        );
        $fields['xf_forum']['thumb_height'] = array(
            'type' => self::TYPE_UINT,
            'default' => 0,
        );
        $fields['xf_forum']['require_thumb'] = array(
            'type' => self::TYPE_UINT,
            'default' => 0,
        );

        return $fields;
    }

    /**
     * @see XenForo_DataWriter_Forum::_preSave()
     */
    protected function _preSave()
    {
        parent::_preSave();
        if (!empty($GLOBALS['XenForo_ControllerAdmin_Forum'])) {
            /* @var $controller XenForo_ControllerAdmin_Forum */
            $controller = $GLOBALS['XenForo_ControllerAdmin_Forum'];

            $options = $controller->getInput()->filterSingle('options', XenForo_Input::ARRAY_SIMPLE,
                array(
                    'array' => true,
                ));
            if (isset($options['th_thumbSources_thumbnails'])) {
                $thumbSources = $options['th_thumbSources_thumbnails'];
                for ($i = 0; $i <= 5; ++$i) {
                    if (!isset($thumbSources[$i])) {
                        $thumbSources[$i] = '';
                    }
                }
                ksort($thumbSources);
                $thumbSources = implode(',', $thumbSources);
                $this->set('thumb_sources', $thumbSources);
            }
            $width = $controller->getInput()->filterSingle('thumb_width', XenForo_Input::UINT);
            $this->set('thumb_width', $width);
            $height = $controller->getInput()->filterSingle('thumb_height', XenForo_Input::UINT);
            $this->set('thumb_height', $height);
            $require = $controller->getInput()->filterSingle('require_thumb', XenForo_Input::UINT);
            $this->set('require_thumb', $require);
        }
    }
}
