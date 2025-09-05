<?php

class SMWFileProtectHooks
{
    public static function SMWProtectuserCan($title, $user, $action, &$result)
    {
        $object = new SMWFileProtect();
        $result = $object->executeImageRefer($title, $user);
        return $result;
    }

    public static function SMWProtectNSuserCan($title, $user, $action, &$result)
    {
        $object = new SMWNSProtect();
        $result = $object->executeNSRefer($title, $user);
        return $result;
    }
}
