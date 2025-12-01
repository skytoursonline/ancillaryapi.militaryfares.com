<?php
class checkin_notification
{
    use tairobot;

    public static function exec()
    {
        $post      = @file_get_contents('php://input');
        $headers   = getallheaders();
        $secret    = 'F8ASASU4iaxh8m66j0fAKI4guvgLSxYcZUE5P5EnjUYzSIxkj0snLVpOT3Ds';
        $signature = hash_hmac('sha256',$post,$secret);
        if ($headers['Signature'] === $signature) {
            $post = json_decode($post,true);
/*
            $post['event']
            $post['object']['request_id']
*/
        }
    }
}
