<?php

/**
 * @see ThemeHouse_NoForo_Model_NoForo
 */
class ThemeHouse_Thumbnails_Extend_ThemeHouse_NoForo_Model_NoForo extends XFCP_ThemeHouse_Thumbnails_Extend_ThemeHouse_NoForo_Model_NoForo
{
    /**
     * @see ThemeHouse_NoForo_Model_NoForo::rebuildForum()
     */
    public function rebuildForum()
    {
        $this->_rebuildPermissionsForAddOn('ThemeHouse_Thumbnails');
        parent::rebuildForum();
    }
}
