<?php

namespace App\Core;

class Profile
{
    public static function generateUniqueFileName($fileExtension)
    {
        return uniqid() . '.' . $fileExtension;
    }

    public static function getDefaultAvatarPath()
    {
        return '/assets/img/default_avatar.png'; // Chemin vers un avatar par défaut
    }
}