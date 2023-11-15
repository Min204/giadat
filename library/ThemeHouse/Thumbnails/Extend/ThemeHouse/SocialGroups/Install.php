<?php

/**
 * @see ThemeHouse_SocialGroups_Install
 */
class ThemeHouse_Thumbnails_Extend_ThemeHouse_SocialGroups_Install extends XFCP_ThemeHouse_Thumbnails_Extend_ThemeHouse_SocialGroups_Install
{
    /**
     * @see ThemeHouse_SocialGroups_Install_Controller::_getTables()
     */
    protected function _getTables()
    {
        $tables = parent::_getTables();
        $tables['xf_social_forum'] = array_merge($tables['xf_social_forum'],
            $this->_getTableChangesForAddOn('ThemeHouse_Thumbnails', 'xf_social_forum'));

        return $tables;
    }
}
