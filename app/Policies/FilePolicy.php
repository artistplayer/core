<?php
namespace App\Policies;
use App\User;
use App\File;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Support\Facades\Auth;
class FilePolicy
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
    public function show(?User $user, File $model)
    {
        return true; //"show" => ['user'],
    }
    public function store(?User $user)
    {
        return true; //"store" => ['admin'],
    }
    public function update(?User $user, File $model)
    {
        return true; //"update" => ['admin'],
    }
    public function delete(?User $user, File $model)
    {
        return true; // "delete" => ['admin'],
    }
}
