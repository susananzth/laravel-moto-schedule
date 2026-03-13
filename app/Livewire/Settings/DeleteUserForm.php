<?php

declare(strict_types=1);

namespace App\Livewire\Settings;

use App\Mail\AccountDeletedNotification;
use App\Modules\Auth\Actions\LogoutAction;
use App\Modules\Auth\DTOs\LogoutDTO;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Livewire\Component;

final class DeleteUserForm extends Component
{
    public string $password = '';

    public function __construct(
        private LogoutAction $logoutAction
    ) {}

    /**
     * Delete the currently authenticated user.
     */
    public function deleteUser(): void
    {
        // Validate password confirmation
        $this->validate([
            'password' => ['required', 'string', 'current_password'],
        ]);

        $user = Auth::user();

        // Send notification email before deletion
        // Use try-catch so deletion proceeds even if email fails (security first)
        try {
            Mail::to($user->email)->send(new AccountDeletedNotification($user->firstname));
        } catch (\Exception $e) {
            Log::error("Error sending account deletion notification: " . $e->getMessage());
        }

        // Execute logout and delete user
        $this->logoutAction->execute(new LogoutDTO());
        $user->delete();

        $this->redirect('/', navigate: true);
    }

    public function render()
    {
        return view('livewire.settings.delete-user-form');
