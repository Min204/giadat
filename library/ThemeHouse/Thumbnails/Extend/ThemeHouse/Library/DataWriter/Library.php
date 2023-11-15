<?php

/**
 * @see ThemeHouse_Library_DataWriter_Library
 */
class ThemeHouse_Thumbnails_Extend_ThemeHouse_Library_DataWriter_Library extends XFCP_ThemeHouse_Thumbnails_Extend_ThemeHouse_Library_DataWriter_Library
{
    /**
     * @see ThemeHouse_Library_DataWriter_Library::_getFields()
     */
    protected function _getFields()
    {
        $fields = parent::_getFields();
        $fields['xf_library']['thumb_sources'] = array(
            'type' => self::TYPE_STRING,
            'default' => '',
        );
        $fields['xf_library']['thumb_width'] = array(
            'type' => self::TYPE_UINT,
            'default' => 0,
        );
        $fields['xf_library']['thumb_height'] = array(
            'type' => self::TYPE_UINT,
            'default' => 0,
        );

        return $fields;
    }

    /**
     * @see ThemeHouse_Library_DataWriter_Library::_preSave()
     */
    protected function _preSave()
    {
        parent::_preSave();

        if (!empty($GLOBALS['ThemeHouse_Library_ControllerAdmin_Library'])) {
            /* @var $controller ThemeHouse_Library_ControllerAdmin_Library */
            $controller = $GLOBALS['ThemeHouse_Library_ControllerAdmin_Library'];

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
        }
    }
}
