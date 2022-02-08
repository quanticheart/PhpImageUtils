<?php

class Utils
{
    static function verifyPermissions(): bool
    {
        return is_writable("../../output");
    }
}