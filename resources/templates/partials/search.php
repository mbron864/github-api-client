<form class="row" id="search-form">
    <div class="col-md-10">
        <input id="query" type="text" class="form-control" placeholder="Search Query" aria-label="Search Query">
    </div>
    <div class="col-md-2">
        <button class="btn btn-primary btn-block" type="submit" id="btn-search">Search</button>
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
    <div class="col">
        <div class="results container-fluid">

        </div>
    </div>
</div>

<script>

let results = [];
let filterRegEx = /[\s\S]*/g;

$("#search-form").submit(function(e) {
    e.preventDefault();

    let query = $("#query").val().split(" ").join("+");

    // Extract the owner and repository from the query string
    // let owner_and_repo = query.match(/repo:[^+]/gm).split(":")[1].split("/");
    // let owner = owner_and_repo[0];
    // let repo = owner_and_repo[1];

    // TODO: add spinner

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
                    console.log(results);
                    showResults();
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
        url: "<?php echo $github["endpoints"]["search_issues"] ?>" + query,
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
    let itemListHtml = "";
    $.each(results, function(i, item) {
        if (item.head.ref.match(filterRegEx)) {
            itemListHtml += `
            <div id="item-${i}" class="row pull-request result">
                <div class="number col-md-1 col-sm-2">${item.number}</div>
                <div class="title col-sm">${item.title}</div>
                <div class="branch col-sm-4">${item.head.ref}</div>
            </div>`;
        }
    });

    $(".results").html("");
    $(".results").append(itemListHtml);
}

</script>