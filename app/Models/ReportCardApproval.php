<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReportCardApproval extends Model
{
    protected $fillable = [
        'report_card_id',
        'approver_id',
        'status',
        'note',
        'approved_at',
    ];

    protected $casts = [
        'approved_at' => 'datetime',
    ];

    public const STATUSES = ['pending', 'approved', 'rejected'];

    public function reportCard()
    {
        return $this->belongsTo(ReportCard::class, 'report_card_id');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approver_id');
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }
}
