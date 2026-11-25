<?php

namespace App\Policies;

use App\Models\Task;
use App\Models\User;

class TaskPolicy
{
    public function before(User $user, string $ability): ?bool
    {
        // ถ้าเป็น admin ให้ผ่านทุกอย่างได้เลย ถ้าไม่ใช่ให้คืน null เพื่อไปตรวจกฎข้างล่างต่อ
        return $user->role === 'admin' ? true : null;
    }

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Task $task): bool
    {
        return $user->id === $task->project->owner_id
            || $user->id === $task->assignee_id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Task $task): bool
    {
        // อนุญาตถ้า: เป็นเจ้าของ Project หรือ เป็นคนที่ถูกมอบหมายงานนี้ (Assignee)
        return $user->id === $task->project->owner_id
            || $user->id === $task->assignee_id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Task $task): bool
    {
        // อนุญาตเฉพาะ: เจ้าของ Project เท่านั้นที่จะลบได้
        return $user->id === $task->project->owner_id;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Task $task): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Task $task): bool
    {
        return false;
    }
}
