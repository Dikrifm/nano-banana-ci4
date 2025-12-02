<?php
namespace App\Models;
use CodeIgniter\Model;
class AiLogModel extends Model {
    protected $table = 'ai_logs';
    protected $primaryKey = 'id';
    protected $allowedFields = ['prompt', 'response'];
    protected $useTimestamps = true;
}
