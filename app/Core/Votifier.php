<?php

namespace App\Core;

/**
 * Minimal Votifier client.
 *
 * Sends an RSA-encrypted VOTE payload to a Votifier-compatible listener.
 * This implementation is intentionally simple and returns false on any failure.
 */
class Votifier
{
    public static function sendVote($publicKey, $host, $port, $username)
    {
        try {
            $socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
            
            if (!$socket) {
                return false;
            }
            
            socket_set_option($socket, SOL_SOCKET, SO_RCVTIMEO, ['sec' => 5, 'usec' => 0]);
            socket_set_option($socket, SOL_SOCKET, SO_SNDTIMEO, ['sec' => 5, 'usec' => 0]);
            
            $result = socket_connect($socket, $host, $port);
            
            if (!$result) {
                socket_close($socket);
                return false;
            }
            
            $voteString = "VOTE\n";
            // Service/site identifier expected by many Votifier implementations.
            $voteString .= "test\n";
            $voteString .= $username . "\n";
            $voteString .= $_SERVER['HTTP_HOST'] . "\n";
            $voteString .= time() . "\n";
            
            $publicKeyResource = openssl_pkey_get_public($publicKey);
            
            if (!$publicKeyResource) {
                socket_close($socket);
                return false;
            }
            
            $encrypted = '';
            $result = openssl_public_encrypt($voteString, $encrypted, $publicKeyResource);
            
            if (!$result) {
                socket_close($socket);
                return false;
            }
            
            socket_write($socket, $encrypted, strlen($encrypted));
            socket_close($socket);
            
            return true;
            
        } catch (Exception $e) {
            return false;
        }
    }
}
