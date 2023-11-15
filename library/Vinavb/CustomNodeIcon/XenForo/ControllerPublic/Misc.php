<?php
class Vinavb_CustomNodeIcon_XenForo_ControllerPublic_Misc extends XFCP_Vinavb_CustomNodeIcon_XenForo_ControllerPublic_Misc {
	public function actionCustomNodeIcons() {
		$lastUpdated = Vinavb_CustomNodeIcon_Icon::getLastUpdated();
		
		if ($this->_Vinavb_CustomNodeIcon_handleIfModifiedSinceHeader($lastUpdated, $_SERVER)) {
			$this->_Vinavb_CustomNodeIcon_displayCss($lastUpdated, $this->_Vinavb_CustomNodeIcon_renderCss());
		}
		
		exit;
	}
	
	protected function _Vinavb_CustomNodeIcon_handleIfModifiedSinceHeader($lastUpdated, array $server) {
		$outputCss = true;
		
		if (isset($server['HTTP_IF_MODIFIED_SINCE'])) {
			$modDate = strtotime($server['HTTP_IF_MODIFIED_SINCE']);
			if ($modDate !== false && $lastUpdated <= $modDate) {
				header('HTTP/1.1 304 Not Modified', true, 304);
				$outputCss = false;
			}
		}

		return $outputCss;
	}
	
	protected function _Vinavb_CustomNodeIcon_renderCss() {
		$nodes = $this->getModelFromCache('XenForo_Model_Node')->getAllNodes();
		$css = '';
		
		foreach ($nodes as $node) {
			Vinavb_CustomNodeIcon_Icon::injectIconsToNode($node);
			$icons =& $node[Vinavb_CustomNodeIcon_Option::KEY_NODE_ICONS];
			
			if (!empty($icons)) {
				if (count($icons) == 1) {
					// single icon mode
					$css .= ".node.node_{$node['node_id']} .nodeIcon {\n\tbackground: transparent url($icons[1]) no-repeat top left !important;\n}\n";
				} else {
					// double icons mode
					$css .= ".node.node_{$node['node_id']} .unread .nodeIcon {\n\tbackground: transparent url($icons[1]) no-repeat top left  !important;\n}\n";
					$css .= ".node.node_{$node['node_id']} .nodeIcon {\n\tbackground: transparent url($icons[2]) no-repeat top left  !important;\n}\n";
				}
			}
		}
		
		return $css;
	}
	
	protected function _Vinavb_CustomNodeIcon_displayCss($lastUpdated, $css) {
		if (XenForo_Application::get('options')->minifyCss) {
			$css = Minify_CSS_Compressor::process($css);
		}

		header('Content-type: text/css; charset=utf-8');
		header('Expires: Wed, 01 Jan 2020 00:00:00 GMT');
		header('Last-Modified: ' . gmdate('D, d M Y H:i:s', $lastUpdated) . ' GMT');
		header('Cache-Control: public');

		$extraHeaders = XenForo_Application::gzipContentIfSupported($css);
		foreach ($extraHeaders AS $extraHeader) {
			header("$extraHeader[0]: $extraHeader[1]", $extraHeader[2]);
		}

		if (is_string($css) && $css && !ob_get_level() && XenForo_Application::get('config')->enableContentLength) {
			header('Content-Length: ' . strlen($css));
		}

		echo $css;
	}
}