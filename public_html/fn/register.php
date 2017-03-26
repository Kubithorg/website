<?php

//Gestion des inscriptions

function isMailValid($mail) {
    $exp = "/^[\w\-\.]+@[\w\-\.]+\.[\w]+$/i";
    if(preg_match($exp, $mail)) {
        if(checkdnsrr(explode('@', $mail)[1], 'MX')) {
            return true;
        } else {
            return false;
        }
    } else {
        return false;
    }
}
