<?php

namespace App\Core\Features;

use App\Models\Server;
use App\Models\Category;
use App\Models\AuditLog;
use App\Core\Integrations\MinecraftPing;

class Servers
{
    public static function canManageServer($user, $server): bool
    {
        if (!$user || !$server) {
            return false;
        }

        // Check effective ownership (original owner or verified claim owner)
        if (Server::isEffectiveOwner($server, $user->id)) {
            return true;
        }

        // Admins can manage any server
        return $user->type >= 1;
    }

    public static function validateServerData($data, $isUpdate = false): array
    {
        $errors = [];

        if (!$isUpdate && empty($data['address'])) {
            $errors[] = 'Server address is required';
        }

        if (empty($data['country'])) {
            $errors[] = 'Country is required';
        }

        $nameLength = strlen($data['name'] ?? '');
        if ($nameLength < 3 || $nameLength > 64) {
            $errors[] = 'Server name must be between 3 and 64 characters';
        }

        if (strlen($data['description'] ?? '') > 2560) {
            $errors[] = 'Description is too long (max 2560 characters)';
        }

        return $errors;
    }

    public static function validateCategories($categoryIds, $primaryCategoryId = null): array
    {
        $errors = [];

        if (empty($categoryIds) || !is_array($categoryIds)) {
            $errors[] = 'At least one category must be selected';
            return $errors;
        }

        $categoryIds = array_filter(array_map('intval', $categoryIds));
        if (empty($categoryIds)) {
            $errors[] = 'Invalid categories selected';
            return $errors;
        }

        // Primary category must be one of the selected categories to avoid inconsistency
        if ($primaryCategoryId && !in_array($primaryCategoryId, $categoryIds)) {
            $errors[] = 'Primary category must be one of the selected categories';
        }

        return $errors;
    }

    public static function processUploads($files): array
    {
        $result = ['image' => '', 'icon' => ''];

        if (isset($files['image']) && $files['image']['error'] === UPLOAD_ERR_OK) {
            $image = uploadFile($files['image'], 'banners');
            if ($image) {
                $result['image'] = $image;
            } else {
                $result['image_error'] = lang('banner_upload_failed');
            }
        }

        if (isset($files['icon']) && $files['icon']['error'] === UPLOAD_ERR_OK) {
            $icon = uploadFile($files['icon'], 'icons');
            if ($icon) {
                $result['icon'] = $icon;
            } else {
                $result['icon_error'] = lang('icon_upload_failed');
            }
        }

        return $result;
    }

    public static function prepareCustomData($postData, $defaultAddress): array
    {
        $customData = [];

        if (!empty($postData['votifier_public_key'])) {
            $customData['votifier_public_key'] = $postData['votifier_public_key'];
            $customData['votifier_ip'] = $postData['votifier_ip'] ?? $defaultAddress;
            $customData['votifier_port'] = (int) ($postData['votifier_port'] ?? 8192);
        }

        return $customData;
    }

    public static function buildFiltersFromRequest($request, $perPage, $offset): array
    {
        $filters = [
            'limit' => $perPage,
            'offset' => $offset
        ];

        if (!empty($request['categories'])) {
            $categoryIds = explode(',', $request['categories']);
            $categoryIds = array_filter(array_map('intval', $categoryIds));
            if (!empty($categoryIds)) {
                $filters['categories'] = $categoryIds;
            }
        }

        // Enable searching within subcategories when flag is set
        if (isset($request['include_subcategories']) && $request['include_subcategories'] == '1') {
            $filters['include_subcategories'] = true;
        }

        if (isset($request['order_by'])) {
            $filters['order_by'] = $request['order_by'];
        }

        if (isset($request['country']) && $request['country'] !== '') {
            $filters['country'] = $request['country'];
        }

        if (isset($request['status']) && $request['status'] !== '') {
            $filters['status'] = $request['status'];
        }

        if (isset($request['highlight'])) {
            $filters['highlight'] = $request['highlight'];
        }

        return $filters;
    }

    public static function submitServer($userId, $data, $files): array
    {
        $errors = self::validateServerData($data);
        $categoryErrors = self::validateCategories($data['category_ids'] ?? [], $data['primary_category_id'] ?? null);
        $errors = array_merge($errors, $categoryErrors);

        if (Server::exists($data['address'], $data['port'])) {
            $errors[] = 'Server already exists';
        }

        $serverStatus = MinecraftPing::checkServer($data['address'], $data['port']);
        if (!$serverStatus['online']) {
            $errors[] = 'Server is offline or unreachable';
        }

        if (!empty($errors)) {
            return ['success' => false, 'errors' => $errors];
        }

        $uploads = self::processUploads($files);
        if (isset($uploads['image_error'])) {
            return ['success' => false, 'errors' => [$uploads['image_error']]];
        }
        if (isset($uploads['icon_error'])) {
            return ['success' => false, 'errors' => [$uploads['icon_error']]];
        }

        $categoryIds = array_filter(array_map('intval', $data['category_ids']));
        // Use first selected category as primary if none specified
        $primaryCategoryId = (int) ($data['primary_category_id'] ?? 0) ?: $categoryIds[0];
        $customData = self::prepareCustomData($data, $data['address']);

        $serverId = Server::create([
            'user_id' => $userId,
            'category_id' => $primaryCategoryId,
            'address' => $data['address'],
            'port' => $data['port'],
            'name' => $data['name'],
            'description' => $data['description'] ?? '',
            'image' => $uploads['image'],
            'icon' => $uploads['icon'],
            'website' => $data['website'] ?? '',
            'country' => $data['country'],
            'youtube_id' => $data['youtube_id'] ?? '',
            'players' => $serverStatus['players'],
            'max_players' => $serverStatus['max_players'],
            'version' => $serverStatus['version'],
            'custom_data' => json_encode($customData)
        ]);

        Server::setCategories($serverId, $categoryIds, $primaryCategoryId);

        return [
            'success' => true,
            'message' => lang('server_added_review'),
            'server_id' => $serverId
        ];
    }

    public static function updateServer($serverId, $userId, $data, $files): array
    {
        $server = Server::find($serverId);
        $user = (object) ['id' => $userId, 'type' => auth() ? auth()->type : 0];

        if (!$server || !self::canManageServer($user, $server)) {
            return [
                'success' => false,
                'error' => lang('server_not_found_access_denied')
            ];
        }

        $errors = self::validateServerData($data, true);
        $categoryErrors = self::validateCategories($data['category_ids'] ?? []);
        $errors = array_merge($errors, $categoryErrors);

        if (!empty($errors)) {
            return ['success' => false, 'errors' => $errors, 'server' => $server];
        }

        $categoryIds = array_filter(array_map('intval', $data['category_ids']));
        $customData = self::prepareCustomData($data, $server->address);

        $updateData = [
            'name' => $data['name'],
            'category_id' => $categoryIds[0],
            'description' => $data['description'] ?? '',
            'website' => $data['website'] ?? '',
            'country' => $data['country'],
            'youtube_id' => $data['youtube_id'] ?? ''
        ];

        $uploads = self::processUploads($files);
        if (!empty($uploads['image'])) {
            $updateData['image'] = $uploads['image'];
        }
        if (!empty($uploads['icon'])) {
            $updateData['icon'] = $uploads['icon'];
        }

        if (!empty($customData)) {
            $updateData['custom_data'] = json_encode($customData);
        }

        Server::update($serverId, $updateData);
        Server::setCategories($serverId, $categoryIds, $categoryIds[0]);

        return [
            'success' => true,
            'message' => lang('server_updated'),
            'server' => $server
        ];
    }

    public static function performAction($serverId, $userId, $action): array
    {
        $server = Server::find($serverId);
        $user = (object) ['id' => $userId, 'type' => auth() ? auth()->type : 0];

        if (!$server || !self::canManageServer($user, $server)) {
            return [
                'success' => false,
                'error' => lang('server_not_found_access_denied')
            ];
        }

        switch ($action) {
            case 'make_public':
                Server::setPrivate($serverId, 0);
                return ['success' => true, 'message' => lang('server_now_public'), 'redirect' => 'edit'];

            case 'make_private':
                Server::setPrivate($serverId, 1);
                return ['success' => true, 'message' => lang('server_now_private'), 'redirect' => 'edit'];

            case 'delete':
                Server::delete($serverId);
                return ['success' => true, 'message' => lang('server_deleted'), 'redirect' => 'profile'];

            default:
                return ['success' => false, 'error' => lang('invalid_action')];
        }
    }

    public static function getEditPageData($serverId, $userId): array
    {
        $server = Server::find($serverId);
        $user = (object) ['id' => $userId, 'type' => auth() ? auth()->type : 0];

        if (!$server || !self::canManageServer($user, $server)) {
            return [
                'success' => false,
                'error' => lang('server_not_found_access_denied')
            ];
        }

        return [
            'success' => true,
            'server' => $server,
            'categories' => Category::getAllForSelect(),
            'countries' => getCountries(),
            'server_categories' => Server::getCategories($serverId)
        ];
    }

    public static function updateAdmin($serverId, $userId, $data, $files): array
    {
        $server = Server::find($serverId);
        if (!$server) {
            return [
                'success' => false,
                'error' => lang('server_not_found_admin')
            ];
        }

        $categoryIds = $data['category_ids'] ?? [];
        
        if (empty($categoryIds) || !is_array($categoryIds)) {
            return [
                'success' => false,
                'error' => lang('category_required')
            ];
        }
        
        $categoryIds = array_filter(array_map('intval', $categoryIds));
        if (empty($categoryIds)) {
            return [
                'success' => false,
                'error' => lang('invalid_categories')
            ];
        }

        if (empty($data['country'] ?? '')) {
            return [
                'success' => false,
                'error' => 'Country is required'
            ];
        }

        $customData = self::prepareCustomData($data, $server->address);

        $updateData = [
            'name' => sanitize($data['name'] ?? ''),
            'address' => sanitize($data['address'] ?? ''),
            'port' => (int)($data['port'] ?? 25565),
            'category_id' => $categoryIds[0],
            'description' => trim($data['description'] ?? ''),
            'website' => sanitize($data['website'] ?? ''),
            'country' => sanitize($data['country'] ?? ''),
            'youtube_id' => sanitize($data['youtube_id'] ?? '')
        ];

        if (array_key_exists('active', $data)) {
            $updateData['active'] = !empty($data['active']) ? 1 : 0;
        }

        if (array_key_exists('private', $data)) {
            $updateData['private'] = !empty($data['private']) ? 1 : 0;
        }

        if (array_key_exists('highlight', $data)) {
            $updateData['highlight'] = !empty($data['highlight']) ? 1 : 0;
        }

        if (isset($files['image']) && $files['image']['error'] === UPLOAD_ERR_OK) {
            $image = uploadFile($files['image'], 'banners');
            if ($image) {
                $updateData['image'] = $image;
            }
        }

        if (!empty($customData)) {
            $updateData['custom_data'] = json_encode($customData);
        }

        if (Server::update($serverId, $updateData)) {
            Server::setCategories($serverId, $categoryIds, $categoryIds[0]);
            AuditLog::log('update', 'servers', $serverId, $userId, 'Updated server: ' . $server->name);
            
            return [
                'success' => true,
                'message' => lang('server_updated')
            ];
        }

        return [
            'success' => false,
            'error' => lang('server_update_failed')
        ];
    }

    public static function performAdminAction($serverId, $userId, $action): array
    {
        $server = Server::find($serverId);
        
        if (!$server) {
            return [
                'success' => false,
                'error' => lang('server_not_found_admin')
            ];
        }

        switch ($action) {
            case 'activate':
                Server::update($serverId, ['active' => 1]);
                AuditLog::log('activate', 'servers', $serverId, $userId, 'Activated server: ' . $server->name);
                return ['success' => true, 'message' => lang('server_activated'), 'redirect' => 'edit'];

            case 'deactivate':
                Server::update($serverId, ['active' => 0]);
                AuditLog::log('deactivate', 'servers', $serverId, $userId, 'Deactivated server: ' . $server->name);
                return ['success' => true, 'message' => lang('server_deactivated'), 'redirect' => 'edit'];

            case 'make_private':
                Server::update($serverId, ['private' => 1]);
                AuditLog::log('make_private', 'servers', $serverId, $userId, 'Made server private: ' . $server->name);
                return ['success' => true, 'message' => lang('server_made_private'), 'redirect' => 'edit'];

            case 'make_public':
                Server::update($serverId, ['private' => 0]);
                AuditLog::log('make_public', 'servers', $serverId, $userId, 'Made server public: ' . $server->name);
                return ['success' => true, 'message' => lang('server_made_public'), 'redirect' => 'edit'];

            case 'add_highlight':
                Server::update($serverId, ['highlight' => 1]);
                AuditLog::log('add_highlight', 'servers', $serverId, $userId, 'Added highlight to server: ' . $server->name);
                return ['success' => true, 'message' => lang('server_highlighted'), 'redirect' => 'edit'];

            case 'remove_highlight':
                Server::update($serverId, ['highlight' => 0]);
                AuditLog::log('remove_highlight', 'servers', $serverId, $userId, 'Removed highlight from server: ' . $server->name);
                return ['success' => true, 'message' => lang('server_highlight_removed'), 'redirect' => 'edit'];

            case 'delete':
                if (Server::delete($serverId)) {
                    AuditLog::log('delete', 'servers', $serverId, $userId, 'Deleted server: ' . $server->name);
                    return ['success' => true, 'message' => lang('server_deleted'), 'redirect' => 'list'];
                }
                return ['success' => false, 'error' => lang('server_delete_failed'), 'redirect' => 'edit'];

            default:
                return ['success' => false, 'error' => 'Invalid action', 'redirect' => 'edit'];
        }
    }

}
