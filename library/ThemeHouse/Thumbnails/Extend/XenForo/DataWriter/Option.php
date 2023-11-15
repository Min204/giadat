<?php

/**
 * @see XenForo_DataWriter_Option
 */
class ThemeHouse_Thumbnails_Extend_XenForo_DataWriter_Option extends XFCP_ThemeHouse_Thumbnails_Extend_XenForo_DataWriter_Option
{
    /**
     * @see XenForo_DataWriter_Option::_preSave()
     */
    protected function _preSave()
    {
        if ($this->isChanged('option_value') && $this->getOption(self::OPTION_VALIDATE_VALUE) &&
             $this->get('option_id') == 'imageLinkProxy') {
            $subOptionBackup = $this->get('sub_options');

            $subOptions = preg_split('/(\r\n|\n|\r)+/', trim($subOptionBackup), -1, PREG_SPLIT_NO_EMPTY);
            $subOptions[] = 'thumbnails';

            $this->set('sub_options', implode("\n", $subOptions));
        }

        parent::_preSave();

        if ($this->isChanged('option_value') && $this->getOption(self::OPTION_VALIDATE_VALUE) &&
             $this->get('option_id') == 'imageLinkProxy') {
            $this->set('sub_options', $subOptionBackup);
        }
    }
}
