<?php

namespace App\Models;

/**
 * Proxy model untuk ExamResultResource.
 *
 * ExamResultResource dan ExamMonitorResource sama-sama membaca tabel exam_sessions,
 * tapi harus punya permission Shield yang TERPISAH agar bisa dikelola secara independen:
 *   - ExamMonitorResource → ViewAny:ExamSession (Monitoring Ujian)
 *   - ExamResultResource  → ViewAny:ExamResult  (Hasil Ujian)
 *
 * Model ini TIDAK menambah tabel baru — cukup alias class agar Shield
 * generate permission set berbeda di UI.
 */
class ExamResult extends ExamSession
{
    protected $table = 'exam_sessions';
}
