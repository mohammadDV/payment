<?php

function round_cm($val){
    return number_format($val, 2, '.', '');
}

function rnd_float($val){
    return floatval(round($val + 0.004,2,2));
}
