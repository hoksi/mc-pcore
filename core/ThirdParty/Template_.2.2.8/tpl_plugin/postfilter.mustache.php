<?php

/* POSTFILTER EXAMPLE */

function mustache($source, $tpl)
{
    return str_replace(['{[', ']}'], ['{{', '}}'], $source);
}