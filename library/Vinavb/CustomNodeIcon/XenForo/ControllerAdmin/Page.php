<?php
class Vinavb_CustomNodeIcon_XenForo_ControllerAdmin_Page extends XFCP_Vinavb_CustomNodeIcon_XenForo_ControllerAdmin_Page {
	public function actionSave() {
		$GLOBALS[Vinavb_CustomNodeIcon_Option::GLOBALS_CONTROLLER_ADMIN_PAGE_ACTION_SAVE] = $this;
		
		return parent::actionSave();
	}
	
	public function actionEdit() {
		$response = parent::actionEdit();
		
		if ($response instanceof XenForo_ControllerResponse_View) {
			$response->params[Vinavb_CustomNodeIcon_Option::KEY_PARAMS_NODE] = $response->params['page'];
			Vinavb_CustomNodeIcon_Icon::injectIconsToNode($response->params[Vinavb_CustomNodeIcon_Option::KEY_PARAMS_NODE]);
		}
		
		return $response;
	}
}