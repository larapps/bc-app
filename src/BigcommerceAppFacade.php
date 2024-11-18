<?php

namespace Larapps\BigcommerceApp;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Larapps\BigcommerceApp\Skeleton\SkeletonClass
 */
class BigcommerceAppFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'bigcommerce-app';
    }
}
