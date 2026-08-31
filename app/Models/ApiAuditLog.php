<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApiAuditLog extends Model
{
    protected $table = 'api_audit_logs';
    public $timestamps = false;

    protected $fillable = [
        'method', 'path', 'response_status', 'request_body', 'response_body',
        'ip', 'actor_id', 'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];
}
