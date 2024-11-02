<?php

namespace App\Models;

class ServiceMeta extends Meta{

    protected $table = 'wp_latepoint_service_meta';
    function __construct($object_id = false){
        parent::__construct($object_id);
    }
}