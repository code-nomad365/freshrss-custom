document.addEventListener("DOMContentLoaded", function() {
    const articleDataElement = document.getElementById("article-data");
    const articleIds = articleDataElement ? JSON.parse(articleDataElement.getAttribute("data-article-ids")) : [];
    const markRead = articleDataElement ? JSON.parse(articleDataElement.getAttribute("data-mark-read")) : false;

    if (markRead && Array.isArray(articleIds) && articleIds.length > 0) {
        console.log("Marking articles as read:", articleIds);
        fetch("?c=entry&a=read", {
            method: "POST",
            headers: {
                "Content-Type": "application/json"
            },
            body: JSON.stringify({
                "_csrf": document.querySelector("meta[name='csrf-token']").getAttribute("content"),
                "ajax": true,
                "id": articleIds
            })
        })
        .then(response => response.text())
        .then(data => {
            console.log("Full API response:", data);
        })
        .catch(error => console.error("Error marking articles as read:", error));
    } else {
        console.warn("No articles to mark as read.");
    }

    const backButton = document.getElementById("back-button");
    if (backButton) {
        backButton.addEventListener("click", function(event) {
            event.preventDefault();
            history.back();
        });
    }
});
