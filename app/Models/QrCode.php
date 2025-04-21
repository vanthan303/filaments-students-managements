<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QrCode extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'qr_codes'; // Tên bảng

    protected $fillable = [
        'content',
        'local_link',
        'created_by',
        'redirect_to',
    ];

    /**
     * Get the user who created the QrCode.
     */
    public function user(): BelongsTo
    {
        /*
            Xác định mối quan hệ model User tại App\Models\User
            với bảng qr_codes thông qua trường created_by
            Trường created_by trong bảng qr_codes sẽ chứa id của người tạo mã QR
            belongto() là mối quan hệ 1-nhiều (1 user có thể tạo nhiều mã QR)
        */
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }
}
