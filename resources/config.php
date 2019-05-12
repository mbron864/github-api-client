<?php

$config = array(
    "github" => array(
        "username" => "mbron864",
        "access_token" => "5467f04db983edde1db221fc20eb6a58de7ca35f",
        "base_url" => "https://api.github.com",
        "endpoints" => array(
            "search_issues" => function($query) {
                return $config["base_url"] . "/search/issues/?q=" . $query;
            },
            "get_pull_request" => function($owner, $repo, $pr_number) {
                return $config["base_url"] . "/repos/" . $owner . "/" . $repo . "/pulls/" . $pr_number;
            }
        )
    )
);



defined("LIBRARY_PATH")
    or define("LIBRARY_PATH", realpath(dirname(__FILE__) . "/library"));

defined("TEMPLATES_PATH")
    or define("TEMPLATES_PATH", realpath(dirname(__FILE__) . "/templates"));


/*
    Error Reporting
*/
ini_set("error_reporting", "true");
error_reporting(E_ALL|ESTRCT);

?>