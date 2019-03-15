<?php
namespace App\Policies;
use App\User;
use App\Device;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Support\Facades\Auth;
class DevicePolicy
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
    public function show(?User $user, Device $model)
    {
        return true; //"show" => ['user'],
    }
    public function store(?User $user)
    {
        return true; //"store" => ['admin'],
    }
    public function update(?User $user, Device $model)
    {
        return true; //"update" => ['admin'],
    }
    public function delete(?User $user, Device $model)
    {
        return true; // "delete" => ['admin'],
    }
}
