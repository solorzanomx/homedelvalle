<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class BrokerManagementController extends Controller
{
    public function index()
    {
        $approvedBrokers = User::where('role', 'broker')->get();
        $pendingUsers = User::where('role', 'user')->get();
        
        return view('admin.brokers', compact('approvedBrokers', 'pendingUsers'));
    }

    public function approveBroker(User $user)
    {
        $user->update(['role' => 'broker']);
        return back()->with('success', 'Broker aprobado correctamente');
    }

    public function revokeBroker(User $user)
    {
        $user->update(['role' => 'user']);
        return back()->with('success', 'Acceso de broker revocado');
    }

    public function makeAdmin(User $user)
    {
        $user->update(['role' => 'admin']);
        return back()->with('success', 'Usuario promocionado a admin');
    }
}
