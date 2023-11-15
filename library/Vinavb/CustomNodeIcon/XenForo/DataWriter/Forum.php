<?php
class Vinavb_CustomNodeIcon_XenForo_DataWriter_Forum extends XFCP_Vinavb_CustomNodeIcon_XenForo_DataWriter_Forum {
	protected function _preSave() {
		if (isset($GLOBALS[Vinavb_CustomNodeIcon_Option::GLOBALS_CONTROLLER_ADMIN_FORUM_ACTION_SAVE])) {
			Vinavb_CustomNodeIcon_DataWriter_Helper::doPreSave($GLOBALS[Vinavb_CustomNodeIcon_Option::GLOBALS_CONTROLLER_ADMIN_FORUM_ACTION_SAVE], $this);
		}		
		
		return parent::_preSave();
	}
	
	protected function _postSave() {
		Vinavb_CustomNodeIcon_DataWriter_Helper::doPostSave($this);
		
		return parent::_postSave();
	}

	protected function _postDelete() {
		Vinavb_CustomNodeIcon_DataWriter_Helper::doPostDelete($this);

		return parent::_postDelete();
	}
}