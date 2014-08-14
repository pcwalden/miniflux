Miniflux.Item = (function() {

    function getItem(item_id)
    {
        var item = document.getElementById("item-" + item_id);

        if (! item) {
            item = document.getElementById("current-item");
            if (item.getAttribute("data-item-id") != item_id) item = false;
        }

        return item;
    }

    function changeLabel(link)
    {
        if (link && link.getAttribute("data-reverse-label")) {
            var content = link.innerHTML;
            link.innerHTML = link.getAttribute("data-reverse-label");
            link.setAttribute("data-reverse-label", content);
        }
    }

    function changeBookmarkLabel(item_id)
    {
        var link = document.getElementById("bookmark-" + item_id);
        changeLabel(link);
    }

    function showItemBookmarked(item_id)
    {
        if (! Miniflux.Nav.IsListing()) {

            var link = document.getElementById("bookmark-" + item_id);
            if (link) link.innerHTML = "★";
        }
        else {

            var link = document.getElementById("show-" + item_id);

            if (link) {
                var icon = document.createElement("span");
                icon.id = "bookmark-icon-" + item_id;
                icon.appendChild(document.createTextNode("★ "));
                link.parentNode.insertBefore(icon, link);
            }

            changeBookmarkLabel(item_id);
        }
    }

    function showItemNotBookmarked(item_id)
    {
        if (! Miniflux.Nav.IsListing()) {

            var link = document.getElementById("bookmark-" + item_id);
            if (link) link.innerHTML = "☆";
        }
        else {

            var icon = document.getElementById("bookmark-icon-" + item_id);
            if (icon) icon.parentNode.removeChild(icon);

            changeBookmarkLabel(item_id);
        }
    }

    function changeStatusLabel(item_id)
    {
        var link = document.getElementById("status-" + item_id);
        changeLabel(link);
    }

    function showItemAsRead(item_id)
    {
        var item = getItem(item_id);

        if (item) {
            if (item.getAttribute("data-hide")) {
                hideItem(item);
            }
            else {

                item.setAttribute("data-item-status", "read");
                changeStatusLabel(item_id);

                // Show icon
                var link = document.getElementById("show-" + item_id);

                if (link) {
                    link.className = "read";

                    var icon = document.createElement("span");
                    icon.id = "read-icon-" + item_id;
                    icon.appendChild(document.createTextNode("✔ "));
                    link.parentNode.insertBefore(icon, link);
                }

                // Change action
                link = document.getElementById("status-" + item_id);
                if (link) link.setAttribute("data-action", "mark-unread");
            }
        }
    }

    function showItemAsUnread(item_id)
    {
        var item = getItem(item_id);

        if (item) {
            if (item.getAttribute("data-hide")) {
                hideItem(item);
            }
            else {

                item.setAttribute("data-item-status", "unread");
                changeStatusLabel(item_id);

                // Remove icon
                var link = document.getElementById("show-" + item_id);
                if (link) link.className = "";

                var icon = document.getElementById("read-icon-" + item_id);
                if (icon) icon.parentNode.removeChild(icon);

                // Change action
                link = document.getElementById("status-" + item_id);
                if (link) link.setAttribute("data-action", "mark-read");
            }
        }
    }

    function hideItem(item)
    {
        if (Miniflux.Event.lastEventType != "mouse") {
            Miniflux.Nav.SelectNextItem();
        }

        item.parentNode.removeChild(item);
        var pageCounter = document.getElementById("page-counter");

        if (pageCounter) {
            var source = item.getAttribute("data-item-page");
            var counter = parseInt(pageCounter.textContent, 10) - 1;
            var articles = document.getElementsByTagName("article");
            
            if (counter === 0 || articles.length === 0) {
                window.location = location.href;
            }

            pageCounter.textContent = counter;

            switch (source) {
                case "unread":
                    document.title = "Miniflux (" + counter + ")";
                    document.getElementById("nav-counter").textContent = "(" + counter + ")";
                    break;
                case "feed-items":
                    document.title = "(" + counter + ") " + pageCounter.parentNode.firstChild.nodeValue;
                    break;
                default:
                    document.title = pageCounter.parentNode.textContent;
            }
        }
    }

    function markAsRead(item_id)
    {
        var request = new XMLHttpRequest();
        request.onload = function() {
            if (Miniflux.Nav.IsListing()) showItemAsRead(item_id);
        };
        request.open("POST", "?action=mark-item-read&id=" + item_id, true);
        request.send();
    }

    function markAsUnread(item_id)
    {
        var request = new XMLHttpRequest();
        request.onload = function() {
            if (Miniflux.Nav.IsListing()) showItemAsUnread(item_id);
        };
        request.open("POST", "?action=mark-item-unread&id=" + item_id, true);
        request.send();
    }

    function markAsRemoved(item_id)
    {
        var request = new XMLHttpRequest();
        request.onload = function() {
            if (Miniflux.Nav.IsListing()) hideItem(getItem(item_id));
        };
        request.open("POST", "?action=mark-item-removed&id=" + item_id, true);
        request.send();
    }

    function bookmark(item, value)
    {
        var item_id = item.getAttribute("data-item-id");
        var request = new XMLHttpRequest();

        request.onload = function() {

            try {

                var response = JSON.parse(this.responseText);

                if (response.result) {

                    item.setAttribute("data-item-bookmark", value);

                    if (value) {
                        showItemBookmarked(item_id);
                    }
                    else {
                        showItemNotBookmarked(item_id);
                    }
                }
            }
            catch (e) {}
        };

        request.open("POST", "?action=bookmark&id=" + item_id + "&value=" + value, true);
        request.send();
    }

    return {
        Get: getItem,
        MarkAsRead: markAsRead,
        MarkAsUnread: markAsUnread,
        MarkAsRemoved: markAsRemoved,
        SwitchBookmark: function(item) {

            var bookmarked = item.getAttribute("data-item-bookmark");

            if (bookmarked == "1") {
                bookmark(item, 0);
            }
            else {
                bookmark(item, 1);
            }
        },
        SwitchStatus: function(item) {

            var item_id = item.getAttribute("data-item-id");
            var status = item.getAttribute("data-item-status");

            if (status == "read") {
                markAsUnread(item_id);
            }
            else if (status == "unread") {
                markAsRead(item_id);
            }
        },
        Show: function(item_id) {
            var link = document.getElementById("show-" + item_id);
            if (link) link.click();
        },
        OpenOriginal: function(item_id) {

            var link = document.getElementById("original-" + item_id);

            if (link) {
                if (getItem(item_id).getAttribute("data-item-status") == "unread") markAsRead(item_id);
                link.removeAttribute("data-action");
                link.click();
            }
        },
        DownloadContent: function() {

            var container = document.getElementById("download-item");
            if (! container) return;

            var item_id = container.getAttribute("data-item-id");
            var message = container.getAttribute("data-before-message");

            var span = document.createElement("span");
            span.appendChild(document.createTextNode("☀"));
            span.className = "loading-icon";

            container.innerHTML = "";
            container.className = "downloading";
            container.appendChild(span);
            container.appendChild(document.createTextNode(" " + message));

            var icon_interval = setInterval(Miniflux.App.BlinkIcon, 250);

            var request = new XMLHttpRequest();

            request.onload = function() {

                var response = JSON.parse(request.responseText);
                clearInterval(icon_interval);

                if (response.result) {

                    var content = document.getElementById("item-content");
                    if (content) content.innerHTML = response.content;

                    if (container) {
                        var message = container.getAttribute("data-after-message");
                        container.innerHTML = "";
                        container.appendChild(document.createTextNode(" " + message));
                    }
                }
                else {

                    if (container) {
                        var message = container.getAttribute("data-failure-message");
                        container.innerHTML = "";
                        container.appendChild(document.createTextNode(" " + message));
                    }
                }
            };

            request.open("POST", "?action=download-item&id=" + item_id, true);
            request.send();
        },
        MarkListingAsRead: function(redirect) {
            var articles = document.getElementsByTagName("article");
            var listing = [];

            for (var i = 0, ilen = articles.length; i < ilen; i++) {
                listing.push(articles[i].getAttribute("data-item-id"));
            }

            var request = new XMLHttpRequest();

            request.onload = function() {
                window.location.href = redirect;
            };

            request.open("POST", "?action=mark-items-as-read", true);
            request.send(JSON.stringify(listing));
        }
    };

})();