<?php

namespace App\Domain\Support\Repositories;

use App\Models\Ticket;
use App\Repositories\BaseRepository;
use Illuminate\Database\Eloquent\Model;

/**
 * REPOSITORY: TicketRepository
 *
 * Data access abstraction for tickets.
 * All queries automatically scoped to current organization via BaseRepository.
 *
 * Usage:
 *   $repo = app(TicketRepository::class);
 *   $repo->all();                    // All tickets for current org
 *   $repo->findOrFail($id);          // Find by ID (scoped to org)
 *   $repo->open()->paginate();       // Custom queries with scoping
 *   $repo->byPriority('urgent');     // Filter by priority
 */
class TicketRepository extends BaseRepository
{
    protected function getModel(): Model
    {
        return new Ticket();
    }

    /**
     * Get all open tickets.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function open()
    {
        return $this->query()->where('status', 'open');
    }

    /**
     * Get all closed tickets.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function closed()
    {
        return $this->query()->where('status', 'closed');
    }

    /**
     * Get all waiting tickets (awaiting customer response).
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function waiting()
    {
        return $this->query()->where('status', 'waiting');
    }

    /**
     * Filter tickets by status.
     *
     * @param string $status
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function byStatus(string $status)
    {
        if (!in_array($status, ['open', 'waiting', 'closed'])) {
            throw new \InvalidArgumentException("Invalid ticket status: {$status}");
        }

        return $this->query()->where('status', $status);
    }

    /**
     * Filter tickets by priority.
     *
     * @param string $priority
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function byPriority(string $priority)
    {
        if (!in_array($priority, ['low', 'medium', 'high', 'urgent'])) {
            throw new \InvalidArgumentException("Invalid ticket priority: {$priority}");
        }

        return $this->query()->where('priority', $priority);
    }

    /**
     * Get urgent tickets (high priority that need immediate attention).
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function urgent()
    {
        return $this->query()
            ->where('priority', 'urgent')
            ->orWhere('priority', 'high')
            ->orderBy('priority', 'desc')
            ->orderBy('created_at', 'asc');
    }

    /**
     * Get tickets for a specific user (created by user).
     *
     * @param int $userId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function forUser(int $userId)
    {
        return $this->query()->where('user_id', $userId);
    }

    /**
     * Get recent tickets (for dashboard).
     *
     * @param int $limit
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function recent(int $limit = 10)
    {
        return $this->query()
            ->with(['user', 'messages'])
            ->latest('created_at')
            ->limit($limit);
    }

    /**
     * Get tickets with a specific tag.
     *
     * @param string $tag
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function withTag(string $tag)
    {
        return $this->query()->whereJsonContains('tags', $tag);
    }

    /**
     * Get count of open tickets.
     *
     * @return int
     */
    public function countOpen(): int
    {
        return $this->open()->count();
    }

    /**
     * Get count of tickets by status.
     *
     * @param string $status
     * @return int
     */
    public function countByStatus(string $status): int
    {
        return $this->byStatus($status)->count();
    }

    /**
     * Get count of urgent tickets.
     *
     * @return int
     */
    public function countUrgent(): int
    {
        return $this->query()
            ->where('priority', 'urgent')
            ->orWhere('priority', 'high')
            ->count();
    }

    /**
     * Search tickets by subject or body text.
     *
     * @param string $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function search(string $query)
    {
        return $this->query()
            ->where('subject', 'like', "%{$query}%")
            ->orWhereHas('messages', function ($q) use ($query) {
                $q->where('body', 'like', "%{$query}%");
            });
    }
}
