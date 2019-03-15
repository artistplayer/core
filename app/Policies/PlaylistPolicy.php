<?php
namespace App\Policies;
use App\User;
use App\Playlist;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Support\Facades\Auth;
class PlaylistPolicy
{
    use HandlesAuthorization;
    public function search(?User $user)
    {
        return true; //"search" => ['user'],
    }
    public function index(?User $user)
    {
        return true; //"index" => ['user'],
    }
    public function show(?User $user, Playlist $model)
    {
        return true; //"show" => ['user'],
    }
    public function store(?User $user)
    {
        return true; //"store" => ['admin'],
    }
    public function update(?User $user, Playlist $model)
    {
        return true; //"update" => ['admin'],
    }
    public function delete(?User $user, Playlist $model)
    {
        return true; // "delete" => ['admin'],
    }
}
