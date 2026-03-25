<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Keuangan extends Model
{
    public const SALDO_AWAL_PREFIX = '__SALDO_AWAL__';

    protected $table = 'keuangan';
    protected $fillable = [
        'masuk', 'keluar', 'keterangan',
        'id_rekening', 'id_kategori', 'created_by', 'tanggal',
    ];

    protected $casts = [
        'masuk'   => 'decimal:2',
        'keluar'  => 'decimal:2',
        'tanggal' => 'date',
    ];

    public function rekening(): BelongsTo
    {
        return $this->belongsTo(Rekening::class, 'id_rekening');
    }

    public function kategori(): BelongsTo
    {
        return $this->belongsTo(Kategori::class, 'id_kategori');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeWithoutSaldoAwal(Builder $query): Builder
    {
        return $query->where(function (Builder $builder) {
            $builder->whereNull('keterangan')
                ->orWhere('keterangan', 'not like', self::SALDO_AWAL_PREFIX . '%');
        });
    }

    public static function isSaldoAwalKeterangan(?string $keterangan): bool
    {
        return $keterangan !== null && str_starts_with($keterangan, self::SALDO_AWAL_PREFIX);
    }

    protected static function booted(): void
    {
        static::saved(function (self $model) {
            $model->kategori->recalculate();
        });

        static::deleted(function (self $model) {
            $model->kategori->recalculate();
        });
    }
}
