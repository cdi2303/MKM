<?php

namespace App\Helpers;

use App\Models\PostLog;

class PostLogger
{
    public static function log($action, $postId = null, $request = [], $response = [], $success = true)
    {
        PostLog::create([
            'post_id'  => $postId,
            'action'   => $action,
            'request'  => $request,
            'response' => $response,
            'success'  => $success
        ]);
    }
}
