<?php
class Vinavb_CustomNodeIcon_DataWriter_Helper {
	const EXTRA_DATA_ICONS_PROCESSED = 'Vinavb_CustomNodeIcon_iconsProcessed';
	
	public static $imageQuality = 85;
	
	public static function doPreSave(XenForo_Controller $controller, XenForo_DataWriter $dw) {
		$icons = array();
		$icons[] = XenForo_Upload::getUploadedFile(Vinavb_CustomNodeIcon_Option::FORM_ICON_FIRST);
		$icons[] = XenForo_Upload::getUploadedFile(Vinavb_CustomNodeIcon_Option::FORM_ICON_SECOND);
		
		$iconsProcessed = self::doProcess($icons, 36);
		
		if (!empty($iconsProcessed)) {
			$dw->setExtraData(self::EXTRA_DATA_ICONS_PROCESSED, $iconsProcessed);
		}
	}
	
	public static function doPostSave(XenForo_DataWriter $dw) {
		$icons = $dw->getExtraData(self::EXTRA_DATA_ICONS_PROCESSED);
		
		if (!empty($icons)) {
			$data = $dw->getMergedData();
			foreach ($icons as $i => $tempFile) {
				$filePath = Vinavb_CustomNodeIcon_Icon::getImageFilePath($data, $i + 1);
				$directory = dirname($filePath);
 
				if (XenForo_Helper_File::createDirectory($directory, true) && is_writable($directory)) {
					if (file_exists($filePath)) {
						@unlink($filePath);
					}
					
					$success = @rename($tempFile, $filePath);
					if ($success) {
						XenForo_Helper_File::makeWritableByFtpUser($filePath);
						XenForo_Application::setSimpleCacheData(Vinavb_CustomNodeIcon_Icon::SIMPLE_CACHE_DATA_LAST_UPDATED, XenForo_Application::$time);
					} else {
						throw new XenForo_Exception(new XenForo_Phrase('vinavb_customnodeicon_unable_to_save_node_icon_to_x', array('path' => $filePath)), true);
					}
				}
			}
		}
	}

	public static function doPostDelete(XenForo_DataWriter $dw) {
		$data = $dw->getMergedData();

		for ($i = 0; $i < 2; $i++) {
			$filePath = Vinavb_CustomNodeIcon_Icon::getImageFilePath($data, $i + 1);
			if (file_exists($filePath)) {
				@unlink($filePath);
			}
		}
	}
	
	protected static function doProcess(array $uploads, $size) {
		$icons = array();
		
		foreach ($uploads as $upload) {
			if (empty($upload)) {
				continue;
			}
			
			if (!$upload->isValid()) {
				throw new XenForo_Exception($upload->getErrors(), true);
			}
	
			if (!$upload->isImage()) {
				throw new XenForo_Exception(new XenForo_Phrase('uploaded_file_is_not_valid_image'), true);
			};
	
			$imageType = $upload->getImageInfoField('type');
			if (!in_array($imageType, array(IMAGETYPE_GIF, IMAGETYPE_JPEG, IMAGETYPE_PNG))) {
				throw new XenForo_Exception(new XenForo_Phrase('uploaded_file_is_not_valid_image'), true);
			}
	
			$outputFiles = array();
			$fileName = $upload->getTempFile();
			$imageType = $upload->getImageInfoField('type');
			$outputType = $imageType;
			$width = $upload->getImageInfoField('width');
			$height = $upload->getImageInfoField('height');
			
			$newTempFile = tempnam(XenForo_Helper_File::getTempDir(), 'xfa');
			$image = XenForo_Image_Abstract::createFromFile($fileName, $imageType);
			if (!$image) {
				continue;
			}

			if ($size > 0) {
				$image->thumbnailFixedShorterSide($size);
	
				if ($image->getOrientation() != XenForo_Image_Abstract::ORIENTATION_SQUARE) {
					$x = floor(($image->getWidth() - $size) / 2);
					$y = floor(($image->getHeight() - $size) / 2);
					$image->crop($x, $y, $size, $size);
				}
			}

			$image->output($outputType, $newTempFile, self::$imageQuality);
			unset($image);
	
			$icons[] = $newTempFile;
		}
		
		return $icons;
	}
}