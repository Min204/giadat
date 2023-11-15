<?php

/**
 * @see XenForo_ControllerAdmin_Option
 */
class ThemeHouse_Thumbnails_Extend_XenForo_ControllerAdmin_Option extends XFCP_ThemeHouse_Thumbnails_Extend_XenForo_ControllerAdmin_Option
{
    /**
     * @see XenForo_ControllerAdmin_Option::actionList()
     */
    public function actionList()
    {
        $response = parent::actionList();

        if ($response instanceof XenForo_ControllerResponse_View) {
            $preparedOptions = $response->params['preparedOptions'];

            if (isset($preparedOptions['imageLinkProxy'])) {
                $response->params['preparedOptions']['imageLinkProxy']['sub_options'] .= "\n".'thumbnails';
                $response->params['preparedOptions']['imageLinkProxy']['formatParams']['thumbnails'] = '{xen:phrase th_proxy_thumbnails_thumbnails}';
            }
        }

        return $response;
    }
}
