<?php
/**
 * Blueprint Name: Divi Blueprint
 * Blueprint URI: https://github.com/ServerPress/dynamic-divi-blueprint
 * Description: Fetches and installs the latest version of WordPress, Divi compatible with DesktopServer 3.X and 5.X.
 * Version: 1.0.1
 * Author: Gregg Franklin
 */

if (function_exists('ds_cli_exec') ) {
    include "blueprint-ds3.php";
}else{
    include "blueprint-ds5.php";
}