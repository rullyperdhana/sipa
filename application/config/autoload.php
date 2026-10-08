<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$autoload['packages']   = array();
$autoload['libraries']  = array('database', 'session', 'auth', 'logger', 'form_validation'); // removed 'security'
$autoload['drivers']    = array(); 
$autoload['helper']     = array('url', 'security', 'app', 'date', 'cookie', 'form');
$autoload['config']     = array();
$autoload['language']   = array();
$autoload['model']      = array('user_model', 'log_model');
