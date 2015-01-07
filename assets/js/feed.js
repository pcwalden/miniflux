Miniflux.Feed = (function() {

    // List of subscriptions
    var feeds = [];

    // List of feeds currently updating
    var queue = [];

    // Number of concurrent requests when updating all feeds
    var queue_length = 5;

    return {
        Update: function(feed, callback) {
            var itemsCounter = feed.querySelector("span.items-count");
            if (! itemsCounter) return;

            var feed_id = feed.getAttribute("data-feed-id")

            var heading = feed.querySelector("h2:first-of-type");
            heading.className = "loading-icon";

            var request = new XMLHttpRequest();
            request.onload = function() {
                heading.className = "";

                var lastChecked = feed.querySelector(".feed-last-checked");
                if (lastChecked) lastChecked.innerHTML = lastChecked.getAttribute("data-after-update");

                var feedParsingError = feed.querySelector(".feed-parsing-error");
                if (feedParsingError) feedParsingError.innerHTML = "";

                var response = JSON.parse(this.responseText);
                if (response.result) {
                    itemsCounter.innerHTML = response.items_count["items_unread"] + "/" + response.items_count['items_total'];
                } else {
                    if (feedParsingError) feedParsingError.innerHTML = feedParsingError.getAttribute("data-after-error"); 
                }

                if (callback) callback(response);
            };

            request.open("POST", "?action=refresh-feed&feed_id=" + feed_id, true);
            request.send();
        },
        UpdateAll: function() {
            var feeds = Array.prototype.slice.call(document.querySelectorAll("article:not([data-feed-disabled])"));

            var interval = setInterval(function() {
                while (feeds.length > 0 && queue.length < queue_length) {
                    var feed = feeds.shift();
                    queue.push(parseInt(feed.getAttribute('data-feed-id')));

                    Miniflux.Feed.Update(feed, function(response) {
                        var index = queue.indexOf(response.feed_id);
                        if (index >= 0) queue.splice(index, 1);

                        if (feeds.length === 0 && queue.length === 0) {
                            clearInterval(interval);
                            window.location.href = "?action=unread";
                        }
                    });
                }
            }, 100);
        }
    };
})();