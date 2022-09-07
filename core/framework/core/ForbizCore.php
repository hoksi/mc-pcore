<?php
// Load the Forbiz Common Core
require_once __DIR__.'/ForbizCommonCore.php';

// Load the Forbiz class
require_once APPPATH.'core/Forbiz.class.php';

/**
 * Reference to the Nuna method.
 *
 * Returns current Nuna instance object
 *
 * @return Nuna
 */
function &get_instance()
{
    return Forbiz::get_instance();
}

function getForbiz()
{
    static $forbiz = null;

    if (get_instance() === null) {
        if ($forbiz === null) {
            $forbiz = new Forbiz();
        }

        return $forbiz;
    }

    return get_instance();
}

if (defined('FORBIZ_MALL_VERSION')) {

    function getForbizView(...$params): CustomMallDefaultViewController
    {
        static $fobizView = null;

        if ($fobizView === null) {
            $fobizView = getForbiz()->import('controller.mall.defaultView', $params);
        }

        return $fobizView;
    }
}


// Set a mark point for benchmarking
$BM->mark('loading_time:_base_classes_end');
