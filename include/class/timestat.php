<?php
error_reporting(2039);

function timestart($name)
{
    global $mytimestats;
    if (strlen($name) == 0) {
        return;
    }
    $x    = explode(' ',microtime());
    $x[1] = substr("$x[1]",2,14);
    $mytimestats[$name]['temp'] = $x[1] + $x[0];
}

function timestop($name)
{
    global $mytimestats;
    if (strlen($name) == 0) {
       return;
    }
    $x    = explode(' ',microtime());
    $x[1] = substr("$x[1]",2,14);
    $mytimestats[$name]['all'] += $x[1] + $x[0] - $mytimestats[$name]['temp'];
    $mytimestats[$name]['counter']++;
}

function timeprint($par = '')
{
    timestop('my_time');
    global $mytimestats;
    $_time_stat_print = '';
    $k = array_keys($mytimestats);
    if (strstr($par,'nomain')) {
        $nomain = 1;
    }
    if (strstr($par,'%min')) {
        $proc1    = 1;
        $procent1 = '<td>% of min</td>';
    }
    if (strstr($par,'%max')) {
        $proc2    = 1;
        $procent2 = '<td>% of max</td>';
    }
    if (strstr($par,'graf')) {
        $graf   = 1;
        $grafik = '<td align=center>total<br>time</td>';
    }
    if ($proc1 || $proc2 || $graf) {
        $mmin = 999999;
        $mmax = -1;
        for ($i = 0; $i < count($k); $i++) {
            if ($k[$i] == 'my_time') continue;
            if ($mmin > $mytimestats[$k[$i]]['all']) $mmin = $mytimestats[$k[$i]]['all'];
            if ($mmax < $mytimestats[$k[$i]]['all']) $mmax = $mytimestats[$k[$i]]['all'];
        }
    }
    $_time_stat_print .= "<center><table border=0 cellspacing=0 cellpadding=3><tr><td align=center>counter</td><td align=center>amount<br>of call</td><td align=center>total<br>time</td><td align=center>average<br>time</td>$procent1$procent2$grafik</tr>";
    for ($i = 0; $i < count($k); $i++) {
        if ($k[$i] == 'my_time') continue;
        $_time_stat_print .= @sprintf("<tr><td><b>$k[$i]</b></td><td>%d</td><td>%.4f</td><td>%.4f</td>",$mytimestats[$k[$i]]['counter'],$mytimestats[$k[$i]]['all'],(float)$mytimestats[$k[$i]]['all'] / $mytimestats[$k[$i]]['counter']);
        if ($k[$i] != 'my_time') {
            if ($proc1) {
                $_time_stat_print .= sprintf("<td>%02.1f%%</td>",(float)$mytimestats[$k[$i]]['all'] / $mmin * 100 - 100);
            }
            if ($proc2) {
                $_time_stat_print .= sprintf("<td>%02.1f%%</td>",(float)$mytimestats[$k[$i]]['all'] / $mmax * 100);
            }
            if ($graf) {
                $width  = round(100 * (float)$mytimestats[$k[$i]]['all'] / $mmax);
                $width2 = 100 - $width;
                $_time_stat_print .= "<td><table width=100 border=0 cellspacing=0 cellpadding=0><tr>".
                    "<td width=$width><br></td>".
                    "<td width=$width2 bgcolor=#ccaaaa><br></td>".
                    "</tr></table></td>";
            }
            $tt += $mytimestats[$k[$i]]['all'];
            $tc += $mytimestats[$k[$i]]['counter'];
        } else {
            if ($proc1) $_time_stat_print .= "<td>&nbsp;</td>";
            if ($proc2) $_time_stat_print .= "<td>&nbsp;</td>";
            if ($graf)  $_time_stat_print .= "<td>&nbsp;</td>";
        }
        $_time_stat_print .= "</tr>";
    }
    if (!$nomain) {
        $_time_stat_print .= sprintf("<tr><td colspan=4>total script time %.4f sec</tD></tr><tr><td colspan=4>all internal calls %.4f sec (%d times)</tD></tr><tr><td colspan=4>rest of the time %.4f sec</tD>",$mytimestats['my_time']['all'],$tt,$tc,$mytimestats['my_time']['all'] - $tt);
    }
    $_time_stat_print .= "</td></table></center>";
    return $_time_stat_print;
}

timestart('my_time');
