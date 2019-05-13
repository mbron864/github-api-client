<?php

$github = array(
    "base_url" => "https://api.github.com",
    "endpoints" => array(
        "search_issues" => "https://api.github.com" . "/search/issues?q=",
        "get_pull_request" => "https://api.github.com/repos/:owner/:repo/pulls/:number"
    )
);

?>

<!-- Home Page -->
<div class="container">
    <div class="row">
        <div class="col">
            <h2>Search Pull Requests</h2>
        </div>
    </div>
    <?php include("partials/search.php"); ?>
</div>