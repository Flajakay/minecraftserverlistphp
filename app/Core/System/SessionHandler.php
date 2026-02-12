<?php

namespace App\Core\System;

use SessionHandlerInterface;
use App\Models\Session;

class SessionHandler implements SessionHandlerInterface
{
    public function open($savePath, $sessionName): bool
    {
        return true;
    }

    public function close(): bool
    {
        return true;
    }

    public function read($id): string|false
    {
        try {
            if ($session = Session::find($id)) {
                return $session->payload;
            }
        } catch (\Exception $e) {
            // Log error if needed
        }

        return '';
    }

    public function write($id, $data): bool
    {
        $userId = $_SESSION['user_id'] ?? null;
        $ipAddress = $_SERVER['REMOTE_ADDR'] ?? null;
        $userAgent = substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 500);
        $lastActivity = time();

        try {
            Session::save($id, $data, $userId, $ipAddress, $userAgent, $lastActivity);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function destroy($id): bool
    {
        try {
            Session::delete($id);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function gc($maxlifetime): int|false
    {
        try {
            return Session::gc($maxlifetime);
        } catch (\Exception $e) {
            return false;
        }
    }
}
