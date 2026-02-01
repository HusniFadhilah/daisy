<?php

namespace App\Services;

use App\Models\User;

class RecipientResolverService
{
    /**
     * Ambil semua email aktif milik user (multi-email) atau fallback ke users.email.
     *
     * @return array<int,string>
     */
    public function emailsForUser(User $user): array
    {
        $emails = [];

        // Ambil activeEmails jika sudah di-load
        if ($user->relationLoaded('activeEmails')) {
            foreach ($user->activeEmails as $row) {
                $emails[] = strtolower(trim($row->email));
            }
        } else {
            foreach ($user->activeEmails()->get() as $row) {
                $emails[] = strtolower(trim($row->email));
            }
        }

        // fallback ke email utama jika tidak ada tambahan aktif
        $emails = array_values(array_unique(array_filter($emails)));

        if (empty($emails) && !empty($user->email)) {
            $emails[] = strtolower(trim($user->email));
        }

        return array_values(array_unique(array_filter($emails)));
    }

    /**
     * Ambil email untuk banyak user, lalu digabung & dedup.
     *
     * @param iterable<User> $users
     * @return array<int,string>
     */
    public function emailsForUsers(iterable $users): array
    {
        $all = [];

        foreach ($users as $user) {
            $all = array_merge($all, $this->emailsForUser($user));
        }

        $all = array_values(array_unique(array_filter($all)));
        return $all;
    }
}
