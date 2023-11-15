<?php
class Vinavb_CustomNodeIcon_Icon {
	const SIMPLE_CACHE_DATA_LAST_UPDATED = 'Vinavb_CustomNodeIcon_lastUpdated';
	
	public static function getLastUpdated() {
		return XenForo_Application::getSimpleCacheData(self::SIMPLE_CACHE_DATA_LAST_UPDATED);
	}
	
	public static function injectIconsToNode(array &$node) {
		$node[Vinavb_CustomNodeIcon_Option::KEY_NODE_ICONS] = array();
		
		for ($i = 0; $i < 2; $i++) {
			$j = $i + 1;
			$filePath = self::getImageFilePath($node, $j);
			if (file_exists($filePath)) {
				$node[Vinavb_CustomNodeIcon_Option::KEY_NODE_ICONS][$j] = self::getImageUrl($node, $j);
			}
		}
	}
	
	public static function getImageFilePath(array $node, $number) {
		$internal = self::_getImageInternal($node, $number);
		
		if (!empty($internal)) {
			return XenForo_Helper_File::getExternalDataPath() . $internal;
		} else {
			return '';
		}
	}
	
	public static function getImageUrl(array $node, $number) {
		$internal = self::_getImageInternal($node, $number);
		
		if (!empty($internal)) {
			$requestPaths = XenForo_Application::get('requestPaths');
			
			return $requestPaths['fullBasePath'] . XenForo_Application::$externalDataPath . $internal . '?' . self::getLastUpdated();
		} else {
			return '';
		}
	}
	
	protected static function _getImageInternal(array $node, $number) {
		if (!emptY($node) AND !empty($node['node_id'])) {
			return "/node-icons/{$node['node_id']}_{$number}.jpg";
		} else {
			return '';
		}
	}
}