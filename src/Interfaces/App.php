<?php

namespace Larapps\BigcommerceApp\Interfaces;

use Illuminate\Http\Request;

interface App {
    public function install( Request $request );

    public function load( Request $request );

    public function uninstall( Request $request ); 
}