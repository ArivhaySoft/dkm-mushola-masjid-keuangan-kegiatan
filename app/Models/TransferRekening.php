<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransferRekening extends Model
{
    protected $table = 'transfer_rekening';
    protected $fillable = [
        'dari_rekening', 'ke_rekening', 'id_kategori',
        'jumlah', 'keterangan', 'tanggal', 'created_by',
    ];

    protected $casts = [
        'jumlah'  => 'decimal:2',
        'tanggal' => 'date',
    ];

    protected function serializeDate(\DateTimeInterface $date): string
    {
        return $date->format('Y-m-d H:i:s');
    }

    public function dariRekening()
    {
        return $this->belongsTo(Rekening::class, 'dari_rekening');
    }

    public function keRekening()
    {
        return $this->belongsTo(Rekening::class, 'ke_rekening');
    }

    public function kategori()
    {
        return $this->belongsTo(Kategori::class, 'id_kategori');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
