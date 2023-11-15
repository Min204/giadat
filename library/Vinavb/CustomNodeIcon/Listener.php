<?php
class Vinavb_CustomNodeIcon_Listener {
	public static function load_class($class, array &$extend) {
		static $classes = array(
			'XenForo_ControllerAdmin_Forum',
			'XenForo_DataWriter_Forum',
		
			'XenForo_ControllerAdmin_Page',
			'XenForo_DataWriter_Page',
		
			'XenForo_ControllerPublic_Misc',
		);
		
		if (in_array($class, $classes)) {
			$extend[] = 'Vinavb_CustomNodeIcon_' . $class;
		}
	}
	
	public static function template_create($templateName, array &$params, XenForo_Template_Abstract $template) {
		switch ($templateName) {
			case 'forum_edit':
			case 'page_edit':
				$template->preloadTemplate('vinavb_customnodeicon_node_edit');
				break;
		}
	}
	
	public static function template_post_render($templateName, &$content, array &$containerData, XenForo_Template_Abstract $template) {
		switch ($templateName) {
			case 'forum_edit':
			case 'page_edit':
				$ourTemplate = $template->create('vinavb_customnodeicon_node_edit', $template->getParams());
				$rendered = trim($ourTemplate->render());
				
				$pos = strpos($content, '</fieldset>');
				if ($pos !== false) {
					$content = substr_replace($content, $rendered, $pos, 0);
				}
				
				$form = '<form';
				$enctype = ' enctype="multipart/form-data" ';
				$pos2 = strrpos($content, $form, $pos - strlen($content));
				if ($pos !== false) {
					$content = substr_replace($content, $enctype, $pos2 + strlen($form), 0);
				}
				break;
			case 'PAGE_CONTAINER':
				$search = '<!--XenForo_Require:CSS-->';
				$replace = '<link rel="stylesheet" type="text/css" href="' . XenForo_Link::buildPublicLink(
					'misc/custom-node-icons',
					'',
					array('d' => Vinavb_CustomNodeIcon_Icon::getLastUpdated())
				) . '" />';
				$content = str_replace($search, $replace . $search, $content);
				break;
		}
	}
}