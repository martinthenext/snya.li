<?php

class Helper
{

    public static function Time($from, $to = '')
    {
        if (empty($to))
            $to = time();

        $diff = (int) abs($to - $from);

        if ($diff <= 3600) {
            $mins = round($diff / 60);
            if ($mins <= 1) {
                $mins = 1;
            }
            $since = sprintf(self::number_ending($mins, '%s минут', '%s минуту', '%s минуты'), $mins);
        } else if (($diff <= 86400) && ($diff > 3600)) {
            $hours = round($diff / 3600);
            if ($hours <= 1) {
                $hours = 1;
            }
            $since = sprintf(self::number_ending($hours, '%s часов', '%s час', '%s часа'), $hours);
        } elseif ($diff >= 86400) {
            $days = round($diff / 86400);
            if ($days <= 1) {
                $days = 1;
            }
            $since = sprintf(self::number_ending($days, '%s дней', '%s день', '%s дня'), $days);
        }

        return $since . ' назад';
    }

    static private function number_ending($number, $ending0, $ending1, $ending2)
    {
        $num100 = $number % 100;
        $num10 = $number % 10;
        if ($num100 >= 5 && $num100 <= 20) {
            return $ending0;
        } else if ($num10 == 0) {
            return $ending0;
        } else if ($num10 == 1) {
            return $ending1;
        } else if ($num10 >= 2 && $num10 <= 4) {
            return $ending2;
        } else if ($num10 >= 5 && $num10 <= 9) {
            return $ending0;
        } else {
            return $ending2;
        }
    }

}
