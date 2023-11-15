<?php
class ThemeHouse_Helper_Php
{
    public static function serialize($data)
    {
        if (XenForo_Application::$versionId < 1050370) {
            return @serialize($data);
        } else {
            return XenForo_Helper_Php::safeSerialize($data);
        }
    }

    public static function unserialize($data)
    {
        if (XenForo_Application::$versionId < 1050370) {
            return @unserialize($data);
        } else {
            return XenForo_Helper_Php::safeUnserialize($data);
        }
    }
}