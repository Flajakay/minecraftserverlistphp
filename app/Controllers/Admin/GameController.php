<?php

namespace App\Controllers\Admin;

use App\Models\Game;
use App\Models\AuditLog;

class GameController
{
    public function index()
    {
        if (!isAdmin()) {
            flash('error', 'Access denied');
            redirect('/');
        }

        $page = (int)($_GET['page'] ?? 1);
        $search = sanitize($_GET['search'] ?? '');

        $limit = 20;
        $games = Game::getAllPaginated($page, $limit, $search);
        $totalGames = Game::countAllAdmin($search);
        $totalPages = ceil($totalGames / $limit);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            return $this->create();
        }

        view('admin.games-index', [
            'games' => $games,
            'totalGames' => $totalGames,
            'totalPages' => $totalPages,
            'currentPage' => $page,
            'search' => $search
        ]);
    }

    public function create(): void
    {
        if (!isAdmin()) {
            flash('error', 'Access denied');
            redirect('/');
        }

        $data = [
            'name' => sanitize($_POST['name'] ?? ''),
            'steam_app_id' => !empty($_POST['steam_app_id']) ? (int)$_POST['steam_app_id'] : null,
            'protocol' => sanitize($_POST['protocol'] ?? 'steam_a2s'),
            'enabled' => isset($_POST['enabled']) ? 1 : 0,
        ];

        $gameId = Game::create($data);

        if ($gameId) {
            AuditLog::log('create', 'games', $gameId, auth()->id, 'Created game: ' . $data['name']);
            flash('success', 'Game created successfully!');
        } else {
            flash('error', 'Failed to create game');
        }

        redirect('/admin/games');
    }

    public function edit($id)
    {
        if (!isAdmin()) {
            flash('error', 'Access denied');
            redirect('/');
        }

        $game = Game::find($id);
        if (!$game) {
            flash('error', 'Game not found');
            redirect('/admin/games');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            return $this->update($id);
        }

        view('admin.games-edit', [
            'game' => $game
        ]);
    }

    public function update($id): void
    {
        if (!isAdmin()) {
            flash('error', 'Access denied');
            redirect('/');
        }

        $game = Game::find($id);
        if (!$game) {
            flash('error', 'Game not found');
            redirect('/admin/games');
        }

        $data = [
            'name' => sanitize($_POST['name'] ?? ''),
            'steam_app_id' => !empty($_POST['steam_app_id']) ? (int)$_POST['steam_app_id'] : null,
            'protocol' => sanitize($_POST['protocol'] ?? 'steam_a2s'),
            'enabled' => isset($_POST['enabled']) ? 1 : 0,
        ];

        Game::update($id, $data);
        AuditLog::log('update', 'games', $id, auth()->id, 'Updated game: ' . $data['name']);

        flash('success', 'Game updated successfully!');
        redirect('/admin/games');
    }

    public function delete($id): void
    {
        if (!isAdmin()) {
            flash('error', 'Access denied');
            redirect('/');
        }

        $game = Game::find($id);
        if (!$game) {
            flash('error', 'Game not found');
            redirect('/admin/games');
        }

        AuditLog::log('delete', 'games', $id, auth()->id, 'Deleted game: ' . $game->name);
        Game::delete($id);

        flash('success', 'Game deleted successfully!');
        redirect('/admin/games');
    }
}
