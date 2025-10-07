<?php

class SMWFileProtectHooks
{
    public static function onGetUserPermissionsErrors($title, $user, $action, &$result)
    {
        // $object = new SMWFileProtect();
        // $result = $object->executeImageRefer($title, $user);
        // return $result;
        return true;
    }

    public static function onGetUserPermissionsErrorsNS($title, $user, $action, &$result)
    {
        $object = new SMWNSProtect();
        $result = $object->executeNSRefer($title, $user);
        return $result;
    }
}
