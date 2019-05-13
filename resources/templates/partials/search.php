<form id="search-form" class="needs-validation">
    <div class="form-row">
        <div class="col-md-6">
            <input id="user" type="text" class="form-control" placeholder="User" aria-label="User" required>
        </div>
        <div class="col-md-6">
            <input id="repo" type="text" class="form-control" placeholder="Repository" aria-label="Repository" required>
        </div>
    </div>
    <div class="form-row">
        <div class="col-md-10">
            <input id="query" type="text" class="form-control" placeholder="Search Query" aria-label="Search Query" required>
        </div>
        <div class="col-md-2">
            <button class="btn btn-primary btn-block" type="submit" id="btn-search">Search</button>
        </div>
    </div>
</form>
<form class="row" id="filter">
    <div class="col input-group input-group-sm">
        <input id="branch-filter" type="text" class="form-control form-control-sm" placeholder="Filter Branch" aria-label="Filter Branch">
        <div class="input-group-append">
            <button class="btn btn-outline-secondary btn-sm" type="submit" id="btn-search">Filter</button>
        </div>
    </div>
</form>
<div class="row">
    <div class="col text-right total">
        <span class="total-label">Total:</span>
        <span class="total-count">0</span>
    </div>
</div>
<div class="row results-header">
    <div id="pr-number" class="header-label col-md-1 col-sm-2">number</div>
    <div id="pr-title" class="header-label col-sm">title</div>
    <div id="pr-branch" class="header-label col-sm-4">branch</div>
    <div id="pr-author" class="header-label col-sm-1 text-right">author</div>
</div>
<div class="row">
    <div class="col">
        <div class="results container-fluid">
            <div class="no-results">
                There are no results to display
            </div>
            <!-- Search results will get put here -->
        </div>
    </div>
</div>

<script>

let results = [];
let filterRegEx = /[\s\S]*/g;

$("#search-form").submit(function(e) {
    e.preventDefault();

    let user = $("#user").val().trim();
    let repo = $("#repo").val().trim();
    let query = $("#query").val().trim();

    // Validation

    query = query.split(" ").join("+") + `+repo:${user}/${repo}`;

    console.log(query);

    // Disable the search button
    $("#btn-search").attr("disabled", true);

    // Insert a loading spinner while pull requests are being pulled
    $(".results").html(`
        <div class="d-flex justify-content-center spinner">
            <div class="spinner-border text-primary" role="status">
                <span class="sr-only">Loading...</span>
            </div>
        </div>
    `);

    searchIssues(query, (data, err) => {
        if (err) {
            console.log(err);
            return;
        }

        let buf = new SharedArrayBuffer(1);
        let counter = new Uint8Array(buf);

        // Get more info on each pull request
        $.each(data.items, (i, item) => {
            getPullRequest(item.pull_request.url, (pr, err) => {
                if (err) {
                    console.log(err);
                }

                results[i] = pr;

                // Increment counter
                Atomics.add(counter, 0, 1);
                // Check to see if all pull requests where loaded
                if (data.items.length === Atomics.load(counter, 0).valueOf()) {
                    // Done
                    console.log(data);
                    console.log(results);

                    showResults();
                    $("#btn-search").removeAttr("disabled");
                }
            });
        });


    });

});

$("#filter").submit(function(e) {
    e.preventDefault();
    
    let filterInput = $("#branch-filter").val();
    if (filterInput === "") {
        filterRegEx = /[\s\S]*/g;
    } else {
        filterRegEx = new RegExp($("#branch-filter").val(), "g");
    }

    showResults();
});

function searchIssues(query, callback) {
    let username = "<?php echo $github["username"] ?>";
    let access_token = "<?php echo $github["access_token"] ?>";

    $.ajax({
        type: "GET",
        url: "<?php echo $github["endpoints"]["search_issues"] ?>" + query + "&per_page=100",
        datatype: "json",
        contentType: "application/json",
        beforeSend: function(xhr) {
            xhr.setRequestHeader("Authorization", `Basic ${window.btoa(username + ":" + access_token)}`);
        },
        error: function(err) {
            callback(undefined, err);
        },
        success: function(data) {
            callback(data);
        },
    });
}

function getPullRequest(url, callback) {
    let username = "<?php echo $github["username"] ?>";
    let access_token = "<?php echo $github["access_token"] ?>";

    $.ajax({
        type: "GET",
        url: url,
        datatype: "json",
        contentType: "application/json",
        beforeSend: function(xhr) {
            xhr.setRequestHeader("Authorization", `Basic ${window.btoa(username + ":" + access_token)}`);
        },
        error: function(err) {
            callback(undefined, err);
        },
        success: function(data) {
            callback(data);
        },
    });
}

function showResults() {
    let counter = 0;
    let itemListHtml = "";
    $.each(results, function(i, item) {
        if (item.head.ref.match(filterRegEx)) {
            counter++;
            itemListHtml += `
            <div id="item item-${i}" class="row pull-request result">
                <div class="number col-md-1 col-sm-2">${item.number}</div>
                <div class="title col-sm">${item.title}</div>
                <div class="branch col-sm-4">${item.head.ref}</div>
                <div class="branch col-sm-1">
                    <span
                        class="avatar"
                        data-toggle="tooltip"
                        data-placement="left"
                        title="${item.user.login}"
                        style="background-image: url(${item.user.avatar_url})">
                    </span>
                </div>
            </div>`;
        }
    });

    $(".results").html("");
    $(".results").append(itemListHtml);
    $(".total-count").html(counter);
}

</script>