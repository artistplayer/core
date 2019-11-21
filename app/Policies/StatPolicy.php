<?php
namespace App\Policies;
use App\User;
use App\Stat;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Support\Facades\Auth;
class StatPolicy
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
    public function show(?User $user, Stat $model)
    {
        return true; //"show" => ['user'],
    }
    public function store(?User $user)
    {
        return true; //"store" => ['admin'],
    }
    public function update(?User $user, Stat $model)
    {
        return true; //"update" => ['admin'],
    }
    public function delete(?User $user, Stat $model)
    {
        return true; // "delete" => ['admin'],
    }
}
