<?php

/*
 */

namespace App\Models;

use CodeIgniter\Model;

/**
 * Description of Users
 *
 * @author duongtc
 */
abstract class BaseModel extends Model
{

    //protected $table              = 'settings';
    protected $primaryKey         = 'id';
    protected $useAutoIncrement   = true;
    protected $returnType         = 'array';
    protected $useSoftDeletes     = false;
    //protected $allowedFields      = ['code', 'title', 'unit', 'quantity', 'alert_quantity'];
    protected $useTimestamps      = true;
    protected $createdField       = 'created_at';
    protected $updatedField       = 'updated_at';
    protected $deletedField       = '';
    protected $validationRules    = [];
    protected $validationMessages = [];
    protected $skipValidation     = false;
    private static $chartColorCodeOroginal = ["#FFBF00", "#FF7F50", "#708090", "#DE3163", "#9FE2BF" , "#40E0D0", "#6495ED", "#FF0000", "#0000FF" , "#00FF00" ,"#B0C4DE" ,"#E6E6FA" ,"#FFFF00" ,"#00FFFF" ,"#FF00FF" ,"#808080" ,"#800000" ,"#808000" ,"#008000" ,"#800080" ,"#008080" ,"#000080" ,"#000000" ,"#FF0000" ,"#00FF00" ,"#0000FF" ,"#8B0000" ,"#DC143C" ,"#FF6347" ,"#FF7F50" ,"#CD5C5C" ,"#F08080" ,"#E9967A" ,"#FA8072" ,"#FFA500" ,"#FFD700" ,"#B8860B" ,"#DAA520" ,"#EEE8AA" ,"#BDB76B" ,"#808000" ,"#9ACD32" ,"#556B2F" ,"#6B8E23" ,"#8FBC8F" ,"#2E8B57" ,"#008080" ,"#00FFFF" ,"#00CED1" ,"#B0E0E6" ,"#6495ED" ,"#87CEEB" ,"#0000CD" ,"#4169E1" ,"#8A2BE2" ,"#6A5ACD" ,"#9370DB" ,"#8B008B" ,"#BA55D3" ,"#EE82EE" ,"#FF00FF" ,"#FF1493" ,"#F5DEB3" ,"#FAFAD2" ,"#A0522D" ,"#CD853F" ,"#D2B48C"];
    private static $chartColorCode = ["#FFBF00", "#FF7F50", "#708090", "#DE3163", "#9FE2BF" , "#40E0D0", "#6495ED", "#FF0000", "#0000FF" , "#00FF00" ,"#B0C4DE" ,"#E6E6FA" ,"#FFFF00" ,"#00FFFF" ,"#FF00FF" ,"#808080" ,"#800000" ,"#808000" ,"#008000" ,"#800080" ,"#008080" ,"#000080" ,"#000000" ,"#FF0000" ,"#00FF00" ,"#0000FF" ,"#8B0000" ,"#DC143C" ,"#FF6347" ,"#FF7F50" ,"#CD5C5C" ,"#F08080" ,"#E9967A" ,"#FA8072" ,"#FFA500" ,"#FFD700" ,"#B8860B" ,"#DAA520" ,"#EEE8AA" ,"#BDB76B" ,"#808000" ,"#9ACD32" ,"#556B2F" ,"#6B8E23" ,"#8FBC8F" ,"#2E8B57" ,"#008080" ,"#00FFFF" ,"#00CED1" ,"#B0E0E6" ,"#6495ED" ,"#87CEEB" ,"#0000CD" ,"#4169E1" ,"#8A2BE2" ,"#6A5ACD" ,"#9370DB" ,"#8B008B" ,"#BA55D3" ,"#EE82EE" ,"#FF00FF" ,"#FF1493" ,"#F5DEB3" ,"#FAFAD2" ,"#A0522D" ,"#CD853F" ,"#D2B48C"];

    protected function randomColorCode()
    {
        self::$chartColorCode = self::$chartColorCodeOroginal;
        // shuffle(self::$chartColorCode);
    }

    protected function getNextChartColorCode()
    {
        if (count(self::$chartColorCode) > 0)
        {
            return array_shift(self::$chartColorCode);
        }

        $r = rand(0, 255);
        $g = rand($r, 255);
        $b = rand($r, 255);

        return "#" . dechex($r) . dechex($g) . dechex($b);
    }

    protected function startsWith($string, $startString)
    {
        $len = strlen($startString);
        return (substr($string, 0, $len) === $startString);
    }
    
    public static function convertDateFromTimestamp($timestamp, $withSecond = true, $fullDate = false, $format = '')
    {
        return static::convertDateInl(date("Y-m-d H:i:s", $timestamp), $withSecond, $fullDate, $format);
    }

    public function convertDate($datetime, $withSecond = true, $fullDate = false, $format = '')
    {
        return static::convertDateInl($datetime, $withSecond, $fullDate, $format);
    }

    private static function convertDateInl($datetime, $withSecond = true, $fullDate = false, $format = '')
    {
        $date    = explode(' ', $datetime);
        $time    = empty($date[1])?'00:00:00':$date[1];

        if (!$withSecond)
        {
            $time = explode(':', $time);
            $time = "{$time[0]}:{$time[1]}";
        }

        $datetmp = explode('-', $date[0]);

        $finalDate = $datetmp[2] . '/' . $datetmp[1] . '/' . ($fullDate?$datetmp[0]:substr($datetmp[0], 2));

        if (empty($format))
            return $finalDate . " $time";
        $format = str_replace("%T", $time, $format);

        return str_replace("%D", $finalDate, $format);
    }

    public function beginTransaction()
    {
        $this->db->query('START TRANSACTION');
    }

    public function commitTransaction()
    {
        $this->db->query('COMMIT');
    }

    public function rollbackTransaction()
    {
        $this->db->query('ROLLBACK');
    }

    public function genNextCode($prefix)
    {
        $lastCode = $this->asArray()
                ->orderBy('code', 'desc')
                ->first();

        if (empty($lastCode))
        {
            return "{$prefix}00001";
        }

        $lastCode = $lastCode['code'];

        $matches = array();
        if (preg_match('#(\d+)$#', $lastCode, $matches))
        {
            if (!empty($matches[1]))
            {
                $number     = $matches[0];
                $strNumLen  = strlen($number);
                $prefix     = substr($lastCode, 0, strlen($lastCode) - $strNumLen);
                $nextNumber = intval($number);

                do
                {
                    $nextNumber = $nextNumber + 1;
                    if (strlen($nextNumber) > $strNumLen)
                    {
                        $strNumLen = strlen($nextNumber) + 1;
                    }
                    $nextNumber = "000000000000$nextNumber";
                    $nextNumber = substr($nextNumber, strlen($nextNumber) - $strNumLen);
                    $newcode    = "$prefix$nextNumber";

                    $check = $this->findByCode($newcode);
                }
                while (!empty($check));

                return "$prefix$nextNumber";
            }
        }

        return "{$lastCode}00001";
    }

    public function __call($name, $arguments)
    {
        $column  = '';
        $oneOnly = false;
        if ($this->startsWith($name, 'findFirstBy'))
        {
            $column  = strtolower(substr($name, strlen('findFirstBy')));
            $oneOnly = true;
        }
        if ($this->startsWith($name, 'findOneBy'))
        {
            $column  = strtolower(substr($name, strlen('findOneBy')));
            $oneOnly = true;
        }
        elseif ($this->startsWith($name, 'findBy'))
        {
            $column = strtolower(substr($name, strlen('findBy')));
        }

        if (empty($column))
        {
            return parent::__call($name, $arguments);
        }

        if (count($arguments) > 1)
        {
            $this->whereIn($column, $arguments);
        }

        if (is_array($arguments[0]))
        {
            if (count($arguments[0]) > 1)
            {
                $this->whereIn($column, $arguments[0]);
            }
            else
            {
                $this->where($column, $arguments[0]);
            }
        }
        else
        {
            $this->where($column, $arguments[0]);
        }
        if ($oneOnly)
            return $this->first();
        return $this->findAll();
    }

}
